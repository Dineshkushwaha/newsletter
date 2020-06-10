<?php

namespace Drupal\sph_newsletter\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmarsysService.
 */
class EmarsysService {

  /**
   * EmarsysService constructor.
   *
   * \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * \Drupal\Core\Messenger\MessengerInterface $messenger.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger) {
    $this->config = $config_factory->get('sph_newsletter.settings');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory'),
        $container->get('messenger')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function emarsysNewsletter($emarsysValues) {

    //Emarsys API details
    $newsLetterId = '';
    $emarsys_api_env = $this->config->get('sph_newsletter.emarsys_api_env');

    //Get the Preview Campaign session if it is available to update the campaign
    $session = \Drupal::request()->getSession();
    $preview_campaign = $session->get('preview_campaign');
    //SegmentID Preview or Launch based on the submit action
    $recipient = json_encode([
      'filter_id' => $emarsysValues['filter'],
    ]);

    if (($emarsysValues['action'] === 'launch') || ($emarsysValues['action'] === 'preview_email' && empty($preview_campaign))) {
      $campaign_content = $this->newsLetterDoCurl($emarsys_api_env . "/api/v2/email/", json_encode($emarsysValues), 'POST');
      $newsLetterId = $campaign_content->data->id;
    }


    //Update the preview campaign if session campaign already present
    if ($emarsysValues['action'] === 'preview_email' && !empty($preview_campaign)) {
      $this->newsLetterDoCurl($emarsys_api_env . "/api/v2/email/" . $preview_campaign . "/patch", json_encode($emarsysValues), 'POST');
    }


    // If email campaign was created.
    if (isset($newsLetterId) || !empty($preview_campaign)) {
      if ($emarsysValues['action'] == 'launch') {
        // Launch Newsletter.
        $launch = $this->newsLetterDoCurl($emarsys_api_env . "/api/v2/email/" . $newsLetterId . "/launch", $recipient, 'POST');
        if ($launch->replyCode === 0) {
          $this->messenger->addStatus('Your NewsLetter is launched');
          //Remove the campaign once launched
          $session->remove('preview_campaign');
        }
      }
      else {
        if (empty($preview_campaign)) {
          // Set the campaign for the Preview Email
          $session->set('preview_campaign', $newsLetterId);
        }
        $preview_campaign = $session->get('preview_campaign');
        // Preview Email Newsletter.
        $preview_email = $this->newsLetterDoCurl($emarsys_api_env . "/api/v2/email/" . $preview_campaign . "/sendtestmail", $recipient, 'POST');
        if ($preview_email->replyCode === 0) {
          $this->messenger->addStatus('Your Email Preview is sent');
        }
      }
    }
  }

  /**
   * Implements curl method.
   */
  public function newsLetterDoCurl($url, $param = NULL, $method = NULL) {

    //Emarsys API details
    $emarsys_api_user = $this->config->get('sph_newsletter.emarsys_api_user');
    $emarsys_api_pass = $this->config->get('sph_newsletter.emarsys_api_pass');

    $process = curl_init($url);
    curl_setopt($process, CURLOPT_TIMEOUT, 30);
    if ($method == 'POST' || $method == 'PUT') {
      curl_setopt($process, CURLOPT_POST, TRUE);
      curl_setopt($process, CURLOPT_POSTFIELDS, $param);
      curl_setopt($process, CURLOPT_CUSTOMREQUEST, $method);
    }
    // Set json headers.
    curl_setopt($process, CURLOPT_HTTPHEADER, $this->jsonheader($emarsys_api_user, $emarsys_api_pass));
    // Because it will not work if we dont set this
    // curl_setopt($process, CURLOPT_BINARYTRANSFER, 1);.
    curl_setopt($process, CURLOPT_HEADER, FALSE);
    curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $content = [];
    $content['output'] = curl_exec($process);
    $content['status_code'] = curl_getinfo($process, CURLINFO_HTTP_CODE);
    $accData = json_decode($content['output']);
    if ($content['status_code'] == 200) {
      return $accData;
    }
    else {
      $this->messenger->addError('Error' . $accData->replyCode . ':' . $accData->replyText);
      return $accData;
    }
  }

  /**
   * Implements json Header Method.
   */
  public function jsonheader($username, $password) {
    $nonce = md5(rand());
    // ISO 8601 date.
    $nonce_ts = date('c');
    $password_digest = base64_encode(sha1($nonce . $nonce_ts . $password));
    $jheader = [
      "Content-Type: application/json",
      "X-WSSE: UsernameToken Username=\"$username\""
      . ", PasswordDigest=\"$password_digest\"" . ", Nonce=\"$nonce\"" . ", Created=\"$nonce_ts\"",
    ];

    return $jheader;
  }

}

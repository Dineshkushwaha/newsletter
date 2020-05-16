<?php

namespace Drupal\sph_newsletter\Services;
/**
 * Class EmarsysService.
 */

class EmarsysService {

  public function __construct() {

  }

  public function previewNewsletter($emarsysValues) {

    $config = \Drupal::configFactory()->get('sph_newsletter.settings');
    $emarsys_api_env = $config->get('sph_newsletter.emarsys_api_env');

    $recipient = json_encode(array(
        'filter_id' => '221963',
    ));

    $preview_email_id = $this->newsletter_doCurl($emarsys_api_env . "/api/v2/email", json_encode($emarsysValues), 'POST');

    $url = $this->newsletter_doCurl($emarsys_api_env . "/api/v2/email/". $preview_email_id->id ."/sendtestmail", $recipient,'POST');

  }

  public function launchNewsletter() {

  }

  /**
   * Implements curl method.
   */
  public function newsletter_doCurl($url, $param = NULL, $method = NULL) {

    $config = \Drupal::configFactory()->get('sph_newsletter.settings');
    $emarsys_api_user = $config->get('sph_newsletter.emarsys_api_user');
    $emarsys_api_pass = $config->get('sph_newsletter.emarsys_api_pass');

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
    $content = array();
    $content['output'] = curl_exec($process);
    $content['status_code'] = curl_getinfo($process, CURLINFO_HTTP_CODE);

    $accData = json_decode($content['output']);
    if( $content['status_code'] == 200 ) {
      \Drupal::messenger()->addStatus('Your NewsLetter is launched...');
    } else {
      \Drupal::messenger()->addStatus('The Newsletter name is already in used. Please add new Name.');
    }

    if($accData->data != '' || !isset($accData->data)) {
      return $accData->data;
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
        . ", PasswordDigest=\"$password_digest\""
        . ", Nonce=\"$nonce\""
        . ", Created=\"$nonce_ts\"",
    ];
    return $jheader;
  }

}

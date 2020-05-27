<?php
namespace Drupal\sph_newsletter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\sph_newsletter\EventSubscriber\NewsletterEventSubscriber;

/**
 * Provides route responses for the Example module.
 */
class PreviewNewsletterController extends ControllerBase {

  /**
   *
   * @var NewsletterEventSubscriber
   */
  protected $newsletterEvent;

  /**
   * PreviewNewsletterController constructor.
   * @param NewsletterEventSubscriber $newsletterEvent
   */
  public function __construct(NewsletterEventSubscriber $newsletterEvent) {
    $this->newsletterEvent = $newsletterEvent;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sph_newsletter.hook_form_alter')
    );
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function previewPage($nid) {

    //Check if it is a valid nids
    $check_nids = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();
    // Generate the HTML and dislay Preview Web Page
    if (isset($check_nids) && !empty($check_nids)) {
      $newsletter_html = $this->newsletterEvent->getHTML($nid);
      $response = new Response();
      $response->setContent($newsletter_html['newsletter_data']);
      return $response;

    } else {
      return [
        '#markup' => 'Not a Valid nid',
      ];
    }
  }

}

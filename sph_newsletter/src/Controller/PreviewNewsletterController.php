<?php
namespace Drupal\sph_newsletter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides route responses for the Example module.
 */
class PreviewNewsletterController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function previewPage($nid) {

    $newsletter_html = $this->getHTML($nid);
    $response = new Response();
    $response->setContent($newsletter_html['newsletter_data']);
    return $response;
  }

  public function getHTML($nid) {
    $entity_type = 'node';
    $view_mode = 'full';
    $builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $node = $storage->load($nid);
    $build = $builder->view($node, $view_mode);

    $renderable = [
      '#theme' => 'newsletter__preview',
      '#result' => $build,
    ];

    $newsletter_data = \Drupal::service('renderer')->renderPlain($renderable); // html output

    $newsletter_html = [
      'newsletter_data' => $newsletter_data,
      'node' => $node,
    ];

    return $newsletter_html;
  }

}
<?php
namespace Drupal\sph_newsletter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Pelago\Emogrifier\CssInliner;

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
    $cssFile = ($node->hasField('field_css_file_name')) ? $node->field_css_file_name->value : '';
    $module_path = drupal_get_path('module', 'sph_newsletter');


    $renderable = [
      '#theme' => 'newsletter__preview',
      '#result' => $build,
      '#cssFile' => $cssFile,
      '#module_path' => $module_path,
    ];

    $newsletter_data = \Drupal::service('renderer')->renderPlain($renderable); // html output

    $newsletter_data = (string) $newsletter_data;
    $visualHtml = CssInliner::fromHtml($newsletter_data)->inlineCss()->render();

    $newsletter_html = [
      'newsletter_data' => $visualHtml,
      'node' => $node,
    ];

    return $newsletter_html;
  }

}
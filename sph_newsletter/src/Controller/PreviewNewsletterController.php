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


    $values = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();
    // Generate the HTML and dislay Preview Web Page
    if (isset($values) && !empty($values)) {
      $newsletter_html = $this->getHTML($nid);
      $response = new Response();
      $response->setContent($newsletter_html['newsletter_data']);
      return $response;

    } else {
      return [
        '#markup' => 'Not a Valid nid',
      ];
    }
  }

  public function getHTML($nid)
  {

    // From the Nid get the Node layout builder output.
    $entity_type = 'node';
    $view_mode = 'full';
    $builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $node = $storage->load($nid);
    $build = $builder->view($node, $view_mode);
    $cssFile = ($node->hasField('field_css_file_name')) ? $node->field_css_file_name->value : '';
    $module_path = drupal_get_path('module', 'sph_newsletter');
    $host = \Drupal::request()->getSchemeAndHttpHost();

    $renderable = [
        '#theme' => 'newsletter__preview',
        '#result' => $build,
    ];

    // generate the rendered HTML from the twig file
    $newsletter_data = \Drupal::service('renderer')->renderPlain($renderable); // html output
    $newsletter_data = (string)$newsletter_data;

    //Converting External css to Inline Css using Emogrifier
    $css_path = $host . '/' . $module_path . '/css/' . $cssFile;
    $css_content = file_get_contents($css_path);
    $visualHtml = CssInliner::fromHtml($newsletter_data)->inlineCss($css_content)->render();

    $newsletter_html = [
        'newsletter_data' => $visualHtml,
        'node' => $node,
    ];

      return $newsletter_html;
    }

}

<?php

namespace Drupal\sph_newsletter\Services;

use Pelago\Emogrifier\CssInliner;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;


class HtmlOutput {

  /**
   *
   * @var $entity_type_manager
   */
  protected $entity_type_manager;

  /**
   * HTMLOUTPUT constructor.
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public function getHTML($nid) {
    // From the Nid get the Node layout builder output.
    $entity_type = 'node';
    $view_mode = 'full';
    $builder = $this->entityTypeManager->getViewBuilder($entity_type);
    $storage = $this->entityTypeManager->getStorage($entity_type);
    $node = $storage->load($nid);
    //Invalidate $node cache Tags
    Cache::invalidateTags($node->getCacheTagsToInvalidate());
    $build = $builder->view($node, $view_mode);
    $cssFile = ($node->hasField('field_css_file_name')) ? $node->field_css_file_name->value : '';
    $module_path = drupal_get_path('module', 'sph_newsletter');
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $renderable['#cache']['max-age'] = 0;
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
<?php

namespace Drupal\sph_newsletter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Pelago\Emogrifier\CssInliner;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides route responses for the Example module.
 */
class PreviewNewsletterController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PreviewNewsletterController constructor.
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @param ContainerInterface $container
   * @return ControllerBase|PreviewNewsletterController
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager')
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

  /**
   * Returns HTML page and $node object.
   */
  public function getHTML($nid)
  {
    // From the Nid get the Node layout builder output.
    $entity_type = 'node';
    $view_mode = 'full';
    $builder = $this->entityTypeManager->getViewBuilder($entity_type);
    $storage = $this->entityTypeManager()->getStorage($entity_type);
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

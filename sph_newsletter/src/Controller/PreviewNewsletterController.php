<?php
namespace Drupal\sph_newsletter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\sph_newsletter\Services\HtmlOutput;

/**
 * Provides route responses for the Example module.
 */
class PreviewNewsletterController extends ControllerBase {

  /**
   *
   * @var HtmlOutput
   */
  protected $htmlOutput;

  /**
   * PreviewNewsletterController constructor.
   * @param HtmlOutput $htmlOutput Service
   */
  public function __construct(HtmlOutput $htmlOutput) {
    $this->htmlOutput = $htmlOutput;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sph_newsletter.html_output')
    );
  }

  /**
   * Returns a simple Preview page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function previewPage($nid) {

    //Check if it is a valid nids
    $check_nids = \Drupal::entityQuery('node')->condition('nid', $nid)->execute();
    // Generate the HTML and dislay Preview Web Page
    if (isset($check_nids) && !empty($check_nids)) {
      $newsletter_html = $this->htmlOutput->getHTML($nid);
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

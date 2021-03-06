<?php
namespace Drupal\sph_newsletter\Controller;

use Drupal\Core\Entity\Query\QueryFactory;
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
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * PreviewNewsletterController constructor.
   * @param HtmlOutput $htmlOutput Service
   */
  public function __construct(HtmlOutput $htmlOutput, QueryFactory $entityQuery) {
    $this->htmlOutput = $htmlOutput;
    $this->entityQuery = $entityQuery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sph_newsletter.html_output'),
      $container->get('entity.query')
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
    $check_nids = $this->entityQuery->get('node')->condition('nid', $nid)->execute();
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

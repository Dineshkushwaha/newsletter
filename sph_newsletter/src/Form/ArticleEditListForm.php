<?php

namespace Drupal\sph_newsletter\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;


/**
 * Class ArticleEditListForm
 * @package Drupal\sph_newsletter\Form
 */
class ArticleEditListForm extends FormBase {
  /**
   * Queue article settings
   */
  const SETTINGS = 'queArticle.settings';

  protected $routematch;

  /**
   * The storage handler class for nodes.
   *
   * @var \Drupal\node\NodeStorage
   */
  protected $nodeStorage;

  /**
   * The storage handler class for files.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * The storage handler class for media.
   *
   * @var \Drupal\media\MediaStorage
   */
  protected $mediaStorage;

  /**
   * ArticleEditListForm constructor.
   */
  public function __construct(CurrentRouteMatch $route_match, EntityTypeManagerInterface $entity) {
    $this->routematch = $route_match;
    $this->nodeStorage = $entity->getStorage('node');
    $this->fileStorage = $entity->getStorage('file');
    $this->mediaStorage = $entity->getStorage('media');
  }

  /**
   * @return FormBase|ArticleEditListForm
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
        $container->get('current_route_match'),
        $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return 'article_edit_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $nid = $this->routematch->getParameter('nid');
    $node = $this->nodeStorage->load($nid);
    //Get the Queue articles from the node
    $queueArticles = $node->get('field_queue_articles')->getValue();
    $config = $this->config(static::SETTINGS);

    $form['article_data'] = [
        '#type' => 'table',
        '#header' => array(t('ID'), t('Title'), t('Summary'), t('Image'), t('Edit')),
        '#title' => 'Article data configuration',
        '#open' => TRUE,
        '#tree' => TRUE,
    ];
    foreach ($queueArticles as $articles) {
      $url = '';
      $node = $this->nodeStorage->load($articles['target_id']);
      $title = $node->getTitle();
      $body = $node->field_subheadline->value;

      $media = $node->field_media->entity;
      if (!empty($media)) {
        $fid = $media->field_media_image->target_id;
      } else {
        $fid = '';
      }
      $config_fid = $config->get($nid .'_'. $articles['target_id'] . '_media');
      if (!empty($config_fid)) {
        $file = $this->fileStorage->load($config_fid[0]);
      } elseif (!empty($fid))  {
        $file = $this->fileStorage->load($fid);
      }
      if (!empty($config_fid) || !empty($fid)) {
        $url = $file->url();
      }

      $form['article_data'][$articles['target_id']]['id'] = array(
          '#plain_text' => $articles['target_id'],
      );
      $form['article_data'][$articles['target_id']]['title'] = array(
          '#plain_text' => !empty($config->get($nid .'_'. $articles['target_id'] . '_title')) ? $config->get($nid .'_'. $articles['target_id'] . '_title') : $title,
      );
      $form['article_data'][$articles['target_id']]['body'] = array(
          '#plain_text' => !empty($config->get($nid .'_'. $articles['target_id'] . '_body')) ? $config->get($nid .'_'. $articles['target_id'] . '_body') : $body,
      );
      $form['article_data'][$articles['target_id']]['media'] = array(
          '#type' => 'markup',
          '#markup' => '<img src="'. $url .'" alt="picture" style="width:30px;height:30px;">',
      );
      $form['article_data'][$articles['target_id']]['edit'] = array(
          '#title' => t('Edit'),
          '#type' => 'link',
          '#url' => Url::fromRoute('sph_newsletter.edit_article', array('id' => $articles['target_id'], 'nid' => $nid)),
          '#attributes' => array('class' => array('button')),
      );
    }
    $form['actions']['edit-article'] = [
      '#title' => t('Back to Newsletter'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $nid]),
      '#attributes' => array('class' => array('button')),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}

<?php

namespace Drupal\sph_newsletter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Class ArticleEditConfigForm
 * @package Drupal\sph_newsletter\Form
 */
class ArticleEditConfigForm extends ConfigFormBase {
  /**
   * Queue Article settings.
   */
  const SETTINGS = 'queArticle.settings';

  protected $routematch;

  /**
   * ArticleEditListForm constructor.
   */
  public function __construct(CurrentRouteMatch $route_match) {
    $this->routematch = $route_match;
  }

  /**
   * @return FormBase|ArticleEditListForm
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
        $container->get('current_route_match')
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
  public function getEditableConfigNames()
  {
    return [
        static::SETTINGS,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $nid = $this->routematch->getParameter('nid');
    $id = $this->routematch->getParameter('id');
    $node = Node::load($id);

    $form['article_data'] = [
        '#type' => 'details',
        '#title' => 'Article data configuration',
        '#open' => TRUE,
        '#tree' => TRUE,
    ];
    $title = $node->getTitle();
    $body = $node->field_subheadline->value;
    $articleMedia = $node->get('field_media')->getValue();
    $media = Media::load($articleMedia[0]['target_id']);
    $fid = $media->field_media_image->target_id;

    $config = $this->config(static::SETTINGS);
    $form['article_data'][$nid .'_'. $id . '_nid'] = [
        '#type' => 'textfield',
        '#title' => t('Queue Article ID'),
        '#default_value' => $id,
        '#disabled' => TRUE,
    ];
    $form['article_data'][$nid .'_'. $id . '_title'] = [
        '#type' => 'textfield',
        '#title' => t('Queue Article title'),
        '#default_value' => !empty($config->get($nid .'_'. $id . '_title')) ? $config->get($nid .'_'. $id . '_title') : $title,
        '#description' => t("Title field"),
    ];
    $form['article_data'][$nid .'_'. $id . '_body'] = [
        '#type' => 'textarea',
        '#title' => t('Queue Article summary'),
        '#default_value' => !empty($config->get($nid .'_'. $id . '_body')) ? $config->get($nid .'_'. $id . '_body') : $body,
        '#description' => t("Summary description"),
    ];
    $validators = [
      'file_validate_extensions' => ['png jpeg jpg'],
    ];
    $form['article_data'][$nid .'_'. $id . '_media'] = [
      '#type' => 'managed_file',
      '#title' => "Media Image",
      '#size' => 20,
      '#description' => t('png, jpeg & jpg format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://sph_newsletter/',
      '#default_value' => !empty($config->get($nid .'_'. $id . '_media')) ? $config->get($nid .'_'. $id . '_media') : [$fid],
    ];
    $form['actions']['article-list'] = [
      '#title' => 'Article List',
      '#type' => 'link',
      '#url' => Url::fromRoute('sph_newsletter.edit_newsletter_article_page', array('nid' => $nid)),
      '#attributes' => array('class' => array('button')),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $articles = $form_state->getValues();
    foreach ($articles['article_data'] as $key => $value) {
      if (strpos ($key, 'media') == true) {
        if (is_array($key)) {
          $fid = (int)reset($value);
          if($fid) {
            $file = File::load($fid);
            if (!$file->isPermanent()) {
              $file->setPermanent();
            }
            $usage = $this->fileUsage->listUsage($file);
            if (empty($usage)) {
              $this->fileUsage->add($file, 'sph_newsletter', 'image', $fid);
            }
            $file->save();
          }
        }
      }
      $warning_value = ['article_data', $key];
      $this->articleDataSave($form_state, $key , $warning_value);
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * Callback function to save the ConfigForm settings.
   */
  public function articleDataSave(&$form_state, $store_val, $value) {
    $this->configFactory->getEditable(static::SETTINGS)
        // Set the submitted configuration setting.
        ->set($store_val, $form_state->getValue($value))
        ->save();
  }
}

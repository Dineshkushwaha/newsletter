<?php

namespace Drupal\sph_newsletter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Class ArticleEditConfigForm
 * @package Drupal\sph_newsletter\Form
 */
class ArticleEditConfigForm extends ConfigFormBase {
  /**
   * Queue Article settings.
   */
  const SETTINGS = 'queArticle.settings';

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
    $nid = \Drupal::routeMatch()->getParameter('nid');
    $id = \Drupal::routeMatch()->getParameter('id');
    $node = Node::load($id);

    $form['article_data'] = [
        '#type' => 'details',
        '#title' => 'Article data configuration',
        '#open' => TRUE,
        '#tree' => TRUE,
    ];
    $title = $node->getTitle();
    $body = $node->field_subheadline->value;
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

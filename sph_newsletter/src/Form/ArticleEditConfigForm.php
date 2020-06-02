<?php

namespace Drupal\sph_newsletter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Class ArticleEditConfigForm
 * @package Drupal\sph_newsletter\Form
 */
class ArticleEditConfigForm extends ConfigFormBase {
  const SETTINGS = 'queArticle.settings';

  public function getFormId()
  {
    return 'article_edit_form';
  }

  public function getEditableConfigNames()
  {
    return [
        static::SETTINGS,
    ];

  }
  public function buildForm(array $form, FormStateInterface $form_state) {
    $nid = \Drupal::routeMatch()->getParameter('id');
    $node = Node::load($nid);

    $form['article_data'] = [
        '#type' => 'details',
        '#title' => 'Article data configuration',
        '#open' => TRUE,
        '#tree' => TRUE,
    ];
    $title = $node->getTitle();
    $body = $node->field_subheadline->value;
    $config = $this->config(static::SETTINGS);
    $form['article_data'][$nid . '_nid'] = [
        '#type' => 'textfield',
        '#title' => t('Queue Article ID'),
        '#default_value' => $nid,
    ];
    $form['article_data'][$nid . '_title'] = [
        '#type' => 'textfield',
        '#title' => t('Queue Article title'),
        '#default_value' => !empty($config->get($nid . '_title')) ? $config->get($nid . '_title') : $title,
        '#description' => t("Title field"),
    ];
    $form['article_data'][$nid . '_body'] = [
        '#type' => 'textarea',
        '#title' => t('Queue Article summary'),
        '#default_value' => !empty($config->get($nid . '_body')) ? $config->get($nid . '_body') : $body,
        '#description' => t("Summary description"),
    ];
    $form['back'] = array(
      '#type' => 'button',
      '#value' => t('Back to Article List'),
      '#attributes' => array(
          'onclick' => 'window.history.back();return false;',
      ),
    );
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

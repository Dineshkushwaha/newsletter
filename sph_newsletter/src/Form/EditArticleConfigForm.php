<?php

namespace Drupal\sph_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;


/**
 * Class EditArticleConfigForm
 * @package Drupal\sph_newsletter\Form
 */
class EditArticleConfigForm extends FormBase {
  const SETTINGS = 'queArticle.settings';

  public function getFormId()
  {
    return 'article_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $nid = \Drupal::routeMatch()->getParameter('nid');
    $node = Node::load($nid);
    $queueArticles = $node->get('field_queue_articles')->getValue();
    $form['article_data'] = [
        '#type' => 'table',
        '#header' => array(t('ID'), t('Title'), t('Summary'), t('Edit')),
        '#title' => 'Article data configuration',
        '#open' => TRUE,
        '#tree' => TRUE,
    ];
    foreach ($queueArticles as $articles) {
      $node = Node::load($articles['target_id']);
      $title = $node->getTitle();
      $body = $node->field_subheadline->value;
      $config = $this->config(static::SETTINGS);
      $form['article_data'][$articles['target_id']]['id'] = array(
          '#plain_text' => $articles['target_id'],
      );
      $form['article_data'][$articles['target_id']]['title'] = array(
          '#plain_text' => !empty($config->get($articles['target_id'] . '_title')) ? $config->get($articles['target_id'] . '_title') : $title,
      );
      $form['article_data'][$articles['target_id']]['body'] = array(
          '#plain_text' => !empty($config->get($articles['target_id'] . '_body')) ? $config->get($articles['target_id'] . '_body') : $body,
      );
      $form['article_data'][$articles['target_id']]['operations'] = array(
          '#type' => 'operations',
          '#links' => array(),
      );
      $form['article_data'][$articles['target_id']]['operations']['#links']['edit'] = array(
          'title' => t('Edit'),
          'url' => Url::fromRoute('sph_newsletter.edit_article', array('id' => $articles['target_id'])),
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}

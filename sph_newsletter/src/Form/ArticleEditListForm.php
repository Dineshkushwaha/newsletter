<?php

namespace Drupal\sph_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;


/**
 * Class ArticleEditListForm
 * @package Drupal\sph_newsletter\Form
 */
class ArticleEditListForm extends FormBase {
  /**
   * Queue article settings
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
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $nid = \Drupal::routeMatch()->getParameter('nid');
    $node = Node::load($nid);
    //Get the Queue articles from the node
    $queueArticles = $node->get('field_queue_articles')->getValue();
    $config = $this->config(static::SETTINGS);

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
      $form['article_data'][$articles['target_id']]['id'] = array(
          '#plain_text' => $articles['target_id'],
      );
      $form['article_data'][$articles['target_id']]['title'] = array(
          '#plain_text' => !empty($config->get($nid .'_'. $articles['target_id'] . '_title')) ? $config->get($nid .'_'. $articles['target_id'] . '_title') : $title,
      );
      $form['article_data'][$articles['target_id']]['body'] = array(
          '#plain_text' => !empty($config->get($nid .'_'. $articles['target_id'] . '_body')) ? $config->get($nid .'_'. $articles['target_id'] . '_body') : $body,
      );
      $form['article_data'][$articles['target_id']]['operations'] = array(
          '#type' => 'operations',
          '#links' => array(),
      );
      $form['article_data'][$articles['target_id']]['operations']['#links']['edit'] = array(
          'title' => t('Edit'),
          'url' => Url::fromRoute('sph_newsletter.edit_article', array('id' => $articles['target_id'], 'nid' => $nid)),
      );
    }
    $form['actions'] = array(
        '#type' => 'operations',
        '#links' => array(),
    );
    $form['actions']['#links']['back'] = array(
        'title' => t('Back to Newsletter'),
        'url' => URL::fromRoute('entity.node.edit_form', ['node' => $nid], []),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}

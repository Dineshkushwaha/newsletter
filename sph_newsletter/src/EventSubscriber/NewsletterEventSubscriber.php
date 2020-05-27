<?php

namespace Drupal\sph_newsletter\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sph_newsletter\Controller\PreviewNewsletterController;

class NewsletterEventSubscriber implements EventSubscriberInterface {


  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'hookFormAlter',
    ];

  }

  /**
   * Hook Alter Method
   * @param  $event
   */
  public function hookFormAlter(&$event) {
    if ($event->getFormId() === 'node_newsletter_edit_form') {
      $form = &$event->getForm();
      $form['actions']['launch'] = [
        '#name' => 'launch',
        '#type' => 'submit',
        '#weight' => 999,
        '#limit_validation_errors' => [],
        '#button_type' => 'submit',
        '#submit' => [
          [$this, 'sph_newsletter_node_form_submit'],
        ],
        '#value' => t('Launch'),
        '#attributes' => ['onclick' => 'if(!confirm("Do you really want to launch?")){return false;}'],
      ];
      $form['actions']['preview_email'] = [
        '#name' => 'preview_email',
        '#type' => 'submit',
        '#weight' => 999,
        '#limit_validation_errors' => [],
        '#button_type' => 'submit',
        '#submit' => [
          [$this, 'sph_newsletter_node_form_submit'],
        ],
        '#value' => t('Preview Email'),
      ];
      $form['actions']['preview']['#submit'][] = [$this, 'sph_newsletter_node_preview'];
    }
  }

  /**
   * Submit action for Launch and Preview Email
   */
  public function sph_newsletter_node_form_submit(&$form, FormStateInterface $form_state) {
    $nid = $form_state->getFormObject()->getEntity()->id();
    $actions = $form_state->getTriggeringElement()['#name'];
    $emarsysValues = $this->get_emarsys_data($nid, $actions);
    $emarsysValues['action'] = $actions;

    $service = \Drupal::service('sph_newsletter.emarsys_services');
    $service->emarsysNewsletter($emarsysValues);
  }


  /**
   * Get the form submit data
   */
  public function get_emarsys_data($nid, $action) {

    $newsletter_html = $this->previewNewsletter->getHTML($nid);
    $node = $newsletter_html['node'];

    $newsLetterValues = [
      'name' => ($node->hasField('field_name')) ? $node->field_name->value . "-" . date('Y-m-d h:i:s') : '',
      'subject' => ($node->hasField('field_subject')) ? $node->field_subject->value : '',
      'language' => ($node->hasField('field_language')) ? $node->field_language->value : '',
      'fromemail' => ($node->hasField('field_from_email')) ? $node->field_from_email->value : '',
      'fromname' => ($node->hasField('field_from_name')) ? $node->field_from_name->value : '',
      'html_source' => $newsletter_html['newsletter_data'],
      'unsubscribe' => 1,
      'filter' => ($action == 'launch' && ($node->hasField('field_preview_segment_id') || $node->hasField('field_production_segment_id'))) ? $node->field_production_segment_id->value : $node->field_preview_segment_id->value,
    ];
    return $newsLetterValues;
  }

  /**
   * Callback function on click of Preview button to set to preview/{nid}
   */
  public function sph_newsletter_node_preview($form, FormStateInterface $form_state) {
    $nid = $form_state->getFormObject()->getEntity()->id();
    $form_state->setRedirect('sph_newsletter.preview_page', ['nid' => $nid]);
  }


}


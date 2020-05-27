<?php

namespace Drupal\sph_newsletter\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;

class NewsletterEventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents()
  {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'hookFormAlter',
    ];

  }

  /**
   * Hook Alter Method
   * @param  $event
   */
  public static function hookFormAlter(&$event) {
    if ($event->getFormId() === 'node_newsletter_edit_form') {
      $form = &$event->getForm();
      $form['actions']['launch'] = [
        '#name' => 'launch',
        '#type' => 'submit',
        '#weight' => 999,
        '#limit_validation_errors' => [],
        '#button_type' => 'submit',
        '#submit' => [
          'sph_newsletter_node_form_submit',
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
          'sph_newsletter_node_form_submit',
        ],
        '#value' => t('Preview Email'),
      ];
      $form['actions']['preview']['#submit'][] = 'sph_newsletter_node_preview';
    }
  }

}


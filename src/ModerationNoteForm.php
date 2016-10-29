<?php

namespace Drupal\moderation_notes;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\moderation_notes\Ajax\AddModerationNoteCommand;
use Drupal\moderation_notes\Ajax\ReplyModerationNoteCommand;

/**
 * Form handler for the moderation_note edit forms.
 */
class ModerationNoteForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\moderation_notes\Entity\ModerationNote $note */
    $note = $this->entity;

    // Wrap our form so that our submit callback can re-render the form.
    $form_id = $this->getOperation() === 'edit' ? $note->id() : $this->getOperation();
    $form['#prefix'] = '<div class="moderation-note-form-wrapper" data-moderation-note-form-id="' . $form_id . '">';
    $form['#suffix'] = '</div>';

    $form['text'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#default_value' => $note->getText(),
    ];

    $form['quote'] = [
      '#type' => 'textarea',
      '#attributes' => [
        'class' => ['visually-hidden', 'field-moderation-note-quote'],
      ],
      '#resizable' => 'none',
      '#default_value' => $note->getQuote(),
    ];

    $form['quote_offset'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['visually-hidden', 'field-moderation-note-quote-offset'],
      ],
      '#default_value' => $note->getQuoteOffset(),
    ];

    if ($this->getOperation() === 'reply' || $this->entity->hasParent()) {
      $form['#attributes']['class'][] = 'moderation-note-form-reply';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->getOperation() === 'reply' ? $this->t('Reply') : $this->t('Save'),
      '#ajax' => [
        'callback' => '::submitForm',
        'method' => 'replace',
        'disable-refocus' => TRUE,
      ],
    ];

    if ($this->getOperation() !== 'reply') {
      $actions['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#executes_submit_callback' => FALSE,
        '#ajax' => [
          'callback' => '::cancelForm',
          'method' => 'replace',
          'disable-refocus' => TRUE,
        ],
      ];
    }

    return $actions;
  }

  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($this->getOperation() === 'create') {
      $command = new CloseDialogCommand('#drupal-offcanvas');
    }
    else {
      /** @var \Drupal\moderation_notes\ModerationNoteInterface $note */
      $note = $this->entity;
      $selector = '[data-moderation-note-form-id="' . $note->id() . '"]';
      $content = $this->entityTypeManager->getViewBuilder('moderation_note')->view($note);
      $command = new ReplaceCommand($selector, $content);
    }

    $response->addCommand($command);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_id = $this->getOperation() === 'edit' ? $this->entity->id() : $this->getOperation();
    $selector = '[data-moderation-note-form-id="' . $form_id . '"]';

    // If the form has errors, return the contents of the form.
    // @todo Why does $form_state->getErrors() and drupal_get_messages() return
    // an empty string at this point in execution? This block of code will
    // highlight form fields that have errors, but there will be no messages
    // for the user.
    if ($form_state->hasAnyErrors()) {
      $response = new AjaxResponse();
      $command = new ReplaceCommand($selector, $form);
      $response->addCommand($command);
      return $response;
    }

    parent::submitForm($form, $form_state);
    parent::save($form, $form_state);

    /** @var \Drupal\moderation_notes\ModerationNoteInterface $note */
    $note = $this->entity;

    $response = new AjaxResponse();

    if ($this->getOperation() === 'create') {
      $command = new AddModerationNoteCommand($note);
      $response->addCommand($command);
      $command = new CloseDialogCommand('#drupal-offcanvas');
    }
    else {
      $content = $this->entityTypeManager->getViewBuilder('moderation_note')->view($note);
      $command = new ReplaceCommand($selector, $content);
    }

    $response->addCommand($command);

    if ($this->getOperation() === 'reply') {
      $command = new ReplyModerationNoteCommand($note->getParent());
      $response->addCommand($command);
    }

    return $response;
  }

}

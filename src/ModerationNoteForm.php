<?php

namespace Drupal\moderation_notes;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\moderation_notes\Ajax\AddModerationNoteCommand;
use Drupal\moderation_notes\Ajax\ShowModerationNoteCommand;

/**
 * Form handler for the moderation_note edit forms.
 */
class ModerationNoteForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\moderation_notes\Entity\ModerationNote $moderation_note */
    $moderation_note = $this->entity;

    // Wrap our form so that our submit callback can re-render the form.
    $form['#prefix'] = '<div id="moderation-note-form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['text'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#default_value' => $moderation_note->getText(),
    ];

    $form['quote'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['visually-hidden', 'field-moderation-note-quote'],
      ],
      '#resizable' => 'none',
      '#default_value' => $moderation_note->getQuote(),
    ];

    $form['quote_offset'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['visually-hidden', 'field-moderation-note-quote-offset'],
      ],
      '#default_value' => $moderation_note->getQuoteOffset(),
    ];

    if ($this->getOperation() !== 'create') {
      $form['#attached']['drupalSettings']['highlight_moderation_note'] = [
        'id' => $moderation_note->id(),
        'quote' => $moderation_note->getQuote(),
        'quote_offset' => $moderation_note->getQuoteOffset(),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#ajax' => [
        'callback' => '::submitForm',
        'wrapper' => 'moderation-note-form-wrapper',
        'method' => 'replace',
        'disable-refocus' => TRUE,
      ],
    );

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    parent::save($form, $form_state);

    /** @var \Drupal\moderation_notes\ModerationNoteInterface $note */
    $note = $this->entity;

    $response = new AjaxResponse();

    if ($this->getOperation() === 'create') {
      $command = new AddModerationNoteCommand($note);
    }
    else {
      $command = new ShowModerationNoteCommand($note);
    }

    $response->addCommand($command);

    return $response;
  }

}

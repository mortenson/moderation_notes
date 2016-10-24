<?php

namespace Drupal\moderation_notes\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\moderation_notes\Ajax\RemoveModerationNoteCommand;

/**
 * Provides a form for deleting a moderation note.
 */
class ModerationNoteDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this->t('The moderation note has been deleted.');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Delete moderation note');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('You are about to delete a note, this action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\moderation_notes\Entity\ModerationNote $note */
    $note = $this->entity;

    // Wrap our form so that our submit callback can re-render the form.
    $form['#prefix'] = '<div class="moderation-note-form-wrapper" data-moderation-note-form-id="' . $note->id() . '">';
    $form['#suffix'] = '</div>';

    $form['#attached']['drupalSettings']['highlight_moderation_note'] = [
      'id' => $note->id(),
      'quote' => $note->getQuote(),
      'quote_offset' => $note->getQuoteOffset(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    return [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->getConfirmText(),
        '#ajax' => [
          'callback' => '::submitForm',
          'wrapper' => 'moderation-note-form-wrapper',
          'method' => 'replace',
          'disable-refocus' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\moderation_notes\Entity\ModerationNote $note */
    $note = $this->entity;

    // Clear the Drupal messages, as this form uses AJAX to display its
    // results. Displaying a deletion message on the next page the user visits
    // is awkward.
    drupal_get_messages();
    $response = new AjaxResponse();
    if (!$note->getParent()) {
      $command = new RemoveModerationNoteCommand($note);
    }
    else {
      $command = new RemoveCommand('[data-moderation-note-form-id="' . $note->id() . '"]');
    }
    $response->addCommand($command);
    return $response;
  }

}

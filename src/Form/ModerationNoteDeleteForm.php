<?php

namespace Drupal\moderation_notes\Form;

use Drupal\Core\Ajax\AjaxResponse;
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

    // Wrap our form so that our submit callback can re-render the form.
    $form['#prefix'] = '<div id="moderation-note-form-wrapper">';
    $form['#suffix'] = '</div>';

    /** @var \Drupal\moderation_notes\Entity\ModerationNote $moderation_note */
    $moderation_note = $this->entity;

    $form['#attached']['drupalSettings']['highlight_moderation_note'] = [
      'id' => $moderation_note->id(),
      'quote' => $moderation_note->getQuote(),
      'quote_offset' => $moderation_note->getQuoteOffset(),
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
    $response = new AjaxResponse();
    $command = new RemoveModerationNoteCommand($this->getEntity());
    $response->addCommand($command);
    return $response;
  }

}

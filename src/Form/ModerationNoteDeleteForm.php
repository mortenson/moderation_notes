<?php

namespace Drupal\moderation_notes\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

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
    $form['#attached']['drupalSettings']['moderation_note_deleted'] = $this->getEntity()->id();
    return $form;
  }

}

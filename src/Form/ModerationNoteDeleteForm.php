<?php

namespace Drupal\moderation_note\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\moderation_note\Ajax\RemoveModerationNoteCommand;

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
    return $this->t('<p>You are about to delete a note, this action cannot be undone.</p>');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\moderation_note\Entity\ModerationNote $note */
    $note = $this->entity;

    // Wrap our form so that our submit callback can re-render the form.
    $form['#prefix'] = '<div class="moderation-note-form-wrapper" data-moderation-note-form-id="' . $note->id() . '">';
    $form['#suffix'] = '</div>';

    $form['#attributes']['class'][] = 'moderation-note-form';
    $form['#attributes']['class'][] = 'moderation-note-form-delete';
    if ($this->entity->hasParent()) {
      $form['#attributes']['class'][] = 'moderation-note-form-reply';
    }

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
          'method' => 'replace',
          'disable-refocus' => TRUE,
        ],
      ],
      'cancel' => [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#executes_submit_callback' => FALSE,
        '#ajax' => [
          'callback' => '::cancelForm',
          'method' => 'replace',
          'disable-refocus' => TRUE,
        ],
      ],
    ];
  }

  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = $this->entity;
    $selector = '[data-moderation-note-form-id="' . $note->id() . '"]';
    $content = $this->entityTypeManager->getViewBuilder('moderation_note')->view($note);
    $command = new ReplaceCommand($selector, $content);

    $response->addCommand($command);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selector = '[data-moderation-note-form-id="' . $this->entity->id() . '"]';

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

    /** @var \Drupal\moderation_note\Entity\ModerationNote $note */
    $note = $this->entity;

    // Delete all Moderation Notes that are replies of this note.
    $replies = $note->getChildren();
    $this->entityTypeManager->getStorage('moderation_note')->delete($replies);

    // Clear the Drupal messages, as this form uses AJAX to display its
    // results. Displaying a deletion message on the next page the user visits
    // is awkward.
    drupal_get_messages();

    $response = new AjaxResponse();
    if (!$note->getParent()) {
      $command = new RemoveModerationNoteCommand($note);
      $response->addCommand($command);
      $command = new CloseDialogCommand('#drupal-offcanvas');
      $response->addCommand($command);
      // This message will only be visible if the note is displayed outside of
      // the modal context.
      $message = '<p>The moderation note and its replies have been deleted. To view the notated content, <a href="@url">click here</a>.</p>';
      $args = ['@url' => $note->getModeratedEntity()->toUrl()->toString()];
      $command = new ReplaceCommand('.moderation-note-sidebar-wrapper', $this->t($message, $args));
    }
    else {
      $command = new RemoveCommand($selector);
    }
    $response->addCommand($command);
    return $response;
  }

}

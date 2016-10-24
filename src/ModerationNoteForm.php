<?php

namespace Drupal\moderation_notes;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\moderation_notes\Ajax\AddModerationNoteCommand;
use Drupal\moderation_notes\Ajax\ReplyModerationNoteCommand;
use Drupal\moderation_notes\Ajax\ShowModerationNoteCommand;
use Drupal\moderation_notes\Entity\ModerationNote;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    if ($this->getOperation() === 'edit') {
      $form['#attached']['drupalSettings']['highlight_moderation_note'] = [
        'id' => $note->id(),
        'quote' => $note->getQuote(),
        'quote_offset' => $note->getQuoteOffset(),
      ];
    }

    if ($this->getOperation() === 'reply' || $this->entity->hasParent()) {
      $form['#attributes']['class'][] = 'moderation-note-form-reply';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->getOperation() === 'reply' ? $this->t('Reply') : $this->t('Save'),
      '#ajax' => [
        'callback' => '::submitForm',
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
      $form_id = $this->getOperation() === 'edit' ? $note->id() : $this->getOperation();
      $selector = '[data-moderation-note-form-id="' . $form_id . '"]';
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

<?php

namespace Drupal\moderation_notes;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

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

    $form['quote'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['visually-hidden'],
      ],
      '#default_value' => $moderation_note->getQuote(),
    ];

    $form['quote_offset'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['visually-hidden'],
      ],
      '#default_value' => $moderation_note->getQuoteOffset(),
    ];

    $form['text'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#default_value' => $moderation_note->getText(),
    ];

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

    if ($this->getOperation() === 'create') {
      $setting = [
        'field_id' => _moderation_notes_generate_field_id($note),
        'text' => $note->getText(),
        'quote' => $note->getQuote(),
        'quote_offset' => $note->getQuoteOffset(),
        'user' => $note->getOwner()->label(),
      ];
      $form['#attached']['drupalSettings']['moderation_notes'][$note->id()] = $setting;
    }
    else {
      $form['#attached']['drupalSettings']['moderation_note_edited'] = $note->id();
    }

    return $form;
  }

}

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

    $form['quote'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['visually-hidden'],
      ],
      '#default_value' => $moderation_note->getQuote(),
    ];

    $form['quote_offset'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['visually-hidden'],
      ],
      '#default_value' => $moderation_note->getQuoteOffset(),
    ];

    $form['text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Note'),
      '#default_value' => $moderation_note->getText(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

}

<?php

namespace Drupal\moderation_notes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\moderation_notes\Entity\ModerationNote;
use Drupal\moderation_notes\ModerationNoteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Endpoints for the Moderation Notes module.
 */
class ModerationNotesController extends ControllerBase {

  /**
   * The ModerationInformation service.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInfo;

  /**
   * Constructs a ModerationNotesController.
   *
   * @param \Drupal\content_moderation\ModerationInformation $moderation_information
   *   The ModerationInformation service.
   */
  public function __construct(ModerationInformation $moderation_information) {
    $this->moderationInfo = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_moderation.moderation_information')
    );
  }

  /**
   * Returns the form for a new Moderation Note.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity this note is related to.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   * @param string $field_name
   *   The name of the field that is being notated.
   * @param string $langcode
   *   The name of the language for which the field is being notated.
   * @param string $view_mode_id
   *   The view mode the field is rendered in.
   *
   * @return array
   *   A render array representing the form.
   */
  public function createNote(EntityInterface $entity, $field_name, $langcode, $view_mode_id, Request $request) {
    $values = [
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_field_name' => $field_name,
      'entity_langcode' => $langcode,
      'entity_view_mode_id' => $view_mode_id,
    ];
    $moderation_note = ModerationNote::create($values);
    $form = $this->entityFormBuilder()->getForm($moderation_note, 'create');
    $form['#attributes']['data-moderation-notes-new-form'] = TRUE;
    return $form;
  }

  /**
   * Views an individual moderation note.
   *
   * @param \Drupal\moderation_notes\ModerationNoteInterface $moderation_note
   *   The moderation note you want to view.
   *
   * @return array
   *   A render array representing the moderation note.
   */
  public function viewNote(ModerationNoteInterface $moderation_note) {
    $view_builder = $this->entityTypeManager()->getViewBuilder('moderation_note');
    $build = $view_builder->view($moderation_note);
    $build['#attached']['drupalSettings']['highlight_moderation_note'] = [
      'id' => $moderation_note->id(),
      'quote' => $moderation_note->getQuote(),
      'quote_offset' => $moderation_note->getQuoteOffset(),
    ];
    return $build;
  }

  /**
   * Deletes an individual moderation note.
   *
   * @param \Drupal\moderation_notes\ModerationNoteInterface $moderation_note
   *   The moderation note you want to delete.
   *
   * @return array
   *   A render array representing the deletion form.
   */
  public function deleteNote(ModerationNoteInterface $moderation_note) {
    return $this->entityFormBuilder()->getForm($moderation_note, 'delete');
  }

  /**
   * Edits an individual moderation note.
   *
   * @param \Drupal\moderation_notes\ModerationNoteInterface $moderation_note
   *   The moderation note you want to edit.
   *
   * @return array
   *   A render array representing the edit form.
   */
  public function editNote(ModerationNoteInterface $moderation_note) {
    return $this->entityFormBuilder()->getForm($moderation_note, 'edit');
  }

}

<?php

namespace Drupal\moderation_note\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Url;
use Drupal\moderation_note\Entity\ModerationNote;
use Drupal\moderation_note\ModerationNoteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Endpoints for the Moderation Note module.
 */
class ModerationNoteController extends ControllerBase {

  /**
   * The ModerationInformation service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  /**
   * The QueryFactory service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a ModerationNoteController.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The ModerationInformation service.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The QueryFactory service.
   */
  public function __construct($moderation_information, QueryFactory $query_factory) {
    $this->moderationInfo = $moderation_information;
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $moderation_info = $container->has('workbench_moderation.moderation_information') ? $container->get('workbench_moderation.moderation_information'): $container->get('content_moderation.moderation_information');
    return new static(
      $moderation_info,
      $container->get('entity.query')
    );
  }

  /**
   * Returns the form for a new Moderation Note.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity this note is related to.
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
  public function createNote(EntityInterface $entity, $field_name, $langcode, $view_mode_id) {
    $values = [
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_field_name' => $field_name,
      'entity_langcode' => $langcode,
      'entity_view_mode_id' => $view_mode_id,
    ];
    $moderation_note = ModerationNote::create($values);
    $form = $this->entityFormBuilder()->getForm($moderation_note, 'create');
    $form['#attributes']['data-moderation-note-new-form'] = TRUE;

    return $form;
  }

  /**
   * Views a moderation note, and all its replies.
   *
   * @param \Drupal\moderation_note\ModerationNoteInterface $moderation_note
   *   The moderation note you want to view.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array representing the moderation note.
   */
  public function viewNote(ModerationNoteInterface $moderation_note, Request $request) {
    $view_builder = $this->entityTypeManager()->getViewBuilder('moderation_note');
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['moderation-note-sidebar-wrapper']],
    ];

    // If this request was made from a preview, provide a return link.
    if ($request->get('from-preview', FALSE)) {
      $params = [
        'entity_type' => $moderation_note->getModeratedEntityTypeId(),
        'entity' => $moderation_note->getModeratedEntityId(),
      ];
      $build[] = [
        '#type' => 'link',
        '#url' => Url::fromRoute('moderation_note.list', $params),
        '#title' => $this->t('← Back'),
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'offcanvas',
        ],
      ];
    }

    $build[] = $view_builder->view($moderation_note);

    $replies = $moderation_note->getChildren();
    $build[] = $view_builder->viewMultiple($replies);

    if ($moderation_note->access('create')) {
      $new_note = ModerationNote::create([
        'parent' => $moderation_note,
        'entity_type' => $moderation_note->getModeratedEntityTypeId(),
        'entity_id' => $moderation_note->getModeratedEntityId(),
      ]);
      $build[] = $this->entityFormBuilder()->getForm($new_note, 'reply');
    }

    $build['#attached']['library'][] = 'moderation_note/main';

    $build['#attached']['drupalSettings']['highlight_moderation_note'] = [
      'id' => $moderation_note->id(),
      'quote' => $moderation_note->getQuote(),
      'quote_offset' => $moderation_note->getQuoteOffset(),
    ];

    return $build;
  }

  /**
   * Lists all top-level notes for the given Entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose notes you want to view.
   *
   * @return array
   *   A render array representing multiple moderation notes.
   */
  public function listNotes(EntityInterface $entity) {
    $build = [];

    $ids = $this->queryFactory->get('moderation_note')
      ->condition('entity_type', $entity->getEntityTypeId())
      ->condition('entity_id', $entity->id())
      ->notExists('parent')
      ->execute();

    if (empty($ids)) {
      $build[] = [
        '#markup' => $this->t('<p>There are no notes for this entity. Go create some!</p>'),
      ];
    }
    else {
      $view_builder = $this->entityTypeManager()->getViewBuilder('moderation_note');
      $notes = $this->entityTypeManager()->getStorage('moderation_note')->loadMultiple($ids);
      $build[] = $view_builder->viewMultiple($notes, 'preview');
    }

    $build['#attached']['library'][] = 'moderation_note/main';

    return $build;
  }

  /**
   * Deletes a moderation note.
   *
   * @param \Drupal\moderation_note\ModerationNoteInterface $moderation_note
   *   The moderation note you want to delete.
   *
   * @return array
   *   A render array representing the deletion form.
   */
  public function deleteNote(ModerationNoteInterface $moderation_note) {
    $response = new AjaxResponse();
    $selector = '[data-moderation-note-id="' . $moderation_note->id() . '"]';
    $content = $this->entityFormBuilder()->getForm($moderation_note, 'delete');
    $command = new ReplaceCommand($selector, $content);
    $response->addCommand($command);
    return $response;
  }

  /**
   * Edits a moderation note.
   *
   * @param \Drupal\moderation_note\ModerationNoteInterface $moderation_note
   *   The moderation note you want to edit.
   *
   * @return array
   *   A render array representing the edit form.
   */
  public function editNote(ModerationNoteInterface $moderation_note) {
    $response = new AjaxResponse();
    $selector = '[data-moderation-note-id="' . $moderation_note->id() . '"]';
    $content = $this->entityFormBuilder()->getForm($moderation_note, 'edit');
    $command = new ReplaceCommand($selector, $content);
    $response->addCommand($command);
    return $response;
  }

  /**
   * Replies to a moderation note.
   *
   * @param \Drupal\moderation_note\ModerationNoteInterface $moderation_note
   *   The moderation note you want to reply to.
   *
   * @return array
   *   A render array representing the deletion form.
   */
  public function replyToNote(ModerationNoteInterface $moderation_note) {
    $response = new AjaxResponse();
    $new_note = ModerationNote::create([
      'parent' => $moderation_note,
      'entity_type' => $moderation_note->getModeratedEntityTypeId(),
      'entity_id' => $moderation_note->getModeratedEntityId(),
    ]);
    $content = $this->entityFormBuilder()->getForm($new_note, 'reply');
    $command = new AppendCommand('.moderation-note-sidebar-wrapper', $content);
    $response->addCommand($command);
    return $response;
  }

}

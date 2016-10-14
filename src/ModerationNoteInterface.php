<?php

namespace Drupal\moderation_notes;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a moderation_note entity.
 */
interface ModerationNoteInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Determines if the note has a parent.
   *
   * @return bool
   *   TRUE if the note has a parent, FALSE otherwise.
   */
  public function hasParent();

  /**
   * Gets the parent note, if there is one.
   *
   * @return \Drupal\moderation_notes\ModerationNoteInterface|null
   *   The parent note, or NULL if this is not a reply.
   */
  public function getParent();

  /**
   * Gets the Entity that this note is related to.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Returns the annotated Entity, or NULL if one cannot be found.
   */
  public function getModeratedEntity();

  /**
   * Sets the Entity that this note is related to.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An Entity to reference.
   */
  public function setModeratedEntity($entity);

  /**
   * Gets the Entity Type ID for the notated Entity.
   *
   * @return string
   *   The Entity Type ID.
   */
  public function getModeratedEntityTypeId();

  /**
   * Gets the Entity ID for the notated Entity.
   *
   * @return integer
   *   The Entity ID.
   */
  public function getModeratedEntityId();

  /**
   * Sets which Entity this note is related to.
   *
   * @param string $entity_type_id
   *   An Entity Type ID.
   * @param int $entity_id
   *   An Entity ID.
   */
  public function setModeratedEntityById($entity_type_id, $entity_id);

  /**
   * Gets the field name this note is related to.
   *
   * @return string
   *   The machine name of an Entity field.
   */
  public function getEntityFieldName();

  /**
   * Sets the field name this note is related to.
   *
   * @param string $field_name
   *   The machine name of an entity field.
   */
  public function setEntityFieldName($field_name);

  /**
   * Gets the related entity's language.
   *
   * @return string
   *   A language's langcode.
   */
  public function getEntityLanguage();

  /**
   * Sets the related entity's language.
   *
   * @param string $langcode
   *   A language's langcode.
   */
  public function setEntityLanguage($langcode);

  /**
   * Gets the related entity's view mode.
   *
   * @return string
   *   The machine name of the view mode.
   */
  public function getEntityViewModeId();

  /**
   * Sets the related entity's view mode.
   *
   * @param string $view_mode_id
   *   The machine name of a view mode.
   */
  public function setEntityViewModeId($view_mode_id);

  /**
   * Gets the created timestamp for this note.
   *
   * @return integer
   *   The created timestamp.
   */
  public function getCreatedTime();

  /**
   * Gets the quoted text for this note.
   *
   * @return string
   *   The text that was selected when the note was created.
   */
  public function getQuote();

  /**
   * Sets the quoted text for this note.
   *
   * @param string $quote
   *   The text that was selected when the note was created.
   */
  public function setQuote($quote);

  /**
   * Gets the quoted text for this note.
   *
   * @return int
   *   The offset relative to the parent field element for this quote.
   */
  public function getQuoteOffset();

  /**
   * Sets the quoted text offset for this note.
   *
   * @param int $offset
   *   The offset relative to the parent field element for this quote.
   */
  public function setQuoteOffset($offset);

  /**
   * Gets the text content for this note.
   *
   * @return string
   *   The text content of this note.
   */
  public function getText();

  /**
   * Sets the text content for this note.
   *
   * @param string $text
   *   The text content of this note.
   */
  public function setText($text);

  /**
   * Gets the severity Entity associated with this note.
   *
   * @return \Drupal\moderation_notes\Entity\ModerationNoteSeverity|null
   *   The ModerationNoteSeverity Entity, or NULL if there is none.
   */
  public function getSeverity();

  /**
   * Gets the severity ID for this note.
   *
   * @return string
   *   The severity ID for this note.
   */
  public function getSeverityId();

  /**
   * Sets the severity ID for this note.
   *
   * @param string $id
   *   The severity ID for this note.
   */
  public function setSeverityId($id);

}

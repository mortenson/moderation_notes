<?php

namespace Drupal\moderation_notes;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a moderation_note entity.
 */
interface ModerationNoteInterface extends ContentEntityInterface {

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
   * Gets the author that created this note.
   *
   * @return \Drupal\user\Entity\User|null
   *   The author of this note, or NULL if there is no author.
   */
  public function getAuthor();

  /**
   * Gets the author's name.
   *
   * @return string|null
   *   The author's label, or NULL if there is no author.
   */
  public function getAuthorName();

  /**
   * Gets a full URL to the image that represents the Author.
   *
   * @return string
   *   A full URL that can be directly used in an <img> tag.
   */
  public function getAuthorImageUrl();

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

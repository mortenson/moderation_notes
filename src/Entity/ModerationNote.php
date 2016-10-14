<?php

namespace Drupal\moderation_notes\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\moderation_notes\ModerationNoteInterface;
use Drupal\user\UserInterface;

/**
 * Defines the moderation_note entity.
 *
 * @ContentEntityType(
 *   id = "moderation_note",
 *   label = @Translation("Moderation note"),
 *   handlers = {
 *     "access" = "Drupal\moderation_notes\AccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\moderation_notes\ModerationNoteForm",
 *     }
 *   },
 *   base_table = "moderation_note",
 *   admin_permission = "administer moderation notes",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 * )
 *
 */
class ModerationNote extends ContentEntityBase implements ModerationNoteInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'uid' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent'))
      ->setDescription(t('The parent Moderation Note if this is a reply.'))
      ->setSetting('target_type', 'moderation_note');

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity'))
      ->setDescription(t('The entity type this note is related to.'))
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH)
      ->setRequired(TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity'))
      ->setDescription(t('The entity id this note is related to.'))
      ->setRequired(TRUE);

    $fields['entity_field_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity'))
      ->setDescription(t('The field name this note is related to.'))
      ->setRequired(TRUE);

    $fields['entity_langcode'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity'))
      ->setDescription(t('The language this note is related to.'))
      ->setRequired(TRUE);

    $fields['entity_view_mode_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity'))
      ->setDescription(t('The entity view mode this note is related to.'))
      ->setRequired(TRUE);

    $fields['quote'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Quote'))
      ->setDescription(t('The quote that was selected, if applicable.'))
      ->setSetting('max_length', 255)
      ->setRequired(TRUE);

    $fields['quote_offset'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Quote Offset'))
      ->setDescription(t('The offset from the field ID for this quote.'))
      ->setRequired(TRUE);

    $fields['text'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Text'))
      ->setDescription(t('The text of the note.'))
      ->setSetting('max_length', FieldStorageConfig::NAME_MAX_LENGTH)
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setRequired(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the node was last edited.'))
      ->setRequired(TRUE);

    $fields['severity'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Severity'))
      ->setSetting('target_type', 'moderation_note_severity')
      ->setDescription(t('The severity for this note.'))
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function hasParent() {
    return (bool) $this->get('parent')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    if ($this->hasParent()) {
      return $this->get('parent')->entity;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getModeratedEntity() {
    $entity_type = $this->getModeratedEntityTypeId();
    $entity_id = $this->getModeratedEntityId();
    $storage = $this->entityTypeManager()->getStorage($entity_type);
    return $storage->load($entity_id);
  }

  /**
   * {@inheritdoc}
   */
  public function setModeratedEntity($entity) {
    if (!$entity->isNew()) {
      $this->set('entity_type', $entity->getEntityTypeId());
      $this->set('entity_id', $entity->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getModeratedEntityTypeId() {
    return $this->get('entity_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getModeratedEntityId() {
    return $this->get('entity_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setModeratedEntityById($entity_type_id, $entity_id) {
    if ($storage = $this->entityTypeManager()->getStorage($entity_type_id)) {
      if ($storage->load($entity_id)) {
        $this->set('entity_type', $entity_type_id);
        $this->set('entity_id', $entity_id);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFieldName() {
    return $this->get('entity_field_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityFieldName($field_name) {
    $this->set('entity_field_name', $field_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityLanguage() {
    return $this->get('entity_langcode')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityLanguage($langcode) {
    $this->set('entity_langcode', $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityViewModeId() {
    return $this->get('entity_view_mode_id')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityViewModeId($view_mode_id) {
    $this->set('entity_view_mode_id', $view_mode_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuote() {
    return $this->get('quote')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setQuote($quote) {
    $this->set('quote', $quote);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuoteOffset() {
    return $this->get('quote_offset')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuoteOffset($offset) {
    if (is_int($offset)) {
      $this->set('quote_offset', $offset);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getText() {
    return $this->get('text')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setText($text) {
    $this->set('text', $text);
  }

  /**
   * {@inheritdoc}
   */
  public function getSeverity() {
    return ModerationNoteSeverity::load($this->getSeverityId());
  }

  /**
   * {@inheritdoc}
   */
  public function getSeverityId() {
    return $this->get('severity')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setSeverityId($id) {
    if ($id === 'default') {
      $this->set('severity', NULL);
    }
    else if (ModerationNoteSeverity::load($id)) {
      $this->set('severity', $id);
    }
  }

}

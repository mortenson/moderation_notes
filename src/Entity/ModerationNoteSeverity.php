<?php

namespace Drupal\moderation_notes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Moderation severity entity.
 *
 * @ConfigEntityType(
 *   id = "moderation_note_severity",
 *   label = @Translation("Moderation note severity"),
 *   config_prefix = "moderation_note_severity",
 *   admin_permission = "administer moderation note severities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 */
class ModerationNoteSeverity extends ConfigEntityBase {

  /**
   * The severity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The severity label.
   *
   * @var string
   */
  protected $label;

  /**
   * The severity level, which can be used to visually modify the note.
   *
   * @var string
   */
  protected $severity_level;

  /**
   * The blocked Content Moderation transitions.
   *
   * @var array
   */
  protected $blocked_moderation_transitions = [];

}

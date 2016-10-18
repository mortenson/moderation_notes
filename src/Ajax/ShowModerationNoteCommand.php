<?php

namespace Drupal\moderation_notes\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\moderation_notes\ModerationNoteInterface;

/**
 * AJAX command to show a Moderation Note.
 */
class ShowModerationNoteCommand implements CommandInterface {

  /**
   * The Moderation Note.
   *
   * @var \Drupal\moderation_notes\ModerationNoteInterface
   */
  protected $moderation_note;

  /**
   * Constructs a \Drupal\moderation_notes\Ajax\AddModerationNoteCommand object.
   *
   * @param \Drupal\moderation_notes\ModerationNoteInterface $moderation_note
   *   The Moderation Note.
   */
  public function __construct(ModerationNoteInterface $moderation_note) {
    $this->moderation_note = $moderation_note;
  }

  /**
   * Implements \Drupal\Core\Ajax\CommandInterface::render().
   */
  public function render() {
    return [
      'command' => 'show_moderation_note',
      'id' => $this->moderation_note->id(),
    ];
  }

}

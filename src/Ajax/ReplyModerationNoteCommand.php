<?php

namespace Drupal\moderation_note\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\moderation_note\ModerationNoteInterface;

/**
 * AJAX command to reload the Moderation Note reply form.
 */
class ReplyModerationNoteCommand implements CommandInterface {

  /**
   * The Moderation Note.
   *
   * @var \Drupal\moderation_note\ModerationNoteInterface
   */
  protected $moderation_note;

  /**
   * Constructs a \Drupal\moderation_note\Ajax\ReplyModerationNoteCommand object.
   *
   * @param \Drupal\moderation_note\ModerationNoteInterface $moderation_note
   *   The parent Moderation Note.
   */
  public function __construct(ModerationNoteInterface $moderation_note) {
    $this->moderation_note = $moderation_note;
  }

  /**
   * Implements \Drupal\Core\Ajax\CommandInterface::render().
   */
  public function render() {
    return [
      'command' => 'reply_moderation_note',
      'id' => $this->moderation_note->id(),
    ];
  }

}

<?php

namespace Drupal\moderation_notes\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\moderation_notes\ModerationNoteInterface;

/**
 * AJAX command to add a Moderation Note.
 */
class AddModerationNoteCommand implements CommandInterface {

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
      'command' => 'add_moderation_note',
      'note' => [
        'field_id' => _moderation_notes_generate_field_id($this->moderation_note),
        'id' => $this->moderation_note->id(),
        'quote' => $this->moderation_note->getQuote(),
        'quote_offset' => $this->moderation_note->getQuoteOffset(),
      ],
    ];
  }

}

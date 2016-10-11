<?php

namespace Drupal\moderation_note\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Dynamic Entity Reference valid reference constraint.
 *
 * Verifies that no notes block the current transition.
 *
 * @Constraint(
 *   id = "ModerationNote",
 *   label = @Translation("Valid moderation notes", context = "Validation")
 * )
 */
class ModerationNote extends Constraint {

  public $message = 'You cannot @transition until you resolve all important notes';

}

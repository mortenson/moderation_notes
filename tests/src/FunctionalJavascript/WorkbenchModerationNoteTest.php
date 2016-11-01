<?php

namespace Drupal\Tests\moderation_note\FunctionalJavascript;

/**
 * Contains Moderation Note integration tests for Workbench Moderation.
 *
 * This is done in a separate test class so that Workbench Moderation errors
 * can be easily identified during test runs.
 *
 * @group moderation_note
 */
class WorkbenchModerationNoteTest extends ModerationNoteTest {

  /**
   * {@inheritdoc}
   */
  protected static $moderation_module = 'workbench_moderation';

}

<?php

namespace Drupal\Tests\moderation_notes\FunctionalJavascript;

/**
 * Contains Moderation Notes integration tests for Workbench Moderation.
 *
 * This is done in a separate test class so that Workbench Moderation errors
 * can be easily identified during test runs.
 *
 * @group moderation_notes
 */
class WorkbenchModerationNotesTest extends ModerationNotesTest {

  /**
   * {@inheritdoc}
   */
  protected static $moderation_module = 'workbench_moderation';

}

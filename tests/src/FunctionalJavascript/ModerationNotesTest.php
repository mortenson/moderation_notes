<?php

namespace Drupal\Tests\moderation_notes\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\moderation_notes\Entity\ModerationNote;

/**
 * Contains Moderation Notes integration tests.
 *
 * @group moderation_notes
 */
class ModerationNotesTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
  ];

  /**
   * Defines the moderation module used for this test.
   *
   * @var string
   */
  protected static $moderation_module = 'content_moderation';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('module_installer')->install([self::$moderation_module, 'moderation_notes'], TRUE);

    // Create a Content Type with moderation enabled.
    $node_type = $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $node_type->setThirdPartySetting(self::$moderation_module, 'enabled', TRUE);
    $node_type->setThirdPartySetting(self::$moderation_module, 'allowed_moderation_states', [
      'published',
      'draft',
      'archived',
    ]);
    $node_type->setThirdPartySetting(self::$moderation_module, 'default_moderation_state', 'published');
    $node_type->setNewRevision(TRUE);
    $node_type->save();

    // Add a plain text field for this content type.
    FieldStorageConfig::create([
      'field_name' => 'test_field',
      'entity_type' => 'node',
      'type' => 'string',
    ])->save();

    FieldConfig::create([
      'field_name' => 'test_field',
      'label' => 'Test Field',
      'entity_type' => 'node',
      'bundle' => 'article',
      'required' => FALSE,
      'settings' => [],
      'description' => '',
    ])->save();

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $entity_display */
    $entity_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node.article.default');
    $entity_display->setComponent('test_field')->save();

    // Create a user who can use Moderation Notes.
    $user = $this->drupalCreateUser([
      'access moderation notes',
      'create moderation notes',
      'access content',
      'create article content',
      'edit any article content',
    ]);
    $this->drupalLogin($user);

    drupal_flush_all_caches();
  }

  /**
   * Tests that notating entities is working as expected.
   */
  public function testModerationNote() {
    // Create a new article.
    $node = $this->createNode([
      'type' => 'article',
      'test_field' => [
        'value' => 'This is speled wrong',
      ],
    ]);

    // Create a Moderation Note that selects the misspelling.
    $note = ModerationNote::create([
      'entity_type' => 'node',
      'entity_id' => $node->id(),
      'entity_field_name' => 'test_field',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => 'speled',
      'quote_offset' => 0,
      'text' => 'Fix this mistake',
    ]);
    $note->save();

    // Make sure that the highlight is made and contains the misspelling.
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->assertWaitOnAjaxRequest();
    $element = $this->assertSession()->elementExists('css', '[data-moderation-note-highlight-id="' . $note->id() . '"]');
    $this->assertEquals($element->getText(), 'speled');
  }

}

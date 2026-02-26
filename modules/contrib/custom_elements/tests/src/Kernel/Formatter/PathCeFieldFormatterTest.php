<?php

namespace Drupal\Tests\custom_elements\Kernel\Formatter;

use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\Entity\EntityCeDisplay;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\content_translation\Traits\ContentTranslationTestTrait;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;

/**
 * Tests rendering of path fields with custom elements.
 *
 * @group custom_elements
 */
class PathCeFieldFormatterTest extends KernelTestBase {

  use ContentTranslationTestTrait;
  use CustomElementGeneratorTrait;
  use TestHelperTrait;

  /**
   * {@inheritdoc}
   *
   * @var bool
   * @todo Fix config schema for CE-displays and re-enable.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to install.
   *
   * @var string[]
   */
  protected static $modules = [
    'custom_elements',
    'user',
    'node',
    'field',
    'path',
    'path_alias',
    'system',
    // The following modules are required for content translation.
    'text',
    'language',
    'locale',
    'content_translation',
  ];

  /**
   * The node used in tests.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('path_alias');
    $this->installSchema('node', ['node_access']);

    // Enable content translation for the 'article' content type.
    $this->installConfig(['node', 'language', 'content_translation']);
    $this->createLanguageFromLangcode('de');

    // Create a content type with path field enabled.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();

    $this->enableContentTranslation('node', 'article');

    // Create the node.
    $this->node = Node::create([
      'type' => 'article',
      'title' => 'Test Node',
      'langcode' => 'en',
      'path' => [
        'alias' => '/test-path',
        'langcode' => 'en',
      ],
    ]);
    $this->node->save();

    // Configure CE display for node.
    EntityCeDisplay::create([
      'targetEntityType' => 'node',
      'customElementName' => 'article',
      'bundle' => 'article',
      'mode' => 'default',
    ])
      ->setComponent('title', [
        'field_name' => 'title',
        'is_slot' => FALSE,
        'formatter' => 'flattened',
      ])
      ->setComponent('url', [
        'field_name' => 'path',
        'is_slot' => FALSE,
        'formatter' => 'path',
        'configuration' => [
          'absolute' => FALSE,
        ],
      ])
      ->save();
  }

  /**
   * Changes configuration of the 'path' display component.
   *
   * @param bool $is_slot
   *   Value for is_slot property.
   * @param bool $absolute
   *   Whether the URL should be absolute.
   */
  protected function changePathDisplayComponent(bool $is_slot, bool $absolute = FALSE): void {
    EntityCeDisplay::load('node.article.default')
      ->setComponent('url', [
        'field_name' => 'path',
        'is_slot' => $is_slot,
        'formatter' => 'path',
        'configuration' => [
          'absolute' => $absolute,
        ],
      ])
      ->save();
  }

  /**
   * Tests output for a path field with relative URLs.
   */
  public function testRelativePath() {
    // Test as attribute (not slot).
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $this->assertEquals('/test-path', $custom_element->getAttribute('url'), 'Relative URL attribute is correct.');
    $this->assertNull($custom_element->getSlot('url'), 'Slot should not be set for attribute mode.');
    $this->assertNotNull($custom_element->getAttribute('url'), 'Attribute should be set for attribute mode.');

    // Test as slot.
    $this->changePathDisplayComponent(TRUE, FALSE);
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $this->assertNull($custom_element->getAttribute('url'), 'Attribute should not be set for slot mode.');
    $slot_content = $custom_element->getSlot('url');
    $this->assertIsArray($slot_content, 'Slot content should be an array.');
    $this->assertArrayHasKey('content', $slot_content, 'Slot content should have a content key.');
    $this->assertNotEmpty($slot_content['content'], 'Slot content should not be empty.');
    $this->assertEquals('/test-path', (string) $slot_content['content'], 'Relative URL slot content is correct.');
  }

  /**
   * Tests output for a path field with absolute URLs.
   */
  public function testAbsolutePath() {
    // Test as attribute.
    $this->changePathDisplayComponent(FALSE, TRUE);
    $base_url = $this->container->get('request_stack')
      ->getCurrentRequest()
      ->getSchemeAndHttpHost();
    $expected_url = $base_url . '/test-path';
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $this->assertEquals($expected_url, $custom_element->getAttribute('url'), 'Absolute URL attribute is correct.');
    $this->assertNull($custom_element->getSlot('url'), 'Slot should not be set for attribute mode.');
    $this->assertNotNull($custom_element->getAttribute('url'), 'Attribute should be set for attribute mode.');

    // Test as slot.
    $this->changePathDisplayComponent(TRUE, TRUE);
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $this->assertNull($custom_element->getAttribute('url'), 'Attribute should not be set for slot mode.');
    $slot_content = $custom_element->getSlot('url');
    $this->assertIsArray($slot_content, 'Slot content should be an array.');
    $this->assertArrayHasKey('content', $slot_content, 'Slot content should have a content key.');
    $this->assertNotEmpty($slot_content['content'], 'Slot content should not be empty.');
    $this->assertEquals($expected_url, (string) $slot_content['content'], 'Absolute URL slot content is correct.');
  }

  /**
   * Tests the settings summary for the path field formatter.
   */
  public function testSettingsSummary() {
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('path', [
        'absolute' => FALSE,
      ]);
    // Relative URL summary.
    $summary = array_map('strval', $formatter->settingsSummary());
    $this->assertContains('Relative URL', $summary);

    // Absolute URL summary.
    $formatter->setConfiguration([
      'absolute' => TRUE,
    ]);
    $summary = array_map('strval', $formatter->settingsSummary());
    $this->assertContains('Absolute URL', $summary);
  }

  /**
   * Tests that a URL is generated correctly for a translated node.
   */
  public function testPathWithTranslation() {
    // Set the path prefix for German.
    $config = \Drupal::configFactory()->getEditable('language.negotiation');
    $config->set('url.prefixes', ['de' => 'de'])->save();

    // Add a German translation for the node.
    $translated_node = $this->node->addTranslation('de', [
      'title' => 'Test Node DE',
      'path' => [
        'alias' => '/de/test-pfad',
        'langcode' => 'de',
      ],
    ]);
    $translated_node->save();

    // Generate the custom element for the German translation.
    $custom_element = $this->getCustomElementGenerator()
      ->generate($translated_node, 'default', 'de');
    $this->assertEquals('/de/test-pfad', $custom_element->getAttribute('url'), 'Relative URL attribute is correct for German translation');
  }

}

<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\custom_elements\CustomElement;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\KernelTests\KernelTestBase;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests Custom Elements Layout Builder integration via entity_ce_display.
 *
 * @group custom_elements
 */
class LayoutBuilderIntegrationTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'node',
    'text',
    'filter',
    'options',
    'image',
    'file',
    'media',
    'link',
    'layout_discovery',
    'layout_builder',
    'block',
    'block_content',
    'custom_elements',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('media');
    $this->installEntitySchema('node');
    $this->installEntitySchema('block_content');
    $this->installEntitySchema('entity_ce_display');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('layout_builder', ['inline_block_usage']);
    $this->installConfig(['system', 'field', 'node', 'filter', 'layout_builder', 'block', 'custom_elements']);

    // Mark layout_builder as installed to fix entity type alter hook
    // In kernel tests, modules are marked as SCHEMA_UNINSTALLED (-1) by
    // default. This causes custom_elements_entity_type_alter() to skip
    // changing the entity class because it thinks layout_builder is being
    // installed.
    \Drupal::service('update.update_hook_registry')->setInstalledVersion('layout_builder', 8000);

    // Create a content type.
    $this->createContentType(['type' => 'article', 'name' => 'Article']);

    // Force entity type manager to rebuild after marking layout_builder as
    // installed.
    \Drupal::entityTypeManager()->clearCachedDefinitions();

    // Enable Layout Builder for the article content type.
    $this->enableLayoutBuilderForContentType('article');
  }

  /**
   * Enables Layout Builder for a given content type.
   */
  protected function enableLayoutBuilderForContentType(string $content_type): void {
    // Delete existing view display if it exists.
    $view_display = EntityViewDisplay::load("node.{$content_type}.default");
    if ($view_display) {
      $view_display->delete();
    }

    // Force entity type manager to clear cached definitions again.
    \Drupal::entityTypeManager()->clearCachedDefinitions();

    // Create new view display with the correct class.
    $view_display = EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => $content_type,
      'mode' => 'default',
      'status' => TRUE,
    ]);

    // Enable Layout Builder.
    $view_display->enableLayoutBuilder()
      ->setOverridable()
      ->save();
  }

  /**
   * Creates a layout with sections and components.
   */
  protected function createLayoutWithSections(): array {
    // Create a two-column layout section.
    $section = new Section('layout_twocol_section');

    $component1 = new SectionComponent(\Drupal::service('uuid')->generate(), 'first', [
      'id' => 'system_powered_by_block',
      'label' => 'Powered by Drupal',
      'provider' => 'system',
      'label_display' => 'visible',
    ]);
    $section->appendComponent($component1);

    $component2 = new SectionComponent(\Drupal::service('uuid')->generate(), 'second', [
      'id' => 'system_branding_block',
      'label' => 'Site branding',
      'provider' => 'system',
      'label_display' => 'visible',
    ]);
    $section->appendComponent($component2);

    return [$section];
  }

  /**
   * Tests EntityCeDisplay with Layout Builder integration.
   *
   * This test verifies that when an EntityCeDisplay is configured with
   * useLayoutBuilder = TRUE, the Custom Elements generator properly
   * converts Layout Builder sections into drupal-layout custom elements
   * with nested custom elements for the block content.
   */
  public function testEntityCeDisplayWithLayoutBuilder(): void {
    // Create EntityCeDisplay with Layout Builder support enabled.
    $entity_ce_display = \Drupal::entityTypeManager()
      ->getStorage('entity_ce_display')
      ->create([
        'id' => 'node.article.default',
        'targetEntityType' => 'node',
        'bundle' => 'article',
        'mode' => 'default',
        'customElementName' => 'drupal-article',
        'useLayoutBuilder' => TRUE,
        'status' => TRUE,
      ]);
    $entity_ce_display->save();

    // Create a node with Layout Builder sections.
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'Layout Builder CE Display Test',
      'body' => [
        'value' => 'Testing Layout Builder with Custom Elements Display.',
        'format' => 'plain_text',
      ],
    ]);

    // Add layout overrides with two-column layout.
    $sections = $this->createLayoutWithSections();
    $layout_builder_field = $node->get(OverridesSectionStorage::FIELD_NAME);
    foreach ($sections as $section) {
      $layout_builder_field->appendSection($section);
    }
    $node->save();

    // Use the actual Custom Elements generator to render the entity via
    // EntityCeDisplay.
    $generator = \Drupal::service('custom_elements.generator');
    $custom_element = $generator->generate($node, 'default');

    // Assert that we have a custom element with the correct tag.
    $this->assertInstanceOf(CustomElement::class, $custom_element);
    $this->assertEquals('drupal-article', $custom_element->getTag());

    $sections_slot = $custom_element->getSlot('sections', 0);
    $this->assertNotNull($sections_slot, 'Custom element should have a sections slot');
    $this->assertArrayHasKey('content', $sections_slot, 'Sections slot should have content');

    $layout_element = $sections_slot['content'];
    $this->assertInstanceOf(CustomElement::class, $layout_element);
    $this->assertStringStartsWith('drupal-layout', $layout_element->getTag());

    // Access layout regions using getSlot() with hard-coded indices
    // First region contains the first block (system_powered_by_block)
    $first_region_slot = $layout_element->getSlot('first', 0);
    $this->assertNotNull($first_region_slot, 'Layout should have a first region at index 0');
    $this->assertArrayHasKey('content', $first_region_slot, 'First region slot should have content');

    $first_region_content = $first_region_slot['content'];
    $this->assertInstanceOf(CustomElement::class, $first_region_content,
      'First region should contain a custom element');
    $this->assertEquals('drupal-markup', $first_region_content->getTag(),
      'First region should contain a drupal-markup element with block content');

    // Second region contains the second block (system_branding_block)
    $second_region_slot = $layout_element->getSlot('second', 0);
    $this->assertNotNull($second_region_slot, 'Layout should have a second region at index 0');
    $this->assertArrayHasKey('content', $second_region_slot, 'Second region slot should have content');

    $second_region_content = $second_region_slot['content'];
    $this->assertInstanceOf(CustomElement::class, $second_region_content,
      'Second region should contain a custom element');
    $this->assertEquals('drupal-markup', $second_region_content->getTag(),
      'Second region should contain a drupal-markup element with block content');

    // Verify both regions have actual block content.
    $this->assertNotEmpty($first_region_content->getSlots(), 'First region element should have content slots');
    $this->assertNotEmpty($second_region_content->getSlots(), 'Second region element should have content slots');
  }

}

<?php

namespace Drupal\Tests\custom_elements\Kernel\Formatter;

use Drupal\custom_elements\CustomElement;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Render\FilteredMarkup;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Auto custom element field formatter.
 *
 * @group custom_elements
 */
class AutoCeFieldFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'field',
    'text',
    'filter',
    'user',
    'entity_test',
    'custom_elements',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');
    $this->installConfig(['custom_elements']);
  }

  /**
   * Tests Auto formatter converts string field attribute to slot.
   *
   * String fields are processed as attributes by default.
   * When isSlot=TRUE, formatter should convert attribute to slot.
   */
  public function testAutoFormatterConvertsStringAttributeToSlot() {
    // String field - processor adds as attribute by default.
    FieldStorageConfig::create([
      'field_name' => 'field_string',
      'entity_type' => 'entity_test',
      'type' => 'string',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_string',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();

    $entity = EntityTest::create([
      'field_string' => 'String value',
    ]);
    $entity->save();

    $field_definition = $entity->get('field_string')->getFieldDefinition();

    // First verify processor behavior without isSlot override.
    // Processor should add as attribute for string field.
    $element = new CustomElement();
    $generator = \Drupal::service('custom_elements.generator');
    $generator->process($entity->get('field_string'), $element, 'default', 'testkey');
    $value = $element->getAttribute('testkey');
    $this->assertNotNull($value, 'String field processor adds as attribute');

    // Now test with isSlot=TRUE - formatter should convert to slot.
    $element = new CustomElement();
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('auto', [
        'field_definition' => $field_definition,
        'name' => 'myfield',
        'is_slot' => TRUE,
        'view_mode' => 'default',
      ]);
    $formatter->build($entity->field_string, $element);

    $this->assertNull($element->getAttribute('myfield'), 'Attribute removed when isSlot=TRUE');
    $slot_data = $element->getSlot('myfield');
    $this->assertNotNull($slot_data, 'Content moved to slot when isSlot=TRUE');
    $this->assertIsArray($slot_data);
    $this->assertArrayHasKey('content', $slot_data);
    $this->assertEquals('String value', (string) $slot_data['content']);

    // Also test isSlot=FALSE (no conversion needed, stays as attribute).
    $element = new CustomElement();
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('auto', [
        'field_definition' => $field_definition,
        'name' => 'myfield',
        'is_slot' => FALSE,
        'view_mode' => 'default',
      ]);
    $formatter->build($entity->field_string, $element);

    $attr_value_direct = $element->getAttribute('myfield');
    $this->assertNotNull($attr_value_direct, 'String field stays as attribute when isSlot=FALSE');
    $this->assertEquals('String value', $attr_value_direct);
    $slot_check = $element->getSlot('myfield');
    $this->assertNull($slot_check, 'Not in slot when isSlot=FALSE');
  }

  /**
   * Tests Auto formatter converts text field slot to attribute.
   *
   * Text fields are processed as slots by default (nested CustomElement).
   * When isSlot=FALSE, formatter should convert slot to attribute.
   */
  public function testAutoFormatterConvertsTextSlotToAttribute() {
    // Set up a text field - processor adds as slot by default.
    $this->installConfig(['filter']);

    FieldStorageConfig::create([
      'field_name' => 'field_text',
      'entity_type' => 'entity_test',
      'type' => 'text_long',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_text',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();

    $entity = EntityTest::create([
      'field_text' => [
        'value' => 'Text content',
        'format' => 'plain_text',
      ],
    ]);
    $entity->save();

    // First verify processor behavior without isSlot override.
    // Processor should add as slot for text field.
    $element = new CustomElement();
    $generator = \Drupal::service('custom_elements.generator');
    $generator->process($entity->get('field_text'), $element, 'default', 'testkey');
    $slot = $element->getSlot('testkey');
    $this->assertNotNull($slot, 'Text field processor adds as slot');
    $this->assertInstanceOf(FilteredMarkup::class, $slot['content']);

    // Now test with isSlot=FALSE - formatter should convert to attribute.
    $element = new CustomElement();
    $field_definition = $entity->get('field_text')->getFieldDefinition();
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('auto', [
        'field_definition' => $field_definition,
        'name' => 'mytext',
        'is_slot' => FALSE,
        'view_mode' => 'default',
      ]);
    $formatter->build($entity->get('field_text'), $element);

    $slot_value = $element->getSlot('mytext');
    $this->assertNull($slot_value, 'Slot removed when isSlot=FALSE');
    $attr_value = $element->getAttribute('mytext');
    $this->assertNotNull($attr_value, 'Slot converted to attribute when isSlot=FALSE');
    $this->assertArrayHasKey(0, $attr_value);
    $this->assertStringContainsString('Text content', $attr_value[0]);

    // Also test isSlot=TRUE (no conversion needed, stays as slot).
    $element = new CustomElement();
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('auto', [
        'field_definition' => $field_definition,
        'name' => 'mytext',
        'is_slot' => TRUE,
        'view_mode' => 'default',
      ]);
    $formatter->build($entity->get('field_text'), $element);

    $slot_value_direct = $element->getSlot('mytext');
    $this->assertNotNull($slot_value_direct, 'Text field stays as slot when isSlot=TRUE');
    $this->assertIsArray($slot_value_direct);
    $this->assertArrayHasKey('content', $slot_value_direct);
    $this->assertStringContainsString('Text content', (string) $slot_value_direct['content']);
    $attr_check = $element->getAttribute('mytext');
    $this->assertNull($attr_check, 'Not in attribute when isSlot=TRUE');
  }

  /**
   * Tests Auto formatter with field names containing underscores.
   */
  public function testAutoFormatterWithUnderscoreFieldNames() {
    // Set up a text field with underscores in the name.
    $this->installConfig(['filter']);

    FieldStorageConfig::create([
      'field_name' => 'field_with_under_scores',
      'entity_type' => 'entity_test',
      'type' => 'text_long',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_with_under_scores',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();

    $entity = EntityTest::create([
      'field_with_under_scores' => [
        'value' => 'Content with underscores',
        'format' => 'plain_text',
      ],
    ]);
    $entity->save();

    $field_definition = $entity->get('field_with_under_scores')->getFieldDefinition();

    // Test slot-to-attribute conversion with underscore field name.
    // The field name "field_with_under_scores" should become "withUnderScores"
    // in camelCase (after removing "field_" prefix).
    $element = new CustomElement();
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('auto', [
        'field_definition' => $field_definition,
        'name' => 'with_under_scores',
        'is_slot' => FALSE,
        'view_mode' => 'default',
      ]);
    $formatter->build($entity->get('field_with_under_scores'), $element);

    // Verify slot was removed and attribute was set correctly.
    $slot_value = $element->getSlot('with_under_scores');
    $this->assertNull($slot_value, 'Slot removed when converting to attribute');
    $attr_value = $element->getAttribute('with_under_scores');
    $this->assertNotNull($attr_value, 'Attribute set after slot-to-attribute conversion');
    $this->assertIsArray($attr_value, 'Attribute contains array value');
    $this->assertArrayHasKey(0, $attr_value);
    $this->assertStringContainsString('Content with underscores', $attr_value[0]);
  }

}

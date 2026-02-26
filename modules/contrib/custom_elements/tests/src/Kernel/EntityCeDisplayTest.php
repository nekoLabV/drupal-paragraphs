<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\CustomElementsFieldFormatterInterface;
use Drupal\custom_elements\Entity\EntityCeDisplay;

/**
 * Tests the entity custom element display configuration entities.
 *
 * @group custom_elements
 */
class EntityCeDisplayTest extends KernelTestBase {

  use CustomElementGeneratorTrait;
  use StringTranslationTrait;

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
    'entity_test',
    'user',
    'field',
    'field_test',
    'text',
  ];

  /**
   * Tests basic CRUD operations on entity custom element display objects.
   */
  public function testEntityCeDisplayCrud() {
    $ce_display = EntityCeDisplay::create([
      'targetEntityType' => 'entity_test',
      'customElementName' => 'name_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
    ]);

    // Check that arbitrary options are correctly stored.
    $expected['component_1'] = [
      'weight' => 10,
      'field_name' => 'component',
      'configuration' => [
        'third_party_settings' => ['field_test' => ['foo' => 'bar']],
        'settings' => [],
      ],
    ];
    $ce_display->setComponent('component_1', $expected['component_1']);
    $this->assertEquals($expected['component_1'], $ce_display->getComponent('component_1'));

    // Check that the display can be properly saved and read back.
    $ce_display->save();

    // Load the entity.
    $ce_display = EntityCeDisplay::load($ce_display->id());
    $this->assertNotNull($ce_display, 'The entity was loaded successfully.');
    $this->assertEquals($expected['component_1'], $ce_display->getComponent('component_1'));

    // Testing saving auto-processing.
    $ce_display->setForceAutoProcessing(TRUE);
    $ce_display->save();
    $ce_display = EntityCeDisplay::load($ce_display->id());
    $this->assertTrue($ce_display->getForceAutoProcessing());

    // Delete the entity.
    $ce_display_id = $ce_display->id();
    $ce_display->delete();
    $this->assertNull(EntityCeDisplay::load($ce_display_id), 'The entity was deleted successfully.');
  }

  /**
   * Tests config of core field formatters is correctly used.
   */
  public function testCoreFieldFormatterComponents() {
    $ce_display = EntityCeDisplay::create([
      'targetEntityType' => 'entity_test',
      'customElementName' => 'name_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
    ]);
    $ce_display->setComponent('user', [
      'field_name' => 'user_id',
      'is_slot' => TRUE,
      'formatter' => 'field:entity_reference_entity_view',
      'configuration' => [
        'settings' => ['view_mode' => 'some_teaser'],
      ],
    ]);
    // Save and load to make sure config stays.
    $ce_display->save();
    $ce_display = EntityCeDisplay::load($ce_display->id());
    $formatter = $ce_display->getRenderer('user');
    $this->assertEquals('user', $formatter->getConfiguration()['name']);
    $this->assertEquals(TRUE, $formatter->getConfiguration()['is_slot']);
    $this->assertInstanceOf(CustomElementsFieldFormatterInterface::class, $formatter);
    $this->assertEquals(
      [$this->t('Rendered as @mode', ['@mode' => 'some_teaser'])],
      $formatter->settingsSummary()
    );
    $this->assertEquals('some_teaser', $formatter->getSetting('view_mode'));
  }

  /**
   * Tests that field deletion removes component but keeps the display config.
   */
  public function testOnDependencyRemoval() {
    $this->installEntitySchema('entity_test');
    $this->installConfig(['field']);

    // Create a field config.
    $field_storage = \Drupal::entityTypeManager()->getStorage('field_storage_config')->create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'type' => 'text',
    ]);
    $field_storage->save();

    $field_config = \Drupal::entityTypeManager()->getStorage('field_config')->create([
      'field_name' => 'field_test',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ]);
    $field_config->save();

    // Create a CE display with multiple fields including the test field.
    $ce_display = EntityCeDisplay::create([
      'targetEntityType' => 'entity_test',
      'customElementName' => 'test_element',
      'bundle' => 'entity_test',
      'mode' => 'default',
    ]);
    $ce_display->setComponent('name', [
      'field_name' => 'name',
      'is_slot' => FALSE,
      'formatter' => 'auto',
    ]);
    $ce_display->setComponent('field_test', [
      'field_name' => 'field_test',
      'is_slot' => FALSE,
      'formatter' => 'auto',
    ]);
    $ce_display->save();

    // Store the ID for reloading.
    $ce_display_id = $ce_display->id();

    // Verify both components exist.
    $this->assertNotNull($ce_display->getComponent('name'));
    $this->assertNotNull($ce_display->getComponent('field_test'));

    // Delete the field - this should trigger onDependencyRemoval.
    $field_config->delete();

    // Reload the display - it should still exist.
    $ce_display = EntityCeDisplay::load($ce_display_id);
    $this->assertNotNull($ce_display, 'CE display config was not deleted when field was removed.');

    // The field_test component should be removed.
    $this->assertNull($ce_display->getComponent('field_test'), 'Deleted field component was removed from display.');

    // But other components should remain.
    $this->assertNotNull($ce_display->getComponent('name'), 'Other components remain after field deletion.');
  }

}

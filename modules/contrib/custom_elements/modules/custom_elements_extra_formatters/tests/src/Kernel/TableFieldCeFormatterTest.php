<?php

namespace Drupal\Tests\custom_elements_extra_formatters\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\Entity\EntityCeDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * Tests rendering of extra formatter fields.
 *
 * @group custom_elements
 */
class TableFieldCeFormatterTest extends KernelTestBase {

  use CustomElementGeneratorTrait;
  use NodeCreationTrait;
  use TestHelperTrait;
  use UserCreationTrait;

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
    'custom_elements_extra_formatters',
    'field',
    'tablefield',
    'user',
    'node',
    'text',
    'system',
    'filter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('node');
    $this->installConfig('node');
    $type = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
    $type->save();

    // $this->installConfig('user');
  }

  /**
   * Create a user with a tablefield.
   *
   * @return \Drupal\user\Entity\User
   *   A node with a tablefield.
   */
  protected function setUpUser() :User {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_tablefield_test',
      'type' => 'tablefield',
      'cardinality' => 1,
      'entity_type' => 'user',
      'settings' => [],
      'locked' => FALSE,
      'custom_storage' => FALSE,
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_name' => 'field_tablefield_test',
      'entity_type' => 'user',
      'bundle' => 'user',
      'label' => 'Tablefield test',
      'settings' => [
        'handler' => 'default',
      ],
    ]);
    $field->save();
    // Create CE config with is_slot=TRUE for the reference fields. Callers can
    // change it.
    EntityCeDisplay::create([
      'targetEntityType' => 'user',
      'customElementName' => 'user',
      'bundle' => 'user',
      'mode' => 'default',
    ])
      ->setComponent('title', [
        'field_name' => 'title',
        'is_slot' => FALSE,
        'formatter' => 'flattened',
      ])
      ->setComponent('test-tablefield', [
        'field_name' => 'field_tablefield_test',
        'is_slot' => FALSE,
        'formatter' => 'ce_tablefield',
        'configuration' => [
          'mode' => 'full',
        ],
      ])
      ->save();
    return $this->createUser([], 'test', FALSE, [
      'field_tablefield_test' => [
        [
          'value' => [
            ['row 0, col 0', 'row 0, col 1', 'weight' => 0],
            ['row 1, col 0', 'row 1, col 1', 'weight' => 1],
            ['row 3, col 0', 'row 3, col 1', 'weight' => 3],
            ['row 2, col 0', 'row 2, col 1', 'weight' => 2],
          ],
          'caption' => 'Test table caption',
        ],
      ],
    ]);
  }

  /**
   * Create a node with a tablefield.
   *
   * @return \Drupal\node\NodeInterface
   *   A node with a tablefield.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUpNode() :NodeInterface {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_tablefield_test',
      'type' => 'tablefield',
      'cardinality' => 2,
      'entity_type' => 'node',
      'settings' => [
        'empty_rules' => [
          'ignore_table_header' => TRUE,
          'ignore_table_structure' => FALSE,
        ],
      ],
      'locked' => FALSE,
      'custom_storage' => FALSE,
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_name' => 'field_tablefield_test',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'Tablefield test',
      'settings' => [
        'handler' => 'default',
        'empty_rules' => [
          'ignore_table_header' => TRUE,
          'ignore_table_structure' => FALSE,
        ],
      ],
    ]);
    $field->save();
    // Create CE config with is_slot=TRUE for the reference fields. Callers can
    // change it.
    EntityCeDisplay::create([
      'targetEntityType' => 'node',
      'customElementName' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'useLayoutBuilder' => FALSE,
      'content' => [
        'created' => [
          'is_slot' => FALSE,
        ],
        'uid' => [
          'is_slot' => FALSE,
        ],
        'title' => [
          'is_slot' => FALSE,
        ],
      ],
      'forceAutoProcessing' => FALSE,

    ])
      ->setComponent('title', [
        'field_name' => 'title',
        'is_slot' => FALSE,
        'formatter' => 'flattened',
      ])
      ->setComponent('test-tablefield', [
        'field_name' => 'field_tablefield_test',
        'is_slot' => FALSE,
        'formatter' => 'ce_tablefield',
        'configuration' => [
          'mode' => 'full',
          'row_header' => TRUE,
          'column_header' => FALSE,
        ],
      ])
      ->save();
    return $this->createNode([
      'title' => 'Test tablefield node',
      'type' => 'page',
      'field_tablefield_test' => [
        [
          'value' => [
            ['header 0', 'header 1', 'weight' => 0],
            ['row 0, col 0', 'row 0, col 1', 'weight' => 1],
            ['row 1, col 0', 'row 1, col 1', 'weight' => 2],
            ['row 2, col 0', 'row 2, col 1', 'weight' => 3],
          ],
          'caption' => 'Test table caption',
        ],
        [
          'value' => [
            ['a5', 'b5', 'c5', 'weight' => 5],
            ['a2', 'b2', 'c2', 'weight' => 2],
            ['header a', 'header b', 'header c', 'weight' => 0],
            ['a3', 'b3', 'c3', 'weight' => 3],
          ],
          'caption' => 'Second table on node',
        ],
      ],
    ]);
  }

  /**
   * Test the custom elements table field formatter.
   */
  public function testTableFieldCeFormatter() {
    $user = $this->setUpUser();
    $custom_element_user = $this->getCustomElementGenerator()->generate($user, 'full');
    $tablefield = $custom_element_user->getAttribute('test-tablefield');
    // Cardinality of a tablefield is 1.
    $this->assertArrayHasKey('value', $tablefield, 'Value key is present in the tablefield data.');
    $this->assertArrayNotHasKey('weight', $tablefield['value'][1], 'Weight key was removed from the table row.');
    $this->assertSame('Test table caption', $tablefield['caption'], 'Caption of the table is correct.');
    $this->assertSame('row 1, col 0', $tablefield['value'][1][0], 'Table data meets expectations.');
    $tested_markup = $this->renderCustomElement($custom_element_user);
    $this->assertStringContainsString('row 1, col 1', (string) $tested_markup, 'Rendered table contains table data.');
    // Test the sort order.
    $this->assertSame('row 2, col 1', $tablefield['value'][2][1], 'Table rows are sorted based on weight.');
    $this->assertSame('row 3, col 1', $tablefield['value'][3][1], 'Table rows are sorted based on weight.');
    $node = $this->setUpNode();
    $custom_element_node = $this->getCustomElementGenerator()->generate($node, 'full');
    $tablefield = $custom_element_node->getAttribute('test-tablefield');
    // Cardinality of a tablefield is 2.
    $this->assertArrayHasKey('data', $tablefield, 'Data key is present in the tablefield custom element.');
    $this->assertArrayHasKey('value', $tablefield['data'][0], 'Value key is present in the tablefield data.');
    $this->assertArrayHasKey('value', $tablefield['data'][1], 'Value key is present in the tablefield data.');
    $this->assertSame('Second table on node', $tablefield['data'][1]['caption'], 'Caption of the table is correct.');
    $this->assertArrayNotHasKey('weight', $tablefield['data'][1]['value'][1], 'Weight key was removed from the table row.');
    $this->assertSame('header b', $tablefield['data'][1]['value'][0][1], 'Header value is present in the tablefield data.');
    $this->assertTrue($tablefield['settings']['row_header'], 'Formatter settings are correct.');
    $this->assertFalse($tablefield['settings']['column_header'], 'Formatter settings are correct.');
    $this->assertSame('b2', $tablefield['data'][1]['value'][1][1], 'Table rows are sorted based on weight.');
    $this->assertSame('b3', $tablefield['data'][1]['value'][2][1], 'Table rows are sorted based on weight.');
    $this->assertSame('b5', $tablefield['data'][1]['value'][3][1], 'Table rows are sorted based on weight.');
  }

}

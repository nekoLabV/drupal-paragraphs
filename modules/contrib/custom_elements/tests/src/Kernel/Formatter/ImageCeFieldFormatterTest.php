<?php

namespace Drupal\Tests\custom_elements\Kernel\Formatter;

use Drupal\Core\Render\Markup;
use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\Entity\EntityCeDisplay;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\image\Entity\ImageStyle;
use Drupal\Tests\content_translation\Traits\ContentTranslationTestTrait;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;

/**
 * Tests rendering of image fields with custom elements.
 *
 * @group custom_elements
 */
class ImageCeFieldFormatterTest extends KernelTestBase {

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
    'text',
    'file',
    'image',
    'path',
    'system',
  ];

  /**
   * The node used in tests.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The image style used in tests.
   *
   * @var \Drupal\image\ImageStyleInterface
   */
  protected $imageStyle;

  /**
   * The field name used for the image field.
   *
   * @var string
   */
  protected $fieldName = 'field_image';

  /**
   * The expected width after applying the image style.
   *
   * @var int
   */
  protected $expectedWidth;

  /**
   * The expected height after applying the image style.
   *
   * @var int
   */
  protected $expectedHeight;

  /**
   * Calculates the expected width and height for a given image file URI.
   *
   * @param string $uri
   *   The image file URI.
   * @param int $max_width
   *   The max width of the image style.
   * @param int $max_height
   *   The max height of the image style.
   *
   * @return array
   *   Array with two elements: [expected_width, expected_height].
   */
  protected function calculateExpectedDimensions($uri, $max_width = 120, $max_height = 90) {
    $image_factory = \Drupal::service('image.factory');
    $image = $image_factory->get($uri);
    if (!$image->isValid()) {
      $this->fail('Sample image is invalid, cannot determine expected dimensions.');
    }
    $orig_width = $image->getWidth();
    $orig_height = $image->getHeight();
    $aspect = $orig_width / $orig_height;
    if ($max_width / $max_height > $aspect) {
      // Limited by height.
      $expected_height = $max_height;
      $expected_width = (int) round($max_height * $aspect);
    }
    else {
      // Limited by width.
      $expected_width = $max_width;
      $expected_height = (int) round($max_width / $aspect);
    }
    return [$expected_width, $expected_height];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'image']);

    // Create a content type.
    $type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $type->save();

    // Add an image field to the article content type.
    $this->createImageField($this->fieldName, 'article');

    // Create an image style with a scale effect to 4:3 aspect ratio (120x90).
    $this->imageStyle = ImageStyle::create([
      'name' => 'test_style',
      'label' => 'Test style',
      'effects' => [],
    ]);
    $this->imageStyle->addImageEffect([
      'id' => 'image_scale',
      'data' => [
        'width' => 120,
        'height' => 90,
        'upscale' => FALSE,
      ],
      'weight' => 0,
    ]);
    $this->imageStyle->save();

    // Create a node with the image field set using generateSampleItems().
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test Node',
    ]);
    $node->save();
    $node->{$this->fieldName}->generateSampleItems(1);
    $node->save();
    $this->node = $node;

    // Calculate expected dimensions for the styled image.
    $image_item = $this->node->{$this->fieldName}->first();
    $file = $image_item->entity;
    $uri = $file->getFileUri();
    [$this->expectedWidth, $this->expectedHeight] = $this->calculateExpectedDimensions($uri);

    // Configure CE display for node.
    EntityCeDisplay::create([
      'targetEntityType' => 'node',
      'customElementName' => 'article',
      'bundle' => 'article',
      'mode' => 'default',
    ])
      ->setComponent('image', [
        'field_name' => $this->fieldName,
        'is_slot' => FALSE,
        'formatter' => 'image',
        'configuration' => [
          'image_style' => 'test_style',
          'flatten' => TRUE,
          'flatten_skip_prefix' => FALSE,
        ],
      ])
      ->save();
  }

  /**
   * Helper to add an image field to a bundle.
   */
  protected function createImageField($field_name, $bundle) {
    $this->container->get('entity_type.manager')
      ->getStorage('field_storage_config')
      ->create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'type' => 'image',
        'cardinality' => 1,
      ])
      ->save();
    $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'bundle' => $bundle,
        'label' => 'Image',
        'settings' => [
          'alt_field' => TRUE,
          'title_field' => TRUE,
        ],
      ])
      ->save();
  }

  /**
   * Changes configuration of the 'image' display component.
   *
   * @param bool $is_slot
   *   Value for is_slot property.
   * @param bool $flatten
   *   Whether to flatten output.
   * @param bool $flatten_skip_prefix
   *   Whether to skip prefix in flatten mode.
   */
  protected function changeImageDisplayComponent($is_slot, $flatten = TRUE, $flatten_skip_prefix = FALSE) {
    EntityCeDisplay::load('node.article.default')
      // Remove title component to avoid conflicts.
      ->removeComponent('title')
      ->setComponent('image', [
        'field_name' => $this->fieldName,
        'is_slot' => $is_slot,
        'formatter' => 'image',
        'configuration' => [
          'image_style' => 'test_style',
          'flatten' => $flatten,
          'flatten_skip_prefix' => $flatten_skip_prefix,
        ],
      ])
      ->save();
  }

  /**
   * Tests output for image field as attribute (flattened, with prefix).
   */
  public function testFlattenedAttributeWithPrefix() {
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $image_item = $this->node->{$this->fieldName}->first();
    $this->assertEquals($image_item->alt, $custom_element->getAttribute('image-alt'));
    $this->assertEquals($image_item->title, $custom_element->getAttribute('image-title'));
    $this->assertEquals($this->expectedWidth, $custom_element->getAttribute('image-width'));
    $this->assertEquals($this->expectedHeight, $custom_element->getAttribute('image-height'));
    $this->assertStringContainsString('/styles/test_style/', $custom_element->getAttribute('image-url'));
    $this->assertNull($custom_element->getSlot('image'));
  }

  /**
   * Tests output for image field as attribute (flattened, no prefix).
   */
  public function testFlattenedAttributeNoPrefix() {
    $this->changeImageDisplayComponent(FALSE, TRUE, TRUE);
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $image_item = $this->node->{$this->fieldName}->first();
    $this->assertEquals($image_item->alt, $custom_element->getAttribute('alt'));
    $this->assertEquals($image_item->title, $custom_element->getAttribute('title'));
    $this->assertEquals($this->expectedWidth, $custom_element->getAttribute('width'));
    $this->assertEquals($this->expectedHeight, $custom_element->getAttribute('height'));
    $this->assertStringContainsString('/styles/test_style/', $custom_element->getAttribute('url'));
    $this->assertNull($custom_element->getSlot('image'));
  }

  /**
   * Tests output for image field as slot (flattened).
   */
  public function testFlattenedSlot() {
    $this->changeImageDisplayComponent(TRUE, TRUE, FALSE);
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $this->assertNull($custom_element->getAttribute('image'));

    $image_item = $this->node->{$this->fieldName}->first();

    // In flattened+slot mode, each property is a separate slot with prefix.
    foreach (['alt', 'title', 'url'] as $property) {
      $slot_name = "image-$property";
      $slot_entry = $custom_element->getSlot($slot_name);
      $this->assertIsArray($slot_entry, "Slot entry for $slot_name should be an array.");
      $this->assertArrayHasKey('content', $slot_entry, "Slot entry for $slot_name should have content.");
      if ($property === 'url') {
        $this->assertStringContainsString('/styles/test_style/', $slot_entry['content']);
      }
      else {
        $this->assertEquals($image_item->{$property}, (string) $slot_entry['content']);
      }
    }
    foreach (['width', 'height'] as $property) {
      $slot_name = "image-$property";
      $slot_entry = $custom_element->getSlot($slot_name);
      $this->assertIsArray($slot_entry, "Slot entry for $slot_name should be an array.");
      $this->assertArrayHasKey('content', $slot_entry, "Slot entry for $slot_name should have content.");
      // Fix: Markup objects must be cast to string before int.
      $value = $slot_entry['content'];
      if ($value instanceof Markup) {
        $value = (string) $value;
      }
      $value = (int) $value;
      if ($property === 'width') {
        $this->assertEquals($this->expectedWidth, $value);
      }
      else {
        $this->assertEquals($this->expectedHeight, $value);
      }
    }
  }

  /**
   * Tests output for image field as attribute (not flattened).
   */
  public function testNotFlattenedAttribute() {
    $this->changeImageDisplayComponent(FALSE, FALSE, FALSE);
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $image_data = $custom_element->getAttribute('image');
    $image_item = $this->node->{$this->fieldName}->first();
    $this->assertIsArray($image_data);
    $this->assertEquals($image_item->alt, $image_data['alt']);
    $this->assertEquals($image_item->title, $image_data['title']);
    $this->assertEquals($this->expectedWidth, $image_data['width']);
    $this->assertEquals($this->expectedHeight, $image_data['height']);
    $this->assertStringContainsString('/styles/test_style/', $image_data['url']);
  }

  /**
   * Tests output for image field as slot (not flattened).
   */
  public function testNotFlattenedSlot() {
    $this->changeImageDisplayComponent(TRUE, FALSE, FALSE);
    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'default');
    $this->assertNull($custom_element->getAttribute('image'));
    $slot_entry = $custom_element->getSlot('image');
    $image_item = $this->node->{$this->fieldName}->first();
    $this->assertIsArray($slot_entry);
    $this->assertArrayHasKey('content', $slot_entry);

    $content = (string) $slot_entry['content'];
    $this->assertStringContainsString($image_item->alt, $content);
    $this->assertStringContainsString($image_item->title, $content);
    $this->assertStringContainsString('/styles/test_style/', $content);
    $this->assertStringContainsString((string) $this->expectedWidth, $content);
    $this->assertStringContainsString((string) $this->expectedHeight, $content);
  }

  /**
   * Tests the settings summary for the image field formatter.
   */
  public function testSettingsSummary() {
    $field_definition = $this->node->getFieldDefinition($this->fieldName);

    // Flattened with prefix (default).
    $formatter = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter')
      ->createInstance('image', [
        'image_style' => 'test_style',
        'flatten' => TRUE,
        'flatten_skip_prefix' => FALSE,
        'field_definition' => $field_definition,
        'view_mode' => 'default',
        'name' => 'image',
        'is_slot' => FALSE,
      ]);
    $summary = array_map('strval', $formatter->settingsSummary());
    $summary_string = implode(' ', $summary);
    $this->assertStringContainsString('Test style', $summary_string);
    $this->assertStringContainsString('Flattened', $summary_string);

    // Flattened without prefix.
    $formatter->setConfiguration([
      'image_style' => 'test_style',
      'flatten' => TRUE,
      'flatten_skip_prefix' => TRUE,
      'field_definition' => $field_definition,
      'view_mode' => 'default',
      'name' => 'image',
      'is_slot' => FALSE,
    ]);
    $summary = array_map('strval', $formatter->settingsSummary());
    $summary_string = implode(' ', $summary);
    $this->assertStringContainsString('Flattened (no prefix)', $summary_string);

    // Not flattened.
    $formatter->setConfiguration([
      'image_style' => 'test_style',
      'flatten' => FALSE,
      'flatten_skip_prefix' => FALSE,
      'field_definition' => $field_definition,
      'view_mode' => 'default',
      'name' => 'image',
      'is_slot' => FALSE,
    ]);
    $summary = array_map('strval', $formatter->settingsSummary());
    $summary_string = implode(' ', $summary);
    $this->assertStringNotContainsString('Flattened', $summary_string);
  }

  /**
   * Tests output for a multi-valued image field.
   */
  public function testMultiValuedImageField() {
    // Create a new image field with cardinality 2.
    $multi_field_name = 'field_image_multi';
    $this->container->get('entity_type.manager')
      ->getStorage('field_storage_config')
      ->create([
        'field_name' => $multi_field_name,
        'entity_type' => 'node',
        'type' => 'image',
        'cardinality' => 2,
      ])
      ->save();
    $this->container->get('entity_type.manager')
      ->getStorage('field_config')
      ->create([
        'field_name' => $multi_field_name,
        'entity_type' => 'node',
        'bundle' => 'article',
        'label' => 'Image Multi',
        'settings' => [
          'alt_field' => TRUE,
          'title_field' => TRUE,
        ],
      ])
      ->save();

    // Create a node with two images.
    $node = Node::create([
      'type' => 'article',
      'title' => 'Multi Image Node',
    ]);
    $node->save();
    $node->{$multi_field_name}->generateSampleItems(2);
    $node->save();

    // Configure CE display for the multi-valued field.
    EntityCeDisplay::load('node.article.default')
      ->setComponent('image_multi', [
        'field_name' => $multi_field_name,
        'is_slot' => FALSE,
        'formatter' => 'image',
        'configuration' => [
          'image_style' => 'test_style',
          'flatten' => FALSE,
          'flatten_skip_prefix' => FALSE,
        ],
      ])
      ->save();

    // Generate the custom element.
    $custom_element = $this->getCustomElementGenerator()
      ->generate($node, 'default');

    // The attribute should be an array of two items.
    $image_data = $custom_element->getAttribute('image-multi');
    $this->assertIsArray($image_data, 'Multi-valued image attribute is an array.');
    $this->assertArrayHasKey('alt', $image_data[0], 'First image has alt property.');
    $this->assertArrayHasKey('alt', $image_data[1], 'Second image has alt property.');
    $this->assertArrayHasKey('url', $image_data[0], 'First image has url property.');
    $this->assertArrayHasKey('url', $image_data[1], 'Second image has url property.');

    // Now test as slot.
    EntityCeDisplay::load('node.article.default')
      ->setComponent('image_multi', [
        'field_name' => $multi_field_name,
        'is_slot' => TRUE,
        'formatter' => 'image',
        'configuration' => [
          'image_style' => 'test_style',
          'flatten' => FALSE,
          'flatten_skip_prefix' => FALSE,
        ],
      ])
      ->save();
    $custom_element = $this->getCustomElementGenerator()
      ->generate($node, 'default');
    // For multi-value, not-flattened slot, each slot entry is a string for each
    // field value.
    $slot_entry_0 = $custom_element->getSlot('image-multi', 0);
    $slot_entry_1 = $custom_element->getSlot('image-multi', 1);
    $this->assertIsArray($slot_entry_0, 'Slot entry 0 for multi-valued image is an array.');
    $this->assertIsArray($slot_entry_1, 'Slot entry 1 for multi-valued image is an array.');
    $this->assertArrayHasKey('content', $slot_entry_0, 'Slot entry 0 has content key.');
    $this->assertArrayHasKey('content', $slot_entry_1, 'Slot entry 1 has content key.');
    $content_0 = (string) $slot_entry_0['content'];
    $content_1 = (string) $slot_entry_1['content'];
    $this->assertStringContainsString($node->{$multi_field_name}->get(0)->alt, $content_0);
    $this->assertStringContainsString($node->{$multi_field_name}->get(1)->alt, $content_1);
    $this->assertStringContainsString('/styles/test_style/', $content_0);
    $this->assertStringContainsString('/styles/test_style/', $content_1);

    // Now test as flattened attributes.
    EntityCeDisplay::load('node.article.default')
      ->setComponent('image_multi', [
        'field_name' => $multi_field_name,
        'is_slot' => FALSE,
        'formatter' => 'image',
        'configuration' => [
          'image_style' => 'test_style',
          'flatten' => TRUE,
          'flatten_skip_prefix' => FALSE,
        ],
      ])
      ->save();
    $custom_element = $this->getCustomElementGenerator()
      ->generate($node, 'default');
    // Only the first value is output as attributes.
    $image_item = $node->{$multi_field_name}->first();
    $this->assertEquals($image_item->alt, $custom_element->getAttribute('image-multi-alt'));
    $this->assertEquals($image_item->title, $custom_element->getAttribute('image-multi-title'));
    $this->assertStringContainsString('/styles/test_style/', $custom_element->getAttribute('image-multi-url'));
    // Calculate expected dimensions for the first image in the multi field.
    $file = $image_item->entity;
    $uri = $file->getFileUri();
    [$expectedWidth, $expectedHeight] = $this->calculateExpectedDimensions($uri);
    $width = $custom_element->getAttribute('image-multi-width');
    $height = $custom_element->getAttribute('image-multi-height');
    if ($width instanceof Markup) {
      $width = (string) $width;
    }
    if ($height instanceof Markup) {
      $height = (string) $height;
    }
    $this->assertEquals($expectedWidth, (int) $width);
    $this->assertEquals($expectedHeight, (int) $height);
  }

}

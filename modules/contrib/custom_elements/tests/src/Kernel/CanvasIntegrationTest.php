<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\canvas\Entity\Page;
use Drupal\canvas\Entity\VersionedConfigEntityBase;
use Drupal\canvas_extjs\Plugin\Canvas\ComponentSource\ExternalJavaScriptComponent;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\Entity\EntityCeDisplay;
use Drupal\canvas\Entity\Component;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\custom_elements\Traits\TestDebugHelperTrait;
use Drupal\Tests\canvas\Traits\GenerateComponentConfigTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests integration between Custom Elements and Canvas.
 */
class CanvasIntegrationTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use TestDebugHelperTrait;
  use GenerateComponentConfigTrait;
  use NodeCreationTrait;
  use \Drupal\custom_elements\CustomElementGeneratorTrait;

  /**
   * Global debug output killswitch.
   *
   * Set to TRUE to enable debug output, FALSE to disable.
   */
  protected const DEBUG_ENABLED = FALSE;

  /**
   * {@inheritdoc}
   *
   * @var bool
   * @todo Fix config schema for CE-displays and re-enable.
   *
   * phpcs:disable DrupalPractice.Objects.StrictSchemaDisabled.StrictConfigSchema
   */
  protected $strictConfigSchema = FALSE;
  // phpcs:enable

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
    'editor',
    'ckeditor5',
    'options',
    'image',
    'file',
    'media',
    'link',
    'path',
    'path_alias',
    'layout_discovery',
    'layout_builder',
    'block',
    'sdc',
    'datetime',
    'custom_elements',
    'canvas',
    'canvas_extjs',
    'canvas_test_sdc',
    'canvas_test_rendering',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('media');
    $this->installEntitySchema('node');
    $this->installConfig(['system', 'field', 'node', 'filter', 'editor']);

    // Install Canvas config.
    $this->installConfig(['canvas']);

    // These tests expect legacy format.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();

    // Create a content type.
    $this->createContentType(['type' => 'article', 'name' => 'Article']);

    // Create Canvas component tree field.
    $this->createComponentTreeField('node', 'article');

    // Generate component config for Canvas components.
    $this->generateComponentConfig();

    // Debug: List all available components.
    $components = Component::loadMultiple();
    $this->debug(
          '/tmp/canvas_available_components.json', [
            'component_ids' => array_keys($components),
            'component_details' => array_map(
              function ($c) {
                  return [
                    'id' => $c->id(),
                    'label' => $c->label(),
                    'source' => $c->get('source'),
                    'status' => $c->status(),
                  ];
              }, $components
            ),
          ]
      );
  }

  /**
   * Creates a component tree field for the given entity type and bundle.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle name.
   */
  protected function createComponentTreeField(string $entity_type_id, string $bundle): void {
    $field_storage = FieldStorageConfig::create(
          [
            'field_name' => 'field_component_tree',
            'entity_type' => $entity_type_id,
            'type' => 'component_tree',
          ]
      );
    $field_storage->save();

    FieldConfig::create(
          [
            'field_storage' => $field_storage,
            'bundle' => $bundle,
          ]
      )->save();
  }

  /**
   * Creates a test ExtJS component entity.
   *
   * @param string $component_name
   *   The component name (e.g., 'TestButton', 'TwoColumnLayout').
   * @param array $config
   *   Component configuration with 'label', 'category', 'props', 'slots'.
   */
  protected function createExtJsComponent(string $component_name, array $config): void {
    $snake_case_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $component_name));
    $component_id = 'extjs.' . $snake_case_name;

    // Check if already exists.
    if (Component::load($component_id)) {
      return;
    }

    $settings = [
      'label' => $config['label'],
      'local_source_id' => $component_name,
      'props' => $config['props'],
      'slots' => $config['slots'] ?? [],
      'prop_field_definitions' => [],
    ];

    $component = Component::create([
      'id' => $component_id,
      'label' => $config['label'],
      'category' => $config['category'],
      'source' => 'extjs',
      'provider' => NULL,
      'source_local_id' => $component_name,
      'active_version' => 'v1',
      'versioned_properties' => [
        VersionedConfigEntityBase::ACTIVE_VERSION => ['settings' => $settings],
      ],
      'status' => TRUE,
    ]);

    ExternalJavaScriptComponent::ensurePropFieldDefinitions($component);
    $component->save();
  }

  /**
   * Tests basic Canvas component conversion with multiple components.
   */
  public function testCanvasBasicComponentConversion(): void {
    // Create a node with three Canvas components.
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test Node',
      'field_component_tree' => [
        [
          'uuid' => 'aaaaaaaa-1111-2222-3333-444444444444',
          'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
          'component_version' => 'active',
          'inputs' => [
            'heading' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'First Component',
              'expression' => 'ℹ︎string␟value',
            ],
          ],
        ],
        [
          'uuid' => 'bbbbbbbb-1111-2222-3333-444444444444',
          'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
          'component_version' => 'active',
          'inputs' => [
            'heading' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'Second Component',
              'expression' => 'ℹ︎string␟value',
            ],
          ],
        ],
        [
          'uuid' => 'cccccccc-1111-2222-3333-444444444444',
          'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
          'component_version' => 'active',
          'inputs' => [
            'heading' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'Third Component',
              'expression' => 'ℹ︎string␟value',
            ],
          ],
        ],
      ],
    ]);
    $node->save();

    // Get the Canvas field render array.
    $field_items = $node->get('field_component_tree');
    $canvas_render_array = $field_items->toRenderable($node);
    $this->assertIsArray($canvas_render_array, 'Canvas should return render array');

    /**
     * @var \Drupal\custom_elements\RenderConverter\CanvasRenderConverter $converter
     */
    $converter = $this->container->get('custom_elements.canvas_render_converter');

    // Configure this SDC to be rendered as custom element (not twig).
    $converter->setSdcCustomElementComponents(['sdc.canvas_test_sdc.props-no-slots']);

    $custom_element = $converter->convertRenderArray($canvas_render_array);

    // Verify we get a renderless-container for multiple components.
    $this->assertInstanceOf(CustomElement::class, $custom_element);
    $this->assertEquals('renderless-container', $custom_element->getTag(), 'Multiple components should use renderless-container');

    // Normalize to verify JSON structure.
    $normalizer = $this->container->get('custom_elements.normalizer');
    $normalized = $normalizer->normalize($custom_element);

    // Should be a flat array, not nested with UUIDs.
    $this->assertIsArray($normalized);
    $this->assertCount(3, $normalized, 'Normalized output should have 3 components');

    // Verify each component in the flat array.
    $this->assertEquals('canvas-test-sdc-props-no-slots', $normalized[0]['element']);
    $this->assertEquals('First Component', $normalized[0]['heading']);

    $this->assertEquals('canvas-test-sdc-props-no-slots', $normalized[1]['element']);
    $this->assertEquals('Second Component', $normalized[1]['heading']);

    $this->assertEquals('canvas-test-sdc-props-no-slots', $normalized[2]['element']);
    $this->assertEquals('Third Component', $normalized[2]['heading']);

    $this->debug('/tmp/canvas_conversion_test.json', [
      'render_array' => $canvas_render_array,
      'custom_element' => $custom_element,
      'normalized' => $normalized,
    ]);

    // TEST SDC TWIG RENDERING: Reuse existing node to test twig rendering.
    // Switch all components to twig rendering.
    $converter->setSdcCustomElementComponents(['sdc.canvas_test_sdc.props-no-slots'], FALSE);

    // Convert the same render array with twig rendering.
    $twig_elements = $converter->convertRenderArray($canvas_render_array);

    // Should still be a renderless-container with three components.
    $this->assertEquals('renderless-container', $twig_elements->getTag());

    // Get the slots directly from the container to check structure.
    $container_slots = $twig_elements->getSlots();
    $this->assertArrayHasKey('default', $container_slots, 'Container should have default slot');
    $this->assertCount(3, $container_slots['default'], 'Container should have 3 elements');

    // Verify each component in the container.
    foreach ([0 => 'First', 1 => 'Second', 2 => 'Third'] as $index => $prefix) {
      $component_element = $container_slots['default'][$index]['content'];
      $this->assertInstanceOf(CustomElement::class, $component_element);
      $this->assertEquals('drupal-markup', $component_element->getTag(),
        "Component $index should be drupal-markup for twig rendering");

      // Get the rendered content from the drupal-markup element.
      $slot = $component_element->getSlot('default');
      $this->assertNotEmpty($slot, "Component $index should have default slot");

      $html = (string) $slot['content'];

      // Verify the heading prop is rendered correctly in the twig template.
      $this->assertStringContainsString("{$prefix} Component", $html,
        "Twig-rendered HTML should contain heading prop value: {$prefix} Component");

      // Verify the component ID attribute is present.
      $this->assertStringContainsString('data-component-id="canvas_test_sdc:props-no-slots"', $html,
        'Twig-rendered HTML should have component ID attribute');

      // Verify the heading is rendered in an h1 tag (from the SDC template).
      $this->assertMatchesRegularExpression(
        '/<h1[^>]*>' . preg_quote("{$prefix} Component", '/') . '<\/h1>/',
        $html,
        "The heading prop should be rendered inside an h1 tag"
      );
    }
  }

  /**
   * Tests conversion of nested Canvas components with two column layout.
   */
  public function testCanvasNestedComponentConversion(): void {
    // Create a node with the actual two_column component from
    // Canvas.
    $node = Node::create(
          [
            'type' => 'article',
            'title' => 'Two Column Layout Test',
            'field_component_tree' => [
          // Two-column layout as root component.
          [
            'uuid' => '11111111-2222-3333-4444-555555555555',
            'component_id' => 'sdc.canvas_test_sdc.two_column',
            'component_version' => 'active',
            'inputs' => [
              'width' => [
                'sourceType' => 'static:field_item:list_integer',
                'value' => 50,
                'expression' => 'ℹ︎list_integer␟value',
                'sourceTypeSettings' => [
                  'storage' => [
                    'allowed_values_function' => 'canvas_load_allowed_values_for_component_prop',
                  ],
                ],
              ],
            ],
          ],
          // First column content - a simple props component.
          [
            'uuid' => '22222222-3333-4444-5555-666666666666',
            'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
            'component_version' => 'active',
            'parent_uuid' => '11111111-2222-3333-4444-555555555555',
            'slot' => 'column_one',
            'inputs' => [
              'heading' => [
                'sourceType' => 'static:field_item:string',
                'value' => 'First Column Heading',
                'expression' => 'ℹ︎string␟value',
              ],
            ],
          ],
          // Second column content - another props component.
          [
            'uuid' => '33333333-4444-5555-6666-777777777777',
            'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
            'component_version' => 'active',
            'parent_uuid' => '11111111-2222-3333-4444-555555555555',
            'slot' => 'column_two',
            'inputs' => [
              'heading' => [
                'sourceType' => 'static:field_item:string',
                'value' => 'Second Column Heading',
                'expression' => 'ℹ︎string␟value',
              ],
            ],
          ],
            ],
          ]
      );
    $node->save();

    // Get the Canvas field render array.
    $field_items = $node->get('field_component_tree');
    $canvas_render_array = $field_items->toRenderable($node);

    $this->debug('/tmp/canvas_debug_full.json', $canvas_render_array);

    // Convert to custom element and see what happens.
    /**
     * @var \Drupal\custom_elements\RenderConverter\CanvasRenderConverter $converter
     */
    $converter = $this->container->get('custom_elements.canvas_render_converter');

    // FIRST TEST: Configure SDCs to be rendered as custom elements (not twig).
    $converter->setSdcCustomElementComponents([
      'sdc.canvas_test_sdc.two_column',
      'sdc.canvas_test_sdc.props-no-slots',
    ]);

    $custom_element = $converter->convertRenderArray($canvas_render_array);

    $this->debug(
          '/tmp/canvas_conversion_debug.json', [
            'render_array' => $canvas_render_array,
            'custom_element' => $custom_element,
          ]
      );

    // Verify the two column component was converted.
    $this->assertInstanceOf(CustomElement::class, $custom_element);
    $this->assertEquals('canvas-test-sdc-two-column', $custom_element->getTag());

    // Verify the width prop was converted.
    $this->assertEquals(50, $custom_element->getAttribute('width'));

    // Verify both column slots are present.
    $slots = $custom_element->getSlots();
    $this->assertArrayHasKey('column-one', $slots, 'Column one slot must exist');
    $this->assertArrayHasKey('column-two', $slots, 'Column two slot must exist');

    // Verify content in column one.
    $this->assertNotEmpty($slots['column-one']);
    $column_one_content = $slots['column-one'][0]['content'];
    $this->assertInstanceOf(CustomElement::class, $column_one_content);
    $this->assertEquals('canvas-test-sdc-props-no-slots', $column_one_content->getTag());
    $this->assertEquals('First Column Heading', $column_one_content->getAttribute('heading'));

    // Verify content in column two.
    $this->assertNotEmpty($slots['column-two']);
    $column_two_content = $slots['column-two'][0]['content'];
    $this->assertInstanceOf(CustomElement::class, $column_two_content);
    $this->assertEquals('canvas-test-sdc-props-no-slots', $column_two_content->getTag());
    $this->assertEquals('Second Column Heading', $column_two_content->getAttribute('heading'));

    // SECOND TEST: Now test with all components rendered via twig.
    // Clear the custom element configuration to render all SDCs via twig.
    $converter->setSdcCustomElementComponents([
      'sdc.canvas_test_sdc.two_column',
      'sdc.canvas_test_sdc.props-no-slots',
    ], FALSE);

    // Convert the same render array with twig rendering for all components.
    $twig_rendered_element = $converter->convertRenderArray($canvas_render_array);

    $this->debug(
          '/tmp/canvas_twig_conversion_debug.json', [
            'render_array' => $canvas_render_array,
            'twig_rendered_element' => $twig_rendered_element,
          ]
      );

    // The root element should be drupal-markup containing the rendered twig.
    $this->assertInstanceOf(CustomElement::class, $twig_rendered_element);
    $this->assertEquals('drupal-markup', $twig_rendered_element->getTag(),
      'Root element should be drupal-markup when rendering via twig');

    // Get the rendered HTML content.
    $slot = $twig_rendered_element->getSlot('default');
    $this->assertNotEmpty($slot, 'drupal-markup should have default slot');

    $html = (string) $slot['content'];

    // Verify the two-column structure is present in the HTML.
    $this->assertStringContainsString('class="column-one width-50"', $html,
      'Twig-rendered HTML should contain column-one with correct width');
    $this->assertStringContainsString('class="column-two width-50"', $html,
      'Twig-rendered HTML should contain column-two with correct width');

    // Verify the headings from the nested components are rendered.
    $this->assertStringContainsString('First Column Heading', $html,
      'First column heading should be present in twig-rendered HTML');
    $this->assertStringContainsString('Second Column Heading', $html,
      'Second column heading should be present in twig-rendered HTML');

    // Verify the nested components are rendered with their h1 tags.
    $this->assertMatchesRegularExpression(
      '/<h1[^>]*>First Column Heading<\/h1>/',
      $html,
      'First column heading should be in an h1 tag'
    );
    $this->assertMatchesRegularExpression(
      '/<h1[^>]*>Second Column Heading<\/h1>/',
      $html,
      'Second column heading should be in an h1 tag'
    );
  }

  /**
   * Tests real Drupal block plugins (dynamic components) in Canvas.
   *
   * Tests that blocks render as drupal-markup elements and cache metadata
   * is preserved.
   */
  public function testCanvasBlockComponent(): void {
    // Create a node with a two-column layout containing the "Powered by
    // Drupal" block.
    $node = Node::create(
          [
            'type' => 'article',
            'title' => 'Dynamic Component (Block) Test',
            'field_component_tree' => [
          // Two-column layout as root component.
          [
            'uuid' => '77777777-8888-9999-aaaa-bbbbbbbbbbbb',
            'component_id' => 'sdc.canvas_test_sdc.two_column',
            'component_version' => 'active',
            'inputs' => [
              'width' => [
                'sourceType' => 'static:field_item:list_integer',
                'value' => 50,
                'expression' => 'ℹ︎list_integer␟value',
                'sourceTypeSettings' => [
                  'storage' => [
                    'allowed_values_function' => 'canvas_load_allowed_values_for_component_prop',
                  ],
                ],
              ],
            ],
          ],
          // "Powered by Drupal" block in the first column.
          [
            'uuid' => '88888888-9999-aaaa-bbbb-cccccccccccc',
            'component_id' => 'block.system_powered_by_block',
            'component_version' => 'active',
            'parent_uuid' => '77777777-8888-9999-aaaa-bbbbbbbbbbbb',
            'slot' => 'column_one',
            'inputs' => [
              'label' => 'Powered by Drupal',
              'label_display' => 'visible',
            ],
          ],
          // A heading component in the second column.
          [
            'uuid' => '99999999-aaaa-bbbb-cccc-dddddddddddd',
            'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
            'component_version' => 'active',
            'parent_uuid' => '77777777-8888-9999-aaaa-bbbbbbbbbbbb',
            'slot' => 'column_two',
            'inputs' => [
              'heading' => [
                'sourceType' => 'static:field_item:string',
                'value' => 'Content alongside the block',
                'expression' => 'ℹ︎string␟value',
              ],
            ],
          ],
            ],
          ]
      );
    $node->save();

    // Get the Canvas field render array.
    $field_items = $node->get('field_component_tree');
    $canvas_render_array = $field_items->toRenderable($node);

    $this->debug('/tmp/canvas_block_debug.json', $canvas_render_array);

    /**
     * @var \Drupal\custom_elements\RenderConverter\CanvasRenderConverter $converter
     */
    $converter = $this->container->get('custom_elements.canvas_render_converter');

    // Configure SDCs to be rendered as custom elements (not twig).
    // Note: block.system_powered_by_block is not an SDC, so it doesn't
    // need configuration.
    $converter->setSdcCustomElementComponents([
      'sdc.canvas_test_sdc.two_column',
      'sdc.canvas_test_sdc.props-no-slots',
    ]);

    $custom_element = $converter->convertRenderArray($canvas_render_array);

    $this->debug(
          '/tmp/canvas_block_conversion.json', [
            'render_array' => $canvas_render_array,
            'custom_element' => $custom_element,
          ]
      );

    // Verify the root structure.
    $this->assertInstanceOf(CustomElement::class, $custom_element);
    $this->assertEquals('canvas-test-sdc-two-column', $custom_element->getTag());

    // Get the block element from column one.
    $slots = $custom_element->getSlots();
    $this->assertArrayHasKey('column-one', $slots, 'Column one slot should exist');
    $this->assertNotEmpty($slots['column-one'], 'Column one should not be empty');

    $block_element = $slots['column-one'][0]['content'];

    // Blocks should be wrapped in drupal-markup.
    $this->assertInstanceOf(CustomElement::class, $block_element);
    $this->assertEquals('drupal-markup', $block_element->getTag());

    // Verify cache metadata is preserved on the custom element.
    $cache_tags = $block_element->getCacheTags();
    $this->assertContains('config:canvas.component.block.system_powered_by_block', $cache_tags, 'Block cache tags should be preserved');

    // Verify the heading in column two renders normally.
    $heading_element = $slots['column-two'][0]['content'];
    $this->assertEquals('canvas-test-sdc-props-no-slots', $heading_element->getTag());
    $this->assertEquals('Content alongside the block', $heading_element->getAttribute('heading'));
  }

  /**
   * Tests the Canvas field formatter with entity CE display.
   */
  public function testCanvasFormatter(): void {
    // Create a node with Canvas content.
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test Canvas Formatter',
      'field_component_tree' => [
        [
          'uuid' => '12345678-formatter-test',
          'component_id' => 'sdc.canvas_test_sdc.props-no-slots',
          'component_version' => 'active',
          'inputs' => [
            'heading' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'Canvas Component Heading',
              'expression' => 'ℹ︎string␟value',
            ],
          ],
        ],
      ],
    ]);
    $node->save();

    // Configure CE display for node with Canvas field as slot.
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
      ->setComponent('xb_content', [
        'field_name' => 'field_component_tree',
        'is_slot' => TRUE,
        'formatter' => 'canvas',
      ])
      ->save();

    // Configure the converter to render this SDC as custom element for test.
    /** @var \Drupal\custom_elements\RenderConverter\CanvasRenderConverter $converter */
    $converter = $this->container->get('custom_elements.canvas_render_converter');
    $converter->setSdcCustomElementComponents(['sdc.canvas_test_sdc.props-no-slots']);

    // Generate the custom element using the CE display.
    $custom_element = $this->getCustomElementGenerator()
      ->generate($node, 'default');

    // Verify the node is rendered as custom element.
    $this->assertEquals('article', $custom_element->getTag());
    $this->assertEquals('Test Canvas Formatter', $custom_element->getAttribute('title'));

    // Verify Canvas content was added as slot.
    $slots = $custom_element->getSlots();
    $this->assertArrayHasKey('xb-content', $slots);
    $this->assertNotEmpty($slots['xb-content']);

    $canvas_element = $slots['xb-content'][0]['content'];
    $this->assertInstanceOf(CustomElement::class, $canvas_element);
    $this->assertEquals('canvas-test-sdc-props-no-slots', $canvas_element->getTag());
    $this->assertEquals('Canvas Component Heading', $canvas_element->getAttribute('heading'));

    // Test formatter as attribute by updating the CE display.
    EntityCeDisplay::load('node.article.default')
      ->setComponent('xb_data', [
        'field_name' => 'field_component_tree',
        'is_slot' => FALSE,
        'formatter' => 'canvas',
      ])
      ->removeComponent('xb_content')
      ->save();

    // Generate custom element with Canvas as attribute.
    $custom_element2 = $this->getCustomElementGenerator()
      ->generate($node, 'default');

    // Verify normalized data was added as attribute.
    $canvas_data = $custom_element2->getAttribute('xb-data');
    $this->assertNotNull($canvas_data, 'Canvas data attribute should be set');
    $this->assertIsArray($canvas_data);
    $this->assertEquals('canvas-test-sdc-props-no-slots', $canvas_data['element']);
    $this->assertArrayHasKey('heading', $canvas_data);
    $this->assertEquals('Canvas Component Heading', $canvas_data['heading']);

    // Verify the slot was removed.
    $slots2 = $custom_element2->getSlots();
    $this->assertArrayNotHasKey('xb-content', $slots2);
  }

  /**
   * Tests conversion of extjs components with nested slots.
   */
  public function testCanvasExtJsComponentConversion(): void {
    // Create extjs test components.
    $this->createExtJsComponent('TwoColumnLayout', [
      'label' => 'Two Column Layout',
      'category' => 'Test Components',
      'props' => [
        'type' => 'object',
        'properties' => [
          'width' => [
            'type' => 'integer',
            'title' => 'Width',
            'default' => 50,
          ],
        ],
      ],
      'slots' => [
        'column-one' => ['title' => 'Column One'],
        'column-two' => ['title' => 'Column Two'],
      ],
    ]);

    $this->createExtJsComponent('TestButton', [
      'label' => 'Test Button',
      'category' => 'Test Components',
      'props' => [
        'type' => 'object',
        'properties' => [
          'label' => ['type' => 'string', 'default' => 'Click me'],
          'variant' => ['type' => 'string', 'default' => 'primary'],
        ],
      ],
    ]);

    // Create a node with TwoColumnLayout containing TestButton in a slot.
    $node = Node::create([
      'type' => 'article',
      'title' => 'ExtJS Component Test',
      'field_component_tree' => [
        // TwoColumnLayout as root.
        [
          'uuid' => 'eeeeeeee-ffff-0000-1111-222222222222',
          'component_id' => 'extjs.two_column_layout',
          'component_version' => 'active',
          'inputs' => [
            'width' => [
              'sourceType' => 'static:field_item:integer',
              'value' => 50,
              'expression' => 'ℹ︎integer␟value',
            ],
          ],
        ],
        // TestButton in column-one slot.
        [
          'uuid' => 'ffffffff-0000-1111-2222-333333333333',
          'component_id' => 'extjs.test_button',
          'component_version' => 'active',
          'parent_uuid' => 'eeeeeeee-ffff-0000-1111-222222222222',
          'slot' => 'column-one',
          'inputs' => [
            'label' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'Nested Button',
              'expression' => 'ℹ︎string␟value',
            ],
            'variant' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'success',
              'expression' => 'ℹ︎string␟value',
            ],
          ],
        ],
      ],
    ]);
    $node->save();

    // Get the Canvas field render array.
    $field_items = $node->get('field_component_tree');
    $canvas_render_array = $field_items->toRenderable($node);

    /** @var \Drupal\custom_elements\RenderConverter\CanvasRenderConverter $converter */
    $converter = $this->container->get('custom_elements.canvas_render_converter');
    $custom_element = $converter->convertRenderArray($canvas_render_array);

    // Verify the root is TwoColumnLayout.
    $this->assertInstanceOf(CustomElement::class, $custom_element);
    $this->assertEquals('TwoColumnLayout', $custom_element->getTag());
    $this->assertEquals(50, $custom_element->getAttribute('width'));

    // Verify slots contain nested CustomElement objects, not HTML strings.
    $slots = $custom_element->getSlots();
    $this->assertArrayHasKey('column-one', $slots);

    $column_one_entry = $slots['column-one'][0];
    $this->assertArrayHasKey('content', $column_one_entry);
    $nested_element = $column_one_entry['content'];
    $this->assertInstanceOf(CustomElement::class, $nested_element, 'Nested component should be CustomElement, not HTML string');
    $this->assertEquals('TestButton', $nested_element->getTag());
    $this->assertEquals('Nested Button', $nested_element->getAttribute('label'));
    $this->assertEquals('success', $nested_element->getAttribute('variant'));
  }

  /**
   * Tests Canvas Page entity rendering with CE generator.
   */
  public function testCanvasPageEntitySupport(): void {
    // Create extjs test component.
    $this->createExtJsComponent('TestButton', [
      'label' => 'Test Button',
      'category' => 'Test Components',
      'props' => [
        'type' => 'object',
        'properties' => [
          'label' => ['type' => 'string', 'default' => 'Click me'],
          'variant' => ['type' => 'string', 'default' => 'primary'],
        ],
      ],
    ]);

    // Install Page entity schema.
    $this->installEntitySchema('canvas_page');

    // Create a Canvas Page with a component.
    $page = Page::create([
      'title' => 'Test Page',
      'components' => [
        [
          'uuid' => 'page-button-uuid',
          'component_id' => 'extjs.test_button',
          'component_version' => 'active',
          'inputs' => [
            'label' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'Page Button',
              'expression' => 'ℹ︎string␟value',
            ],
            'variant' => [
              'sourceType' => 'static:field_item:string',
              'value' => 'primary',
              'expression' => 'ℹ︎string␟value',
            ],
          ],
        ],
      ],
    ]);
    $page->save();

    // Generate the custom element using the auto-generated CE display.
    $custom_element = $this->getCustomElementGenerator()
      ->generate($page, 'full');

    // The component is wrapped in drupal-markup from view() rendering.
    // Just verify it's a CustomElement and contains our component data.
    $this->assertInstanceOf(CustomElement::class, $custom_element);
    $this->assertEquals('canvas-page', $custom_element->getTag());
    $slot = $custom_element->getSlot('components');
    $this->assertArrayHasKey('content', $slot);
    $nested_element = $slot['content'];
    $this->assertInstanceOf(CustomElement::class, $nested_element, 'Nested component should be CustomElement, not HTML string');
    $this->assertEquals('TestButton', $nested_element->getTag());
  }

}

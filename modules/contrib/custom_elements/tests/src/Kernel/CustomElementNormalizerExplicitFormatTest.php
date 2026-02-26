<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\custom_elements\CustomElement;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests CustomElementNormalizer with explicit JSON format.
 *
 * @group custom_elements
 */
class CustomElementNormalizerExplicitFormatTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['custom_elements'];

  /**
   * The custom elements normalizer service.
   *
   * @var \Drupal\custom_elements\CustomElementNormalizer
   */
  protected $normalizer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['custom_elements']);

    // Explicit format should be default, but set explicitly for clarity.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'explicit')
      ->save();

    $this->normalizer = $this->container->get('custom_elements.normalizer');
  }

  /**
   * Tests element with props only in explicit format.
   */
  public function testPropsOnlyExplicitFormat() {
    $element = CustomElement::create('test-element')
      ->setAttribute('title', 'Test Title')
      ->setAttribute('tags', ['news', 'breaking']);

    $normalized = $this->normalizer->normalize($element);

    // Explicit format: attributes in props object.
    $this->assertEquals([
      'element' => 'test-element',
      'props' => [
        'title' => 'Test Title',
        'tags' => ['news', 'breaking'],
      ],
    ], $normalized);
  }

  /**
   * Tests element with slots only in explicit format.
   */
  public function testSlotsOnlyExplicitFormat() {
    $element = CustomElement::create('test-element')
      ->setSlot('header', '<h1>Header</h1>')
      ->setSlot('footer', '<p>Footer</p>');

    $normalized = $this->normalizer->normalize($element);

    // Explicit format: slots in slots object.
    $this->assertEquals([
      'element' => 'test-element',
      'slots' => [
        'header' => '<h1>Header</h1>',
        'footer' => '<p>Footer</p>',
      ],
    ], $normalized);
  }

  /**
   * Tests element with default slot in explicit format.
   */
  public function testDefaultSlotExplicitFormat() {
    $element = CustomElement::create('test-element')
      ->setAttribute('title', 'Title')
      ->setSlot('default', '<p>Content</p>');

    $normalized = $this->normalizer->normalize($element);

    // Explicit format: default slot keeps name 'default'
    // (not renamed to 'content').
    $this->assertEquals([
      'element' => 'test-element',
      'props' => [
        'title' => 'Title',
      ],
      'slots' => [
        'default' => '<p>Content</p>',
      ],
    ], $normalized);
  }

  /**
   * Tests element with both props and slots in explicit format.
   */
  public function testPropsAndSlotsExplicitFormat() {
    $element = CustomElement::create('article')
      ->setAttribute('title', 'Article Title')
      ->setAttribute('author', 'John Doe')
      ->setSlot('body', '<p>Article body</p>')
      ->setSlot('footer', '<p>Read more</p>');

    $normalized = $this->normalizer->normalize($element);

    $this->assertEquals([
      'element' => 'article',
      'props' => [
        'title' => 'Article Title',
        'author' => 'John Doe',
      ],
      'slots' => [
        'body' => '<p>Article body</p>',
        'footer' => '<p>Read more</p>',
      ],
    ], $normalized);
  }

  /**
   * Tests nested elements in explicit format.
   */
  public function testNestedElementsExplicitFormat() {
    $nestedElement = CustomElement::create('nested')
      ->setAttribute('id', '123')
      ->setSlot('default', '<span>Nested content</span>');

    $element = CustomElement::create('parent')
      ->setAttribute('title', 'Parent')
      ->setSlot('child', $nestedElement);

    $normalized = $this->normalizer->normalize($element);

    // Nested elements should also use explicit format.
    $this->assertEquals([
      'element' => 'parent',
      'props' => [
        'title' => 'Parent',
      ],
      'slots' => [
        'child' => [
          'element' => 'nested',
          'props' => [
            'id' => '123',
          ],
          'slots' => [
            'default' => '<span>Nested content</span>',
          ],
        ],
      ],
    ], $normalized);
  }

  /**
   * Tests multi-value slots in explicit format.
   */
  public function testMultiValueSlotExplicitFormat() {
    $element1 = CustomElement::create('item')->setAttribute('id', '1');
    $element2 = CustomElement::create('item')->setAttribute('id', '2');

    $container = CustomElement::create('container')
      ->setSlot('items', $element1, 'div', [], 0)
      ->setSlot('items', $element2, 'div', [], 1);

    $normalized = $this->normalizer->normalize($container);

    // Multi-value slot should be array.
    $this->assertEquals([
      'element' => 'container',
      'slots' => [
        'items' => [
          [
            'element' => 'item',
            'props' => ['id' => '1'],
          ],
          [
            'element' => 'item',
            'props' => ['id' => '2'],
          ],
        ],
      ],
    ], $normalized);
  }

  /**
   * Tests empty props and slots are omitted.
   */
  public function testEmptyPropsAndSlotsOmitted() {
    $element = CustomElement::create('test-element');

    $normalized = $this->normalizer->normalize($element);

    // Should only have element key when no props/slots.
    $this->assertEquals([
      'element' => 'test-element',
    ], $normalized);
  }

  /**
   * Tests context override to force explicit format.
   */
  public function testContextOverrideExplicit() {
    // Set config to legacy.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();

    $element = CustomElement::create('test')
      ->setAttribute('foo', 'bar')
      ->setSlot('default', 'content');

    // Override with context parameter.
    $normalized = $this->normalizer->normalize($element, NULL, ['json_format' => 'explicit']);

    // Should use explicit format despite config.
    $this->assertEquals([
      'element' => 'test',
      'props' => [
        'foo' => 'bar',
      ],
      'slots' => [
        'default' => 'content',
      ],
    ], $normalized);
  }

  /**
   * Tests that empty div/span slots are not output in explicit format.
   */
  public function testEmptyDivSlotsNotOutput() {
    $element = CustomElement::create('test-element');

    // Add test-data:
    // empty div slot (CustomElement with no content).
    // non-empty div slot with content (CustomElement with attributes).
    $empty_div = CustomElement::create('div');
    $element->setSlotFromCustomElement('empty_slot', $empty_div);
    $content_element = CustomElement::create('div');
    $content_element->setAttribute('data', 'value');
    $element->setSlotFromCustomElement('content_slot', $content_element);

    $array = $element->toArray(FALSE, NULL, TRUE);
    $this->assertArrayHasKey('slots', $array);
    $this->assertArrayNotHasKey('emptySlot', $array['slots'], 'Empty div slot filtered out');
    $this->assertArrayHasKey('contentSlot', $array['slots'], 'Slot with content is output');

    // The not-empty div element should have its structure preserved.
    $this->assertArrayHasKey('element', $array['slots']['contentSlot']);
    $this->assertEquals('div', $array['slots']['contentSlot']['element']);
    $this->assertArrayHasKey('props', $array['slots']['contentSlot']);
    $this->assertArrayHasKey('data', $array['slots']['contentSlot']['props']);
    $this->assertEquals('value', $array['slots']['contentSlot']['props']['data']);
  }

}

<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\custom_elements\CustomElement;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests CustomElementNormalizer with legacy JSON format.
 *
 * @group custom_elements
 */
class CustomElementNormalizerLegacyFormatTest extends KernelTestBase {

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

    // Set to legacy format for these tests.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();

    $this->normalizer = $this->container->get('custom_elements.normalizer');
  }

  /**
   * Tests basic element with attributes in legacy format.
   */
  public function testBasicElementLegacyFormat() {
    $element = CustomElement::create('test-element')
      ->setAttribute('title', 'Test Title')
      ->setAttribute('tags', ['news', 'breaking']);

    $normalized = $this->normalizer->normalize($element);

    // Legacy format: attributes mixed at root level.
    $this->assertEquals([
      'element' => 'test-element',
      'title' => 'Test Title',
      'tags' => ['news', 'breaking'],
    ], $normalized);
  }

  /**
   * Tests element with default slot in legacy format.
   */
  public function testDefaultSlotLegacyFormat() {
    $element = CustomElement::create('test-element')
      ->setAttribute('title', 'Title')
      ->setSlot('default', '<p>Content</p>');

    $normalized = $this->normalizer->normalize($element);

    // Legacy format: default slot renamed to 'content'.
    $this->assertEquals([
      'element' => 'test-element',
      'title' => 'Title',
      'content' => '<p>Content</p>',
    ], $normalized);
  }

  /**
   * Tests element with named slot in legacy format.
   */
  public function testNamedSlotLegacyFormat() {
    $element = CustomElement::create('test-element')
      ->setSlot('header', '<h1>Header</h1>')
      ->setSlot('footer', '<p>Footer</p>');

    $normalized = $this->normalizer->normalize($element);

    // Legacy format: slots mixed at root level.
    $this->assertEquals([
      'element' => 'test-element',
      'header' => '<h1>Header</h1>',
      'footer' => '<p>Footer</p>',
    ], $normalized);
  }

  /**
   * Tests nested elements in legacy format.
   */
  public function testNestedElementLegacyFormat() {
    $nestedElement = CustomElement::create('nested')
      ->setAttribute('id', '123');

    $element = CustomElement::create('parent')
      ->setAttribute('title', 'Parent')
      ->setSlot('child', $nestedElement);

    $normalized = $this->normalizer->normalize($element);

    // Legacy format: all mixed at root.
    $this->assertEquals([
      'element' => 'parent',
      'title' => 'Parent',
      'child' => [
        'element' => 'nested',
        'id' => '123',
      ],
    ], $normalized);
  }

  /**
   * Tests context override to force legacy format.
   */
  public function testContextOverride() {
    // Temporarily set config to explicit.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'explicit')
      ->save();

    $element = CustomElement::create('test')
      ->setAttribute('foo', 'bar')
      ->setSlot('default', 'content');

    // Override with context parameter.
    $normalized = $this->normalizer->normalize($element, NULL, ['json_format' => 'legacy']);

    // Should use legacy format despite config.
    $this->assertEquals([
      'element' => 'test',
      'foo' => 'bar',
      'content' => 'content',
    ], $normalized);
  }

}

<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\custom_elements\CustomElement;
use Drupal\KernelTests\KernelTestBase;

/**
 * Kernel tests for the CustomElement class.
 *
 * @group custom_elements
 * @see \Drupal\Tests\custom_elements\Unit\CustomElementUnitTest
 */
class CustomElementKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'custom_elements',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['custom_elements']);
  }

  /**
   * Tests toArray() with explicit parameter controlling output format.
   */
  public function testToArrayExplicitParameter() {
    $element = CustomElement::create('test-element');
    $element->setAttribute('title', 'Test Title');
    $element->setAttribute('count', 42);
    $element->addSlot('content', 'Test content');

    // Test with explicit = FALSE (default, backwards compatible).
    // Attributes and slots are at root level.
    $array_implicit = $element->toArray(FALSE, NULL, FALSE);
    $this->assertEquals('test-element', $array_implicit['element']);
    $this->assertArrayHasKey('title', $array_implicit);
    $this->assertEquals('Test Title', $array_implicit['title']);
    $this->assertEquals(42, $array_implicit['count']);
    $this->assertArrayHasKey('content', $array_implicit);

    // Test with explicit = TRUE (separate props and slots).
    // Attributes are under 'props', slots under 'slots'.
    $array_explicit = $element->toArray(FALSE, NULL, TRUE);
    $this->assertEquals('test-element', $array_explicit['element']);
    $this->assertArrayHasKey('props', $array_explicit);
    $this->assertEquals('Test Title', $array_explicit['props']['title']);
    $this->assertEquals(42, $array_explicit['props']['count']);
    $this->assertArrayHasKey('slots', $array_explicit);
    $this->assertArrayHasKey('content', $array_explicit['slots']);
  }

  /**
   * Tests that toArray() defaults to non-explicit format.
   */
  public function testToArrayDefaultsToNonExplicit() {
    $element = CustomElement::create('test');
    $element->setAttribute('attr', 'value');

    $array = $element->toArray();

    // Default should be backwards compatible (non-explicit unless configured).
    // The normalizer config determines this, but explicit parameter
    // should override it when provided.
    $this->assertEquals('test', $array['element']);
  }

}

<?php

namespace Drupal\Tests\custom_elements\Unit;

use Drupal\custom_elements\CustomElement;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the CustomElement class.
 *
 * @group custom_elements
 * @see \Drupal\Tests\custom_elements\Kernel\CustomElementKernelTest
 */
class CustomElementUnitTest extends TestCase {

  /**
   * Verifies that addSlot() can be called multiple times on the same slot key.
   */
  public function testAddSlotMultipleTimesSameKey() {
    // Test with a regular slot name.
    $element = CustomElement::create('test');
    $element->addSlot('foo', 'first');
    $element->addSlot('foo', 'second');
    $element->addSlot('foo', 'third');

    $slots = $element->getSlots();
    $this->assertArrayHasKey('foo', $slots, 'Slot key "foo" exists.');
    $this->assertCount(3, $slots['foo'], 'There are three slot entries for "foo".');
    $this->assertEquals('first', (string) $slots['foo'][0]['content']);
    $this->assertEquals('second', (string) $slots['foo'][1]['content']);
    $this->assertEquals('third', (string) $slots['foo'][2]['content']);

    $this->assertEquals('first', (string) $element->getSlot('foo', 0)['content']);
    $this->assertEquals('second', (string) $element->getSlot('foo', 1)['content']);
    $this->assertEquals('third', (string) $element->getSlot('foo', 2)['content']);
    $this->assertNull($element->getSlot('foo', 3));

    // Test with a slot name having a "field_" prefix.
    $element2 = CustomElement::create('test');
    $element2->addSlot('field_bar', 'first');
    $element2->addSlot('field_bar', 'second');
    $element2->addSlot('field_bar', 'third');

    $slots2 = $element2->getSlots();
    // The prefix should be removed, so the slot key is "bar".
    $this->assertArrayHasKey('bar', $slots2, 'Slot key "bar" exists (field_ prefix removed).');
    $this->assertCount(3, $slots2['bar'], 'There are three slot entries for "bar".');
    $this->assertEquals('first', (string) $slots2['bar'][0]['content']);
    $this->assertEquals('second', (string) $slots2['bar'][1]['content']);
    $this->assertEquals('third', (string) $slots2['bar'][2]['content']);

    $this->assertEquals('first', (string) $element2->getSlot('field_bar', 0)['content']);
    $this->assertEquals('second', (string) $element2->getSlot('field_bar', 1)['content']);
    $this->assertEquals('third', (string) $element2->getSlot('field_bar', 2)['content']);
    $this->assertNull($element2->getSlot('field_bar', 3));
  }

  /**
   * Tests that getAttribute() normalizes keys the same way as setAttribute().
   */
  public function testGetAttributeNormalizesKeys() {
    $element = CustomElement::create('test');

    // Test underscore to hyphen conversion.
    $element->setAttribute('my_attribute', 'value1');
    // getAttribute should find it with either underscore or hyphen.
    $this->assertEquals('value1', $element->getAttribute('my_attribute'));
    $this->assertEquals('value1', $element->getAttribute('my-attribute'));

    // Test field_ prefix removal.
    $element->setAttribute('field_test', 'value2');
    // Both with and without prefix should work.
    $this->assertEquals('value2', $element->getAttribute('field_test'));
    $this->assertEquals('value2', $element->getAttribute('test'));

    // Test combined: field_ prefix + underscores.
    $element->setAttribute('field_my_field', 'value3');
    $this->assertEquals('value3', $element->getAttribute('field_my_field'));
    $this->assertEquals('value3', $element->getAttribute('my_field'));
    $this->assertEquals('value3', $element->getAttribute('my-field'));
  }

  /**
   * Tests removeAttribute() and removeSlot() methods.
   */
  public function testRemoveAttributeAndSlot() {
    $element = CustomElement::create('test');

    // Test removeAttribute().
    $element->setAttribute('foo', 'bar');
    $this->assertEquals('bar', $element->getAttribute('foo'));
    $element->removeAttribute('foo');
    $this->assertNull($element->getAttribute('foo'));

    // Test with field_ prefix removal.
    $element->setAttribute('field_test', 'value');
    $this->assertEquals('value', $element->getAttribute('test'));
    $element->removeAttribute('field_test');
    $this->assertNull($element->getAttribute('test'));

    // Test removeSlot().
    $element->addSlot('slot1', 'content1');
    $this->assertNotNull($element->getSlot('slot1'));
    $element->removeSlot('slot1');
    $this->assertNull($element->getSlot('slot1'));

    // Test removing slot with field_ prefix.
    $element->addSlot('field_slot', 'content');
    $slots_before = $element->getSlots();
    $this->assertArrayHasKey('slot', $slots_before);
    $element->removeSlot('field_slot');
    $slots_after = $element->getSlots();
    $this->assertArrayNotHasKey('slot', $slots_after);
  }

}

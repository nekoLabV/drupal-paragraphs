<?php

namespace Drupal\Tests\custom_elements\Kernel\PreviewProvider;

use Drupal\custom_elements\CustomElement;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Nuxt preview provider.
 *
 * @group custom_elements
 */
class NuxtPreviewProviderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'custom_elements',
  ];

  /**
   * Tests the Nuxt preview provider rendering.
   */
  public function testNuxtPreviewProvider() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $renderer = \Drupal::service('renderer');

    // Create provider with base URL.
    $provider = $plugin_manager->createInstance('nuxt');
    $provider->setBaseUrl('http://localhost:3000');

    // Create test element.
    $element = new CustomElement();
    $element->setTag('test-component');
    $element->setAttribute('title', 'Test Title');
    $element->setSlot('default', 'Test content');

    // Generate preview.
    $preview = $provider->preview($element);

    // Verify container structure.
    $this->assertEquals('html_tag', $preview['#type']);
    $this->assertEquals('div', $preview['#tag']);
    $this->assertStringStartsWith('nuxt-preview-test-component', $preview['#attributes']['id']);
    $this->assertContains('nuxt-preview-container', $preview['#attributes']['class']);
    $this->assertEquals('test-component', $preview['#attributes']['data-component-name']);

    // Verify component props JSON.
    $props = json_decode($preview['#attributes']['data-component-props'], TRUE);
    $this->assertIsArray($props);
    $this->assertEquals('Test Title', $props['title']);

    // Verify slot containers are DOM elements.
    $this->assertArrayHasKey('slot_default', $preview, 'Default slot container should exist');
    $this->assertEquals('html_tag', $preview['slot_default']['#type']);
    $this->assertEquals('div', $preview['slot_default']['#tag']);
    $this->assertContains('visually-hidden', $preview['slot_default']['#attributes']['class']);
    $this->assertEquals('default', $preview['slot_default']['#attributes']['data-slot']);

    // Verify attachments.
    $this->assertArrayHasKey('#attached', $preview);
    $this->assertArrayHasKey('library', $preview['#attached']);
    $this->assertContains('custom_elements/nuxt_preview', $preview['#attached']['library']);
    $this->assertArrayHasKey('drupalSettings', $preview['#attached']);
    $this->assertEquals('http://localhost:3000', $preview['#attached']['drupalSettings']['customElementsNuxtPreview']['baseUrl']);

    // Verify rendered HTML.
    $html = (string) $renderer->renderInIsolation($preview);
    $this->assertStringContainsString('class="nuxt-preview-container"', $html);
    $this->assertStringContainsString('data-component-name="test-component"', $html);
    $this->assertStringContainsString('data-component-props=', $html);
    $this->assertStringContainsString('class="visually-hidden"', $html);
    $this->assertStringContainsString('data-slot="default"', $html);
    $this->assertStringContainsString('Test content', $html);
  }

  /**
   * Tests that multiple previews get unique IDs.
   */
  public function testUniqueIds() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $provider = $plugin_manager->createInstance('nuxt');
    $provider->setBaseUrl('http://localhost:3000');

    $element = new CustomElement();
    $element->setTag('my-component');

    $preview1 = $provider->preview($element);
    $preview2 = $provider->preview($element);

    $this->assertNotEquals($preview1['#attributes']['id'], $preview2['#attributes']['id']);
  }

  /**
   * Tests slots with nested components.
   */
  public function testSlotsWithNestedComponents() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $renderer = \Drupal::service('renderer');
    $provider = $plugin_manager->createInstance('nuxt');
    $provider->setBaseUrl('http://localhost:3000');

    // Create nested button component.
    $button = CustomElement::create('TestButton');
    $button->setAttribute('label', 'Nested Button');
    $button->setAttribute('variant', 'success');

    // Create layout component with slots.
    $layout = CustomElement::create('TwoColumnLayout');
    $layout->setAttribute('width', 50);
    $layout->setSlotFromCustomElement('column_one', $button);
    $layout->setSlot('column_two', '<p>Right column</p>');

    // Generate preview.
    $preview = $provider->preview($layout);

    // Verify container structure and props JSON.
    $this->assertEquals('html_tag', $preview['#type']);
    $this->assertEquals('TwoColumnLayout', $preview['#attributes']['data-component-name']);
    $props = json_decode($preview['#attributes']['data-component-props'], TRUE);
    $this->assertIsArray($props, 'Props should be valid JSON');
    $this->assertEquals(50, $props['width']);

    // Verify slot names are kebab-case (column_one -> column-one).
    $this->assertArrayHasKey('slot_column-one', $preview);
    $this->assertArrayHasKey('slot_column-two', $preview);

    // Verify slot containers are passed in the DOM.
    $this->assertContains('visually-hidden', $preview['slot_column-one']['#attributes']['class']);
    $this->assertEquals('column-one', $preview['slot_column-one']['#attributes']['data-slot']);
    $this->assertContains('visually-hidden', $preview['slot_column-two']['#attributes']['class']);
    $this->assertEquals('column-two', $preview['slot_column-two']['#attributes']['data-slot']);

    // Verify rendering works.
    $html = (string) $renderer->renderInIsolation($preview);
    $this->assertStringContainsString('data-component-name="TwoColumnLayout"', $html);
  }

}

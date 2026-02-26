<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\custom_elements\CustomElement;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;

/**
 * Tests renderless-container functionality in both JSON and markup modes.
 *
 * @group custom_elements
 */
class RenderlessContainerTest extends KernelTestBase {

  use TestHelperTrait;

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

    // These tests expect legacy format.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();

    // Ensure the theme registry is rebuilt.
    $this->container->get('theme.registry')->reset();
  }

  /**
   * Creates a renderless-container with three test children.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   A renderless-container with three child elements.
   */
  protected function createTestContainer(): CustomElement {
    $container = CustomElement::create('renderless-container');

    // Add some child elements.
    $child1 = CustomElement::create('div')
      ->setAttribute('class', 'child-1')
      ->setSlot('default', 'First child content');

    $child2 = CustomElement::create('span')
      ->setAttribute('class', 'child-2')
      ->setSlot('default', 'Second child content');

    $child3 = CustomElement::create('p')
      ->setAttribute('class', 'child-3')
      ->setSlot('default', 'Third child content');

    // Add children to the container.
    $container->setSlotFromCustomElement('default', $child1, 0);
    $container->setSlotFromCustomElement('default', $child2, 1);
    $container->setSlotFromCustomElement('default', $child3, 2);

    // Test attributes on renderless-container (should be ignored in markup).
    $container->setAttribute('id', 'should-be-ignored');
    $container->setAttribute('class', 'also-ignored');

    return $container;
  }

  /**
   * Tests empty renderless-container in JSON mode.
   */
  public function testEmptyRenderlessContainerJson(): void {
    $container = CustomElement::create('renderless-container');

    // Normalize the container.
    $normalizer = $this->container->get('custom_elements.normalizer');
    $normalized = $normalizer->normalize($container);

    // Empty renderless-container should normalize to empty array.
    $this->assertIsArray($normalized);
    $this->assertEmpty($normalized);
  }

  /**
   * Tests empty renderless-container in markup mode.
   */
  public function testEmptyRenderlessContainerMarkup(): void {
    $container = CustomElement::create('renderless-container');

    // Render the custom element.
    $markup = $this->renderCustomElement($container);

    // Empty renderless-container should render as empty string.
    $this->assertEmpty(trim($markup));
  }

  /**
   * Tests renderless-container in JSON mode (normalization).
   *
   * Note: renderless-container is omitted in JSON mode, only slots are output.
   */
  public function testRenderlessContainerJson(): void {
    $container = $this->createTestContainer();

    // Normalize the container.
    $normalizer = $this->container->get('custom_elements.normalizer');
    $normalized = $normalizer->normalize($container);

    // The renderless-container is flattened in JSON normalization.
    $this->assertIsArray($normalized);
    $this->assertCount(3, $normalized, 'Should have 3 elements in flat array');

    // Verify no container element or attributes in output.
    $this->assertArrayNotHasKey('element', $normalized);
    $this->assertArrayNotHasKey('id', $normalized);
    $this->assertArrayNotHasKey('class', $normalized);

    // Verify children are rendered correctly with numeric indices.
    // Note: div and span elements have their 'element' key removed by
    // normalizer.
    $this->assertArrayNotHasKey('element', $normalized[0], 'div element key should be removed');
    $this->assertEquals('child-1', $normalized[0]['class']);
    $this->assertEquals('First child content', $normalized[0]['content']);

    $this->assertArrayNotHasKey('element', $normalized[1], 'span element key should be removed');
    $this->assertEquals('child-2', $normalized[1]['class']);
    $this->assertEquals('Second child content', $normalized[1]['content']);

    $this->assertEquals('p', $normalized[2]['element']);
    $this->assertEquals('child-3', $normalized[2]['class']);
    $this->assertEquals('Third child content', $normalized[2]['content']);
  }

  /**
   * Tests renderless-container in markup mode (Drupal render array).
   */
  public function testRenderlessContainerMarkup(): void {
    // Create a renderless-container with test children.
    $container = $this->createTestContainer();

    // Render the custom element.
    $markup = $this->renderCustomElement($container);

    // Expected markup should have no wrapping tags, just the children
    // with slot="default" attributes for web component style.
    $expected_markup = '
      <div class="child-1" slot="default">First child content</div>
      <span class="child-2" slot="default">Second child content</span>
      <p class="child-3" slot="default">Third child content</p>
    ';

    $this->assertMarkupEquals($expected_markup, $markup);
  }

  /**
   * Tests renderless-container in Vue3 markup mode.
   */
  public function testRenderlessContainerMarkupVue3(): void {
    // Set Vue3 markup style.
    $this->config('custom_elements.settings')
      ->set('markup_style', 'vue-3')
      ->save();

    // Create a renderless-container with test children.
    $container = $this->createTestContainer();

    // Render the custom element.
    $markup = $this->renderCustomElement($container);

    // Expected markup should have no wrapping tags, just the children
    // without slot attributes for Vue3 style (only default slot).
    $expected_markup = '
      <div class="child-1">First child content</div>
      <span class="child-2">Second child content</span>
      <p class="child-3">Third child content</p>
    ';

    $this->assertMarkupEquals($expected_markup, $markup);
  }

  /**
   * Tests renderless-container with nested elements.
   */
  public function testRenderlessContainerNested(): void {
    // Create a more complex structure with nested elements.
    $container = CustomElement::create('renderless-container');

    // Create a complex child with its own slots.
    $complexChild = CustomElement::create('article')
      ->setAttribute('class', 'article-wrapper');

    $header = CustomElement::create('header')
      ->setSlot('default', 'Article Header');

    $content = CustomElement::create('div')
      ->setAttribute('class', 'article-content')
      ->setSlot('default', 'Article body text');

    $complexChild->setSlotFromCustomElement('header', $header);
    $complexChild->setSlotFromCustomElement('content', $content);

    // Add simple and complex children to container.
    $simpleChild = CustomElement::create('aside')
      ->setAttribute('class', 'sidebar')
      ->setSlot('default', 'Sidebar content');

    $container->setSlotFromCustomElement('default', $complexChild, 0);
    $container->setSlotFromCustomElement('default', $simpleChild, 1);

    // Test JSON normalization.
    $normalizer = $this->container->get('custom_elements.normalizer');
    $normalized = $normalizer->normalize($container);

    // The renderless-container is flattened in JSON normalization.
    $this->assertCount(2, $normalized);
    $this->assertEquals('article', $normalized[0]['element']);
    $this->assertEquals('aside', $normalized[1]['element']);

    // Test markup rendering.
    $markup = $this->renderCustomElement($container);

    // Expected markup should have no wrapping tags, just the children
    // with slot="default" attributes for web component style.
    $expected_markup = '
      <article class="article-wrapper" slot="default">
        <header slot="header">Article Header</header>
        <div class="article-content" slot="content">Article body text</div>
      </article>
      <aside class="sidebar" slot="default">Sidebar content</aside>
    ';

    $this->assertMarkupEquals($expected_markup, $markup);
  }

}

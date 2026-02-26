<?php

namespace Drupal\Tests\custom_elements\Kernel\PreviewProvider;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Markup preview provider.
 *
 * @group custom_elements
 */
class MarkupPreviewProviderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'custom_elements',
  ];

  /**
   * Tests the markup preview provider.
   */
  public function testMarkupPreview() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $renderer = \Drupal::service('renderer');

    $provider = $plugin_manager->createInstance('markup');
    $this->assertInstanceOf(CustomElementsPreviewProviderInterface::class, $provider);

    $element = new CustomElement();
    $element->setTag('test-element');
    $element->setAttribute('title', 'Test Title');
    $element->setSlot('default', 'Test content');

    $preview = $provider->preview($element);

    $this->assertIsArray($preview);
    $this->assertEquals('container', $preview['#type']);
    $this->assertStringContainsString('custom-elements-preview', $preview['#attributes']['class'][0]);
    $this->assertStringContainsString('custom-elements-preview--markup', $preview['#attributes']['class'][1]);

    // Check that content contains the escaped markup.
    $this->assertArrayHasKey('content', $preview);
    $this->assertStringContainsString('<test-element', $preview['content']['#plain_text']);
    $this->assertStringContainsString('title="Test Title"', $preview['content']['#plain_text']);
    $this->assertStringContainsString('Test content', $preview['content']['#plain_text']);

    $rendered = $renderer->renderInIsolation($preview);
    $rendered_html = (string) $rendered;

    // Markup should be HTML-escaped in output.
    $this->assertStringContainsString('&lt;test-element', $rendered_html);
    $this->assertStringContainsString('title=&quot;Test Title&quot;', $rendered_html);
    $this->assertStringContainsString('Test content', $rendered_html);
    $this->assertStringContainsString('&lt;/test-element&gt;', $rendered_html);
    $this->assertStringContainsString('class="custom-elements-preview custom-elements-preview--markup"', $rendered_html);
  }

}

<?php

namespace Drupal\Tests\custom_elements\Kernel\PreviewProvider;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the JSON preview provider.
 *
 * @group custom_elements
 */
class JsonPreviewProviderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'custom_elements',
  ];

  /**
   * Tests the JSON preview provider.
   */
  public function testJsonPreview() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $renderer = \Drupal::service('renderer');

    $definitions = $plugin_manager->getDefinitions();
    $this->assertArrayHasKey('json', $definitions);
    $this->assertEquals('JSON structure', (string) $definitions['json']['label']);

    $provider = $plugin_manager->createInstance('json');
    $this->assertInstanceOf(CustomElementsPreviewProviderInterface::class, $provider);

    $element = new CustomElement();
    $element->setTag('test-element');
    $element->setAttribute('title', 'Test Title');
    $element->setAttribute('count', 42);
    $element->addSlot('default', 'Test content');

    $preview = $provider->preview($element);

    $this->assertIsArray($preview);
    $this->assertEquals('container', $preview['#type']);
    $this->assertArrayHasKey('content', $preview);
    $this->assertArrayHasKey('#plain_text', $preview['content']);

    // Verify JSON is valid and properly formatted.
    $json_text = $preview['content']['#plain_text'];
    $decoded = json_decode($json_text, TRUE);
    $this->assertNotNull($decoded, 'JSON is valid');

    // toArray() uses 'element' for tag name and 'props' for attributes.
    $this->assertEquals('test-element', $decoded['element']);
    $this->assertArrayHasKey('props', $decoded);
    $this->assertEquals('Test Title', $decoded['props']['title']);
    $this->assertEquals(42, $decoded['props']['count']);
    $this->assertArrayHasKey('slots', $decoded);

    $rendered = $renderer->renderInIsolation($preview);
    $rendered_html = (string) $rendered;

    // JSON quotes are HTML-escaped in output.
    $this->assertStringContainsString('&quot;element&quot;', $rendered_html);
    $this->assertStringContainsString('test-element', $rendered_html);
    $this->assertStringContainsString('custom-elements-preview--json', $rendered_html);
  }

}

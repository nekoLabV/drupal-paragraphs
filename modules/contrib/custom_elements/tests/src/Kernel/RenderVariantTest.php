<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Symfony\Component\HttpFoundation\Request;
use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_elements\CustomElement;

/**
 * Tests custom element render variant functionality.
 *
 * @group custom_elements
 */
class RenderVariantTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['custom_elements'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['custom_elements']);
  }

  /**
   * Tests toRenderArray() with NULL uses default configuration.
   */
  public function testRenderVariantDefaultConfiguration() {
    // Default should be 'markup' for BC - renders custom elements markup
    // without preview.
    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray();

    // Should render as custom elements markup (not preview).
    $this->assertEquals('custom_element', $render_array['#theme']);
    $this->assertSame($element, $render_array['#custom_element']);
    // Verify it's NOT a preview render (which would have #type = 'container').
    $this->assertArrayNotHasKey('#type', $render_array);

    // Render in isolation and verify custom element tag appears in output.
    $renderer = \Drupal::service('renderer');
    $rendered = $renderer->renderInIsolation($render_array);
    $rendered_html = (string) $rendered;
    $this->assertStringContainsString('<test-element', $rendered_html);
  }

  /**
   * Tests toRenderArray() API response detection and config defaults.
   *
   * Verifies that:
   * - API responses always use 'markup' variant regardless of config
   * - Non-API requests respect the configured default
   * - Both lupus_ce_renderer attribute and custom_elements format are
   *   detected.
   */
  public function testRenderVariantApiResponseAndConfigDefaults() {
    // Set config to use preview by default.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('default_render_variant', 'preview:markup')
      ->save();

    $request_stack = \Drupal::service('request_stack');

    // Test 1: API response via lupus_ce_renderer attribute.
    $request = Request::create('/');
    $request->attributes->set('lupus_ce_renderer', TRUE);
    $request_stack->push($request);

    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray();

    // Should use 'markup' variant despite config being set to preview.
    $this->assertEquals('custom_element', $render_array['#theme']);
    $this->assertSame($element, $render_array['#custom_element']);

    $request_stack->pop();

    // Test 2: API response via custom_elements request format.
    $request = Request::create('/');
    $request->setRequestFormat('custom_elements');
    $request_stack->push($request);

    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray();

    // Should use 'markup' variant despite config being set to preview.
    $this->assertEquals('custom_element', $render_array['#theme']);
    $this->assertSame($element, $render_array['#custom_element']);

    $request_stack->pop();

    // Test 3: Non-API request should respect configured default.
    $request = Request::create('/');
    $request_stack->push($request);

    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray();

    // Should use configured preview variant for non-API requests.
    $this->assertEquals('container', $render_array['#type']);
    $this->assertArrayHasKey('#attributes', $render_array);
    $this->assertContains('custom-elements-preview--markup', $render_array['#attributes']['class']);

    $request_stack->pop();
  }

  /**
   * Tests toRenderArray() respects configuration change.
   */
  public function testRenderVariantConfigurationChange() {
    // Change configuration to a specific preview provider to avoid needing
    // request setup for auto-selection.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('default_render_variant', 'preview:markup')
      ->save();

    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray();

    // Should use the markup preview provider.
    $this->assertEquals('container', $render_array['#type']);
    $this->assertArrayHasKey('#attributes', $render_array);
    $this->assertContains('custom-elements-preview--markup', $render_array['#attributes']['class']);
  }

  /**
   * Tests toRenderArray() with explicit 'markup' variant.
   */
  public function testRenderVariantExplicitMarkup() {
    // Even if config is set to preview, explicit 'markup' should work.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('default_render_variant', 'preview')
      ->save();

    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray('markup');

    $this->assertEquals('custom_element', $render_array['#theme']);
    $this->assertSame($element, $render_array['#custom_element']);
  }

  /**
   * Tests toRenderArray() with 'preview' variant.
   */
  public function testRenderVariantPreview() {
    // Create a request for preview auto-selection.
    $request = Request::create('/');
    $request_stack = \Drupal::service('request_stack');
    $request_stack->push($request);

    $element = CustomElement::create('test-element');
    $render_array = $element->toRenderArray('preview');

    // Should use preview rendering.
    $this->assertEquals('container', $render_array['#type']);
    $this->assertArrayHasKey('#attributes', $render_array);
    $this->assertContains('custom-elements-preview', $render_array['#attributes']['class']);

    // Clean up.
    $request_stack->pop();
  }

  /**
   * Tests toRenderArray() with invalid variant throws exception.
   */
  public function testRenderVariantInvalidThrowsException() {
    $element = CustomElement::create('test-element');

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid render variant: invalid');
    $element->toRenderArray('invalid');
  }

  /**
   * Tests toRenderArray() with non-existent preview provider.
   */
  public function testRenderVariantNonExistentPreviewProvider() {
    $element = CustomElement::create('test-element');

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Preview provider "nonexistent" does not exist.');
    $element->toRenderArray('preview:nonexistent');
  }

  /**
   * Tests preview() method with NULL uses auto-selection.
   */
  public function testPreviewAutoSelection() {
    // Create a request for preview auto-selection.
    $request = Request::create('/');
    $request_stack = \Drupal::service('request_stack');
    $request_stack->push($request);

    $element = CustomElement::create('test-element');
    $render_array = $element->preview();

    // Should use preview rendering with auto-selected provider.
    $this->assertEquals('container', $render_array['#type']);
    $this->assertArrayHasKey('#attributes', $render_array);
    $this->assertContains('custom-elements-preview', $render_array['#attributes']['class']);

    // Clean up.
    $request_stack->pop();
  }

  /**
   * Tests preview() method with specific provider ID.
   */
  public function testPreviewSpecificProvider() {
    $element = CustomElement::create('test-element');
    $render_array = $element->preview('markup');

    // Should use the markup preview provider.
    $this->assertEquals('container', $render_array['#type']);
    $this->assertArrayHasKey('#attributes', $render_array);
    $this->assertContains('custom-elements-preview--markup', $render_array['#attributes']['class']);
  }

}

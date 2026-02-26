<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the Custom Elements preview functionality.
 *
 * @group custom_elements
 */
class CustomElementsPreviewTest extends KernelTestBase {

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
    $this->installConfig(['system']);
  }

  /**
   * Tests the preview provider plugin manager.
   */
  public function testPreviewProviderPluginManager() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $this->assertNotNull($plugin_manager);

    // Check that the markup provider is available.
    $definitions = $plugin_manager->getDefinitions();
    $this->assertArrayHasKey('markup', $definitions);
    $this->assertEquals('Markup code', (string) $definitions['markup']['label']);

    // Check that Nuxt provider is available.
    $this->assertArrayHasKey('nuxt', $definitions);
    $this->assertEquals('JavaScript - Nuxt', (string) $definitions['nuxt']['label']);
  }

  /**
   * Tests the preview resolver service.
   */
  public function testPreviewResolver() {
    // Get the provider from resolver.
    $resolver = \Drupal::service('custom_elements.preview_resolver');
    $request = Request::create('/');
    $provider = $resolver->getProvider($request);

    // Some provider should be returned, always, as the markup provider is
    // applicable and available out of the box.
    $this->assertInstanceOf(CustomElementsPreviewProviderInterface::class, $provider);
  }

  /**
   * Tests the CustomElement::preview() method.
   */
  public function testCustomElementPreviewMethod() {
    // Create a test custom element.
    $element = new CustomElement();
    $element->setTag('my-component');
    $element->setAttribute('id', 'test-123');
    $element->setSlot('header', 'Header content');
    $element->setSlot('default', 'Body content');

    // Generate preview using the helper method and verify it worked.
    // The markup provider should be used (it's the default).
    $preview = $element->preview();
    $this->assertIsArray($preview);
    $this->assertEquals('container', $preview['#type']);
  }

  /**
   * Tests preview providers with base URL.
   */
  public function testPreviewProvidersWithBaseUrl() {
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $nuxt_provider = $plugin_manager->createInstance('nuxt');
    $nuxt_provider->setBaseUrl('http://localhost:3000');
    $request = Request::create('/');
    $this->assertTrue($nuxt_provider->isApplicable($request), 'Nuxt provider should be applicable with base URL');
  }

  /**
   * Tests that preview providers without base URL are not applicable.
   */
  public function testPreviewProvidersWithoutBaseUrlNotApplicable() {
    // Test Nuxt provider without base URL.
    // Provider should not be applicable without a base URL.
    $plugin_manager = \Drupal::service('plugin.manager.custom_elements_preview_provider');
    $nuxt_provider = $plugin_manager->createInstance('nuxt');
    $request = Request::create('/');
    $this->assertFalse($nuxt_provider->isApplicable($request), 'Nuxt provider should not be applicable without base URL');

    // After setting base URL, it should become applicable.
    $nuxt_provider->setBaseUrl('http://localhost:3000');
    $this->assertTrue($nuxt_provider->isApplicable($request), 'Nuxt provider should be applicable with base URL');
  }

}

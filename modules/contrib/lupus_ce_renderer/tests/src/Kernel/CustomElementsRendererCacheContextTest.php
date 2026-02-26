<?php

namespace Drupal\Tests\lupus_ce_renderer\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests that responses include the content format cache context.
 *
 * @group lupus_ce_renderer
 */
class CustomElementsRendererCacheContextTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'metatag',
    'token',
    'node',
    'custom_elements',
    'lupus_ce_renderer',
    // Add other required modules here.
  ];

  /**
   * Helper to assert the cache context has been added correctly.
   */
  protected function assertContentFormatCacheContextAndContent($response, $expected_format) {
    $cacheable_metadata = $response->getCacheableMetadata();
    $contexts = $cacheable_metadata->getCacheContexts();
    $this->assertContains(
      'lupus_ce_renderer_content_format',
      $contexts,
      'The response contains the lupus_ce_renderer_content_format cache context.'
    );

    // Convert cache context tokens to cache keys and inspect the actual string.
    $cache_contexts_manager = $this->container->get('cache_contexts_manager');
    $context_keys = $cache_contexts_manager->convertTokensToKeys($contexts)->getKeys();
    $found = FALSE;
    foreach ($context_keys as $key) {
      if (str_contains($key, 'lupus_ce_renderer_content_format')) {
        $found = TRUE;
        $this->assertStringContainsString(
          'ce:' . $expected_format,
          $key,
          "Cache context string contains expected content format: $expected_format"
        );
      }
    }
    $this->assertTrue($found, 'Found lupus_ce_renderer_content_format context key.');

    $data = json_decode($response->getContent(), TRUE);
    $this->assertArrayHasKey('content', $data, 'Response contains "content" attribute.');

    if ($expected_format === 'markup') {
      $this->assertIsString($data['content'], 'Content is a string for markup format.');
    }
    elseif ($expected_format === 'json') {
      $this->assertIsArray($data['content'], 'Content is an array for json format.');
    }
    else {
      $this->fail('Unknown expected format: ' . $expected_format);
    }
  }

  /**
   * Tests the content format cache context is present in the response.
   */
  public function testContentFormatCacheContext() {
    $kernel = $this->container->get('http_kernel');

    // 1. No format or content format passed.
    $request1 = Request::create('/node', 'GET');
    $response1 = $kernel->handle($request1);
    $this->assertContentFormatCacheContextAndContent($response1, 'markup');

    // 2. Only content format passed as query param.
    $request2 = Request::create('/node', 'GET', ['_content_format' => 'json']);
    $response2 = $kernel->handle($request2);
    $this->assertContentFormatCacheContextAndContent($response2, 'json');

    // 3. Content format passed as query param: markup.
    $request3 = Request::create('/node', 'GET', ['_content_format' => 'markup']);
    $request3->attributes->set('_route', 'lupus_ce_renderer.node');
    $response3 = $kernel->handle($request3);
    $this->assertContentFormatCacheContextAndContent($response3, 'markup');

    // 4. Content format set via request attribute: markup.
    $request4 = Request::create('/node', 'GET');
    $request4->attributes->set('lupus_ce_renderer.content_format', 'markup');
    $response4 = $kernel->handle($request4);
    $this->assertContentFormatCacheContextAndContent($response4, 'markup');

    // 5. Content format set via request attribute: json.
    $request5 = Request::create('/node', 'GET');
    $request5->attributes->set('lupus_ce_renderer.content_format', 'json');
    $response5 = $kernel->handle($request5);
    $this->assertContentFormatCacheContextAndContent($response5, 'json');
  }

}

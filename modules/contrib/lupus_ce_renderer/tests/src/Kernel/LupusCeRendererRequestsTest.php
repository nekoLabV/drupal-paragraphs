<?php

namespace Drupal\Tests\lupus_ce_renderer\Kernel;

use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use Drupal\node\Entity\Node;
use Drupal\Core\Site\Settings;

/**
 * Kernel test for lupus_ce_renderer request/response handling.
 *
 * @group lupus_ce_renderer
 */
class LupusCeRendererRequestsTest extends LupusCeRendererKernelTestBase {

  /**
   * Tests _format and _content_format passed by GET parameter.
   */
  public function testFormatParameters() {
    $response = $this->request($this->nodePath, [
      '_format' => 'custom_elements',
      '_content_format' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
    ]);
    $data = $this->decodeResponse($response);
    $this->assertEquals('markup', $data['content_format']);

    $response = $this->request($this->nodePath, [
      '_format' => 'custom_elements',
      '_content_format' => CustomElementsRenderer::CONTENT_FORMAT_JSON,
    ]);
    $data = $this->decodeResponse($response);
    $this->assertEquals('json', $data['content_format']);

    // Test with no _format passed, it should not render into JSON.
    $response = $this->request($this->nodePath);
    $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    $this->assertStringContainsString('<!DOCTYPE html>', $response->getContent());

    // Now, enable lupus_ce_renderer globally via Settings.
    new Settings(Settings::getAll() + ['lupus_ce_renderer_enable' => TRUE]);

    $response = $this->request($this->nodePath);
    $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
    $data = $this->decodeResponse($response);
    $this->assertArrayHasKey('content', $data);

    $response = $this->request($this->nodePath, ['_format' => 'html']);
    $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    $this->assertStringContainsString('<!DOCTYPE html>', $response->getContent());
  }

  /**
   * Tests default content format 'markup' to be set in Settings.
   */
  public function testDefaultContentFormatHtml() {
    // Set default format to markup.
    new Settings(Settings::getAll() + [
      'lupus_ce_renderer_default_format' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
    ]);
    $response = $this->request($this->nodePath, ['_format' => 'custom_elements']);
    $data = $this->decodeResponse($response);
    $this->assertEquals('markup', $data['content_format']);
  }

  /**
   * Tests default content format 'json' to be set in Settings.
   */
  public function testDefaultContentFormatJson() {
    // Set default format to json - using proper Settings approach.
    new Settings(Settings::getAll() + [
      'lupus_ce_renderer_default_format' => CustomElementsRenderer::CONTENT_FORMAT_JSON,
    ]);
    $response = $this->request($this->nodePath, ['_format' => 'custom_elements']);
    $data = $this->decodeResponse($response);
    $this->assertEquals('json', $data['content_format']);

    // Parameter _content_format must override lupus_ce_renderer_default_format.
    $response = $this->request($this->nodePath, [
      '_format' => 'custom_elements',
      '_content_format' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
    ]);
    $data = $this->decodeResponse($response);
    $this->assertEquals('markup', $data['content_format']);
  }

  /**
   * Tests API response structure (metatags, breadcrumbs, page_layout).
   */
  public function testApiResponseData() {
    new Settings(Settings::getAll() + [
      'lupus_ce_renderer_default_format' => CustomElementsRenderer::CONTENT_FORMAT_MARKUP,
    ]);

    $response = $this->request($this->nodePath, ['_format' => 'custom_elements']);
    $data = $this->decodeResponse($response);

    $this->assertEquals([], $data['messages'], 'The messages key is present in the response and is an empty array.');
    $this->assertArrayHasKey('metatags', $data, 'The metatags key is present in the response.');
    $this->assertArrayHasKey('meta', $data['metatags'], 'The meta key is present in the metatags array.');
    $this->assertArrayHasKey('link', $data['metatags'], 'The link key is present in the metatags array.');
    $this->assertArrayHasKey('breadcrumbs', $data, 'The breadcrumbs key is present in the response.');
    $this->assertEquals([
      [
        'frontpage' => 1,
        'url' => '/',
        'label' => 'Home',
      ],
    ], $data['breadcrumbs'], 'The breadcrumbs key has the correct structure and values.');
    $this->assertEquals('markup', $data['content_format'], 'The content_format is markup.');
    $this->assertStringContainsString('Test node', $data['content'], 'The content contains the node title.');
    $this->assertEquals('default', $data['page_layout'], 'The page_layout is default.');
    $this->assertEquals('Test node', $data['title'], 'The title matches the node title.');
    $this->assertArrayHasKey('local_tasks', $data, 'The local_tasks key is present in the response.');
  }

  /**
   * Tests HTTP status codes for access and error cases.
   */
  public function testStatusCodes() {
    // 200 for existing node.
    $response = $this->request($this->nodePath, ['_format' => 'custom_elements']);
    $this->assertEquals(200, $response->getStatusCode());

    // 404 for non-existing node.
    $nonExistingPath = '/node/999999';
    $request = Request::create($nonExistingPath, 'GET', ['_format' => 'custom_elements']);
    $request->attributes->set('_route', 'entity.node.canonical');
    $response = $this->container->get('http_kernel')->handle($request);
    $this->assertEquals(404, $response->getStatusCode());

    // 403 for unpublished node.
    $unpublished = Node::create([
      'type' => 'page',
      'title' => 'Unpublished',
      'status' => FALSE,
    ]);
    $unpublished->save();

    $request = Request::create('/node/' . $unpublished->id(), 'GET', ['_format' => 'custom_elements']);
    $request->attributes->set('_route', 'entity.node.canonical');
    $request->attributes->set('node', $unpublished);
    $response = $this->container->get('http_kernel')->handle($request);
    $this->assertEquals(403, $response->getStatusCode());

    // Set admin user and check if it works now.
    $this->container->get('current_user')->setAccount($this->adminUser);
    $request = Request::create('/node/' . $unpublished->id(), 'GET', ['_format' => 'custom_elements']);
    $response = $this->container->get('http_kernel')->handle($request);
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
   * Tests handling other formats, e.g. JSON, still works.
   */
  public function testsHandlingOtherFormats() {
    // Verifies the core user login status endpoint still works.
    $this->container->get('current_user')->setAccount(User::getAnonymousUser());
    $request = Request::create('/user/login_status', 'GET', ['_format' => 'json']);
    $response = $this->container->get('http_kernel')->handle($request);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('0', $response->getContent());

    // When requesting it as custom_elements format, it should not work.
    $request = Request::create('/user/login_status', 'GET', ['_format' => 'custom_elements']);
    $response = $this->container->get('http_kernel')->handle($request);
    $this->assertEquals(404, $response->getStatusCode());
  }

  /**
   * Tests redirects are turned into redirect-API-responses.
   */
  public function testRedirectResponses() {
    // Ensure current user is logged in.
    $this->container->get('current_user')->setAccount($this->adminUser);

    // Request /user with custom_elements format.
    $request = Request::create('/user', 'GET', ['_format' => 'custom_elements']);
    $request->attributes->set('_route', 'entity.user.canonical');

    $response = $this->container->get('http_kernel')->handle($request);

    // Should be a JSON response with redirect information to /user/login.
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);
    $this->assertIsArray($data);
    $this->assertArrayHasKey('redirect', $data);
    $this->assertEquals(302, $data['redirect']['statusCode']);
    $this->assertStringContainsString('/user/' . $this->adminUser->id(), $data['redirect']['url']);
  }

  /**
   * Tests redirect response for routes not supporting custom_elements format.
   */
  public function testCustomElementsFormatRedirectForUnsupportedRoute() {
    // Set admin user to ensure access to edit form.
    $this->container->get('current_user')->setAccount($this->adminUser);

    // Request node edit route with custom_elements format.
    $edit_path = '/node/' . $this->node->id() . '/edit';
    $request = Request::create($edit_path, 'GET', ['_format' => 'custom_elements']);
    $request->attributes->set('_route', 'entity.node.edit_form');
    $request->attributes->set('node', $this->node);

    $response = $this->container->get('http_kernel')->handle($request);

    // Should be a JSON response with redirect information.
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);
    $this->assertIsArray($data);
    $this->assertArrayHasKey('redirect', $data);
    $this->assertTrue($data['redirect']['external']);
    $this->assertEquals(302, $data['redirect']['statusCode']);
    $this->assertStringContainsString($edit_path, $data['redirect']['url']);
  }

  /**
   * Tests that local tasks are delivered when cache is enabled.
   */
  public function testLocalTasksWithDynamicPageCache() {
    $this->enableDynamicPageCache();

    // Ensure current user is logged in.
    $this->container->get('current_user')->setAccount($this->adminUser);

    // First request: warm up the cache.
    $request1 = Request::create($this->nodePath, 'GET', ['_format' => 'custom_elements']);
    $response1 = $this->container->get('http_kernel')->handle($request1);
    $this->assertEquals(200, $response1->getStatusCode(), 'First request is successful.');

    // Second request: should be served from dynamic page cache.
    $request2 = Request::create($this->nodePath, 'GET', ['_format' => 'custom_elements']);
    $response2 = $this->container->get('http_kernel')->handle($request2);
    $data2 = json_decode($response2->getContent(), TRUE);

    // Verify that the response is cached and the local tasks are included.
    $this->assertEquals('HIT', $response2->headers->get('x-drupal-dynamic-cache'), 'Dynamic page cache HIT.');
    $this->assertArrayHasKey('local_tasks', $data2, 'local_tasks key exists in response.');
    $this->assertArrayHasKey('primary', $data2['local_tasks'], 'Primary local tasks are present.');

    $primary = $data2['local_tasks']['primary'];
    $this->assertGreaterThanOrEqual(2, count($primary), 'At least two primary local tasks are present.');

    // Assert the first is "View", the second is "Edit".
    $this->assertEquals([
      'url' => $this->nodePath,
      'label' => 'View',
      'active' => TRUE,
    ], $primary[0]);
    $this->assertEquals([
      'url' => $this->nodePath . '/edit',
      'label' => 'Edit',
      'active' => FALSE,
    ], $primary[1]);
  }

}

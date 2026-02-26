<?php

namespace Drupal\Tests\lupus_ce_renderer\Kernel;

use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

/**
 * Kernel test for lupus_ce_renderer error page handling.
 *
 * @group lupus_ce_renderer
 */
class LupusCeRendererErrorPagesTest extends LupusCeRendererKernelTestBase {

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
   * Tests serving custom 404 and 403 error pages as configured.
   */
  public function testCustomErrorPages() {
    // Create custom 404 and 403 nodes.
    $custom404 = Node::create([
      'type' => 'page',
      'title' => 'Custom Not Found',
      'status' => 1,
      'uid' => $this->adminUser->id(),
    ]);
    $custom404->save();

    $custom403 = Node::create([
      'type' => 'page',
      'title' => 'Custom Access Denied',
      'status' => 1,
      'uid' => $this->adminUser->id(),
    ]);
    $custom403->save();

    // Set the custom error pages in site configuration.
    $config_factory = $this->container->get('config.factory');
    $site_config = $config_factory->getEditable('system.site');
    $site_config
      ->set('page.404', '/node/' . $custom404->id())
      ->set('page.403', '/node/' . $custom403->id())
      ->save();

    // Test 404: request a non-existing node.
    $nonExistingPath = '/node/9999999';
    $request = Request::create($nonExistingPath, 'GET', ['_format' => 'custom_elements']);
    $request->attributes->set('_route', 'entity.node.canonical');
    $response = $this->container->get('http_kernel')->handle($request);

    $this->assertEquals(404, $response->getStatusCode());
    $data = $this->decodeResponse($response);
    $this->assertIsArray($data);
    $this->assertArrayHasKey('content', $data);
    // Only assert the custom title, not the body.
    $this->assertEquals('Custom Not Found', $data['title']);

    // Test 403: request an unpublished node as anonymous.
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
    $data = $this->decodeResponse($response);
    $this->assertIsArray($data);
    $this->assertArrayHasKey('content', $data);
    // Only assert the custom title, not the body.
    $this->assertEquals('Custom Access Denied', $data['title']);
  }

}

<?php

namespace Drupal\Tests\lupus_ce_renderer\Kernel;

use Symfony\Component\HttpFoundation\Request;

/**
 * Tests redirect handling in lupus_ce_renderer.
 *
 * @group lupus_ce_renderer
 */
class LupusCeRendererMessageTest extends LupusCeRendererKernelTestBase {

  /**
   * Tests including drupal messages in redirect API responses.
   */
  public function testDrupalMessagesInRedirectResponse() {
    // Ensure current user is logged in.
    $this->container->get('current_user')->setAccount($this->adminUser);
    // Enable messages in redirect response.
    $this->config('lupus_ce_renderer.settings')->set('redirect_response.add_drupal_messages', TRUE)->save();

    // Add a status message to the session.
    $session = $this->container->get('session');
    $session->start();
    $this->container->get('messenger')->addStatus('Redirected successfully!');

    // Let the system trigger the /user -> /user/ID redirect.
    $request = Request::create('/user', 'GET', ['_format' => 'custom_elements']);
    $request->attributes->set('_route', 'entity.user.canonical');
    $request->setSession($session);
    $response = $this->container->get('http_kernel')->handle($request);

    // Check if the response is a redirect.
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(302, $data['redirect']['statusCode']);

    // Assert the message is included in the response.
    $this->assertArrayHasKey('messages', $data, 'Messages key exists in redirect response.');
    $this->assertArrayHasKey('success', $data['messages'], 'Success messages are present.');
    $this->assertContains('Redirected successfully!', $data['messages']['success']);

    // Do another request to check if the message is gone.
    // Simulate the next requests, using the same session.
    $next_request = Request::create($this->nodePath, 'GET', ['_format' => 'custom_elements']);
    $next_request->setSession($session);

    $next_response = $this->container->get('http_kernel')->handle($next_request);
    $next_data = json_decode($next_response->getContent(), TRUE);

    // The message should now be present in the next response.
    $this->assertArrayHasKey('messages', $next_data, 'Messages key exists in next response.');
    $this->assertArrayNotHasKey('success', $next_data['messages'], 'Success message is not present in next response.');
  }

  /**
   * Tests delivering drupal messages with the next request.
   */
  public function testDrupalMessagesDeliveredWithNextRequest() {
    // Ensure current user is logged in.
    $this->container->get('current_user')->setAccount($this->adminUser);
    // Disable messages in redirect response (default).
    $this->config('lupus_ce_renderer.settings')->set('redirect_response.add_drupal_messages', FALSE)->save();

    // Use the same session for both requests, so messages are preserved.
    $session = $this->container->get('session');
    $session->start();

    // Add a status message as part of the session.
    $this->container->get('messenger')->addStatus('Redirected for SSR!');

    $redirect_request = Request::create('/user', 'GET', ['_format' => 'custom_elements']);
    $redirect_request->attributes->set('_route', 'entity.user.canonical');
    $redirect_request->setSession($session);
    $response = $this->container->get('http_kernel')->handle($redirect_request);

    // Check if the response is a redirect.
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(302, $data['redirect']['statusCode']);

    // Assert the message is NOT included in the redirect response.
    $this->assertArrayHasKey('messages', $data, 'Messages key exists in redirect response.');
    $this->assertEmpty($data['messages'], 'No messages in redirect response.');

    // Simulate the next request using the same session.
    $next_request = Request::create($this->nodePath, 'GET', ['_format' => 'custom_elements']);
    $next_request->setSession($session);

    $next_response = $this->container->get('http_kernel')->handle($next_request);
    $next_data = json_decode($next_response->getContent(), TRUE);

    // The message should now be present in the next response.
    $this->assertArrayHasKey('messages', $next_data, 'Messages key exists in next response.');
    $this->assertArrayHasKey('success', $next_data['messages'], 'Success messages are present in next response.');
    $this->assertContains('Redirected for SSR!', $next_data['messages']['success']);
  }

  /**
   * Tests that drupal messages are delivered when cache is enabled.
   */
  public function testDrupalMessagesWithDynamicPageCache() {
    $this->enableDynamicPageCache();

    // Ensure current user is logged in.
    $this->container->get('current_user')->setAccount($this->adminUser);

    // First request: warm up the cache.
    $request1 = Request::create($this->nodePath, 'GET', ['_format' => 'custom_elements']);
    $response1 = $this->container->get('http_kernel')->handle($request1);
    $this->assertEquals(200, $response1->getStatusCode(), 'First request is successful.');

    // Add a status message to the session.
    $this->container->get('messenger')->addStatus('Message after cache!');
    // Second request: should be served from dynamic page cache.
    $request2 = Request::create($this->nodePath, 'GET', ['_format' => 'custom_elements']);
    $response2 = $this->container->get('http_kernel')->handle($request2);
    $data2 = json_decode($response2->getContent(), TRUE);

    // Verify that the response is cached and the message is included.
    $this->assertEquals('HIT', $response2->headers->get('x-drupal-dynamic-cache'), 'Dynamic page cache HIT.');
    $this->assertArrayHasKey('messages', $data2, 'Messages key exists in second response.');
    $this->assertArrayHasKey('success', $data2['messages'], 'Success messages are present in second response.');
  }

}

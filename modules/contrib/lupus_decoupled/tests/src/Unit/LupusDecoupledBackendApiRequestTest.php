<?php

namespace Drupal\Tests\lupus_decoupled\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\lupus_decoupled_ce_api\BackendApiRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test BackendApiRequest.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledBackendApiRequestTest extends UnitTestCase {

  /**
   * Tests if middleware identifies ce api requests correctly.
   *
   * @param string $request_uri
   *   The request uri.
   * @param string $base_path
   *   The installation's base path.
   * @param bool $expected_attribute_value
   *   The expected value of the request's lupus_ce_renderer attribute.
   * @param string $expected_uri
   *   The expected value of the modified request uri.
   *
   * @dataProvider getTestProvider
   */
  public function testBackendApiRequestApplication(string $request_uri, string $base_path, ?bool $expected_attribute_value, string $expected_uri) {
    $new_request = NULL;
    $request = Request::create($request_uri, 'GET', [], [], [], [
      'SCRIPT_NAME' => '/var/www/html/web/index.php',
      'SCRIPT_FILENAME' => '/var/www/html/web/index.php' . $base_path,
    ]);
    $http_kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    $http_kernel->expects($this->once())
      ->method('handle')
      ->willReturnCallback(function ($request) use (&$new_request) {
        $new_request = $request;
        return new Response();
      });
    $backendApiRequestMiddleware = new BackendApiRequest($http_kernel, '/ce-api');
    $backendApiRequestMiddleware->handle($request);

    // Construct taken literally from Request::getUri():
    if (NULL !== $query_string = $new_request->getQueryString()) {
      $query_string = '?' . $query_string;
    }
    $this->assertEquals($expected_uri, $new_request->getPathInfo() . $query_string);
    $this->assertEquals($expected_attribute_value, $new_request->attributes->get('lupus_ce_renderer'));
  }

  /**
   * Data provider for testBackendApiRequestApplication.
   *
   * @return array[]
   *   The data.
   */
  public static function getTestProvider() {
    return [
      'Frontpage CE-API' => ['/ce-api', '', TRUE, '/'],
      'Frontpage CE-API trailing slash' => ['/ce-api/', '', TRUE, '/'],
      'Frontpage' => ['/', '', NULL, '/'],
      'Frontpage base path CE-API' => ['/web/ce-api', '/web', TRUE, '/'],
      'Frontpage base path CE-API trailing slash' => ['/web/ce-api', '/web', TRUE, '/'],
      'Frontpage base path' => ['/web', '/web', NULL, '/'],
      'Node path CE-API' => ['/ce-api/node/1', '', TRUE, '/node/1'],
      'Node path CE-API trailing slash' => ['/ce-api/node/1/', '', TRUE, '/node/1/'],
      'Node path' => ['/node/1', '', NULL, '/node/1'],
      'Node path base path CE-API' => ['/web/ce-api/node/1', '/web', TRUE, '/node/1'],
      'Node path base path CE-API trailing slash' => ['/web/ce-api/node/1/', '/web', TRUE, '/node/1/'],
      'Node path base path' => ['/web/node/1', '/web', NULL, '/node/1'],
      'Web prefix no base path' => ['/web/ce-api/node/1', '', NULL, '/web/ce-api/node/1'],
      'Frontpage prefix no base path' => ['/web/ce-api/', '', NULL, '/web/ce-api/'],
      'Frontpage param CE-API' => ['/ce-api?param=1', '', TRUE, '/?param=1'],
      'Frontpage param CE-API trailing slash' => ['/ce-api/?param=1', '', TRUE, '/?param=1'],
      'Frontpage param base path CE-API' => ['/web/ce-api?param=1', '/web', TRUE, '/?param=1'],
      'Frontpage param base path CE-API trailing slash' => ['/web/ce-api/?param=1', '/web', TRUE, '/?param=1'],
      'node param base path CE-API' => ['/web/ce-api/node/1?param=1', '/web', TRUE, '/node/1?param=1'],
      'node param base path CE-API trailing slash' => ['/web/ce-api/node/1/?param=1', '/web', TRUE, '/node/1/?param=1'],
    ];
  }

}

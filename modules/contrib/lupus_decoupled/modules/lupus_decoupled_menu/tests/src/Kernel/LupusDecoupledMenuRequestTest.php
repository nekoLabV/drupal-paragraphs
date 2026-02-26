<?php

namespace Drupal\Tests\lupus_decoupled_menu\Kernel;

use Drupal\Tests\lupus_ce_renderer\Kernel\LupusCeRendererKernelTestBase;
use Drupal\system\Entity\Menu;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the lupus_decoupled_menu module REST endpoints.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledMenuRequestTest extends LupusCeRendererKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Install additional modules needed for menu testing.
    // Note: lupus_decoupled_menu is NOT included here because we want to
    // test its installation.
    $additional_modules = [
      'serialization',
      'rest',
      'rest_menu_items',
      'menu_link_content',
      'link',
      'path_alias',
      'trusted_redirect',
      'lupus_decoupled_ce_api',
    ];

    $this->enableModules($additional_modules);
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('path_alias');
    $this->installConfig([
      'rest',
      'rest_menu_items',
    ]);

    // Rebuild routes to ensure REST endpoints are available.
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Test that the menu REST endpoint works after module installation.
   */
  public function testMenuRestEndpointWorks() {
    // Install our module, which should configure everything.
    $this->container->get('module_installer')->install(['lupus_decoupled_menu']);

    // Verify the REST resource is properly configured.
    $rest_config = $this->config('rest.resource.rest_menu_item');
    $this->assertTrue($rest_config->get('status'), 'REST resource should be enabled after module install.');
    $this->assertEquals('rest_menu_item', $rest_config->get('id'));

    // Create a test menu if it doesn't exist.
    if (!Menu::load('main')) {
      Menu::create([
        'id' => 'main',
        'label' => 'Main navigation',
      ])->save();
    }

    // Create some menu links.
    MenuLinkContent::create([
      'title' => 'Home',
      'link' => ['uri' => 'internal:/'],
      'menu_name' => 'main',
      'weight' => 0,
    ])->save();

    MenuLinkContent::create([
      'title' => 'About',
      'link' => ['uri' => 'internal:/about'],
      'menu_name' => 'main',
      'weight' => 1,
    ])->save();

    // Make a request to the REST endpoint.
    $request = Request::create('/api/menu_items/main', 'GET');
    $request->headers->set('Accept', 'application/json');

    // Handle the request through the kernel.
    $response = $this->container->get('http_kernel')->handle($request);

    // Check that we get a successful response.
    $this->assertEquals(200, $response->getStatusCode(), 'Menu endpoint returns 200 OK');

    // Check the response content.
    $content = json_decode($response->getContent(), TRUE);
    $this->assertIsArray($content, 'Response is valid JSON array');
    $this->assertCount(2, $content, 'Response contains 2 menu items');

    // Verify the menu items have the expected structure.
    $first_item = $content[0];
    $this->assertEquals('Home', $first_item['title']);
    $this->assertArrayHasKey('relative', $first_item);
    $this->assertArrayHasKey('key', $first_item);
    $this->assertArrayHasKey('uri', $first_item);
    $this->assertArrayHasKey('alias', $first_item);
    $this->assertArrayHasKey('external', $first_item);
    $this->assertArrayHasKey('absolute', $first_item);
    $this->assertArrayHasKey('weight', $first_item);
    $this->assertArrayHasKey('expanded', $first_item);
    $this->assertArrayHasKey('enabled', $first_item);
    $this->assertArrayHasKey('uuid', $first_item);
    $this->assertArrayHasKey('options', $first_item);
  }

  /**
   * Test that anonymous users can access the menu endpoint.
   */
  public function testAnonymousAccessToMenuEndpoint() {
    // Install our module.
    $this->container->get('module_installer')->install(['lupus_decoupled_menu']);

    // Ensure we're anonymous.
    $this->container->get('current_user')->setAccount(User::getAnonymousUser());

    // Create a test menu if it doesn't exist.
    if (!Menu::load('main')) {
      Menu::create([
        'id' => 'main',
        'label' => 'Main navigation',
      ])->save();
    }

    // Make a request to the REST endpoint as anonymous.
    $request = Request::create('/api/menu_items/main', 'GET');
    $request->headers->set('Accept', 'application/json');

    $response = $this->container->get('http_kernel')->handle($request);

    // Anonymous users should be able to access the endpoint.
    $this->assertEquals(200, $response->getStatusCode(), 'Anonymous users can access menu endpoint');
  }

  /**
   * Test that all menus are accessible (allowed_menus is empty).
   */
  public function testAllMenusAreAccessible() {
    // Install our module.
    $this->container->get('module_installer')->install(['lupus_decoupled_menu']);

    // Create multiple test menus.
    $menus = ['main', 'footer', 'sidebar'];
    foreach ($menus as $menu_id) {
      if (!Menu::load($menu_id)) {
        Menu::create([
          'id' => $menu_id,
          'label' => ucfirst($menu_id) . ' menu',
        ])->save();
      }
    }

    // Test that all menus are accessible.
    foreach ($menus as $menu_id) {
      $request = Request::create("/api/menu_items/$menu_id", 'GET');
      $request->headers->set('Accept', 'application/json');

      $response = $this->container->get('http_kernel')->handle($request);
      $this->assertEquals(200, $response->getStatusCode(), "Menu '$menu_id' is accessible");
    }
  }

}

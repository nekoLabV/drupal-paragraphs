<?php

namespace Drupal\Tests\lupus_ce_renderer\Kernel;

use Drupal\Core\PageCache\ChainRequestPolicy;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;
use Drupal\user\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;

/**
 * Base kernel test for lupus_ce_renderer, provides shared setup and helpers.
 */
abstract class LupusCeRendererKernelTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'metatag',
    'token',
    'custom_elements',
    'lupus_ce_renderer',
    'layout_builder',
    'file',
  ];

  /**
   * The node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Path to created node.
   *
   * @var string
   */
  protected string $nodePath;

  /**
   * The admin user to use for testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Install necessary schemas.
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);

    // Install config.
    $this->installConfig(['system', 'field', 'node', 'user']);

    // Create Basic page node type.
    $page = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
      'description' => 'Basic page type for testing',
    ]);
    $page->save();
    node_add_body_field($page);

    // Ensure the 'administrator' role exists and has necessary permissions.
    $admin_permissions = [
      'bypass node access',
      'administer nodes',
      'access content',
    ];
    $role = Role::create([
      'id' => 'administrator',
      'label' => 'Administrator',
    ]);
    $role->save();
    user_role_grant_permissions('administrator', $admin_permissions);

    // Create admin user and assign administrator role.
    $admin_user = User::create([
      'name' => $this->randomMachineName(),
      'status' => 1,
      'roles' => ['administrator'],
    ]);
    $admin_user->enforceIsNew();
    $admin_user->save();
    $this->adminUser = $admin_user;

    // Set the current user to anonymous for access checks and add basic
    // permission setup.
    $this->container->get('current_user')->setAccount(User::getAnonymousUser());
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, ['access content']);
    user_role_grant_permissions(RoleInterface::AUTHENTICATED_ID, ['access content']);

    // Create a test node with the admin user as author.
    $this->node = Node::create([
      'type' => 'page',
      'title' => 'Test node',
      'body' => [
        'value' => 'Test body content',
        'format' => 'plain_text',
      ],
      'status' => 1,
      'uid' => $this->adminUser->id(),
    ]);
    $this->node->save();
    $this->nodePath = '/node/' . $this->node->id();
  }

  /**
   * Helper to issue a Symfony request and return the response.
   */
  protected function request(string $path, array $query = [], string $method = 'GET') {
    $request = Request::create($path, $method, $query);
    // Set the route to match lupus_ce_renderer.
    $request->attributes->set('_route', 'entity.node.canonical');
    $request->attributes->set('node', $this->node);
    return $this->container->get('http_kernel')->handle($request);
  }

  /**
   * Helper to decode JSON response.
   */
  protected function decodeResponse($response) {
    return json_decode($response->getContent(), TRUE);
  }

  /**
   * Helper to enable dynamic_page_cache and override its request policy.
   */
  protected function enableDynamicPageCache() {
    // Enable dynamic_page_cache module.
    \Drupal::service('module_installer')->install(['dynamic_page_cache']);
    // Override the dynamic_page_cache_request_policy service to only use the
    // ChainRequestPolicy, so that the default CommandLineOrUnsafeMethod policy
    // is disabled for this test. Otherwise, the cache is disabled due to the
    // phpunit invocation via the command line.
    $container = \Drupal::getContainer();
    $container->set(
      'dynamic_page_cache_request_policy',
      new ChainRequestPolicy()
    );
  }

}

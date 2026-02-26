<?php

namespace Drupal\Tests\lupus_decoupled_menu\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * Tests the lupus_decoupled_menu module install hook.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledMenuInstallTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'serialization',
    'rest',
    'rest_menu_items',
    'menu_link_content',
    'link',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('menu_link_content');
    $this->installConfig(['system', 'user', 'rest_menu_items']);
  }

  /**
   * Test that the install hook properly configures REST resources.
   */
  public function testInstallHookConfiguresRestResource() {
    // Install the module to trigger the install hook.
    $this->container->get('module_installer')->install(['lupus_decoupled_menu'], FALSE);

    // Check that the REST resource configuration is created.
    $rest_config = $this->config('rest.resource.rest_menu_item');
    $this->assertTrue($rest_config->get('status'), 'REST resource is enabled.');
    $this->assertEquals('rest_menu_item', $rest_config->get('id'));
    $this->assertEquals(['GET'], $rest_config->get('configuration.methods'));
    $this->assertEquals(['json'], $rest_config->get('configuration.formats'));
    $this->assertEquals(['cookie'], $rest_config->get('configuration.authentication'));

    // Check that permissions are granted to anonymous and authenticated users.
    $anonymous_role = Role::load('anonymous');
    $this->assertTrue(
      $anonymous_role->hasPermission('restful get rest_menu_item'),
      'Anonymous users have permission to access REST menu items.'
    );

    $authenticated_role = Role::load('authenticated');
    $this->assertTrue(
      $authenticated_role->hasPermission('restful get rest_menu_item'),
      'Authenticated users have permission to access REST menu items.'
    );
  }

  /**
   * Test that the install hook handles existing REST configuration gracefully.
   */
  public function testInstallHookWithExistingRestConfig() {
    // Pre-create a complete REST resource configuration.
    $this->config('rest.resource.rest_menu_item')
      ->set('status', TRUE)
      ->set('id', 'rest_menu_item')
      ->set('plugin_id', 'rest_menu_item')
      ->set('granularity', 'resource')
      ->set('dependencies.module', ['rest_menu_items', 'serialization', 'user'])
      ->set('configuration.methods', ['GET'])
      ->set('configuration.formats', ['json'])
      ->set('configuration.authentication', ['cookie'])
      ->save();

    // Install the module to trigger the install hook.
    $this->container->get('module_installer')->install(['lupus_decoupled_menu'], FALSE);

    // Check that existing REST resource configuration is preserved.
    $rest_config = $this->config('rest.resource.rest_menu_item');
    $this->assertTrue($rest_config->get('status'), 'Existing REST resource configuration is preserved.');
    $this->assertEquals('rest_menu_item', $rest_config->get('id'));
    $this->assertEquals(['GET'], $rest_config->get('configuration.methods'));
    $this->assertEquals(['json'], $rest_config->get('configuration.formats'));
    $this->assertEquals(['cookie'], $rest_config->get('configuration.authentication'));
  }

}

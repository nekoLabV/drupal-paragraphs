<?php

declare(strict_types=1);

namespace Drupal\Tests\lupus_decoupled_layout_builder\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests lupus_decoupled_layout_builder module installation.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledLayoutBuilderModuleInstallationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'node',
    'menu_link_content',
    'link',
    'rest',
    'serialization',
    // Similar to standard profile, without custom_elements or layout_builder.
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that lupus_decoupled_layout_builder can be installed.
   */
  public function testLupusDecoupledInstallation(): void {
    // Create node types.
    $this->drupalCreateContentType(['type' => 'article']);

    // Step 1: Verify entity_view_display works with standard Drupal.
    $display = EntityViewDisplay::load('node.article.default');
    $this->assertNotNull($display, 'Entity view display exists in standard Drupal');
    $this->assertInstanceOf(
      'Drupal\Core\Entity\Entity\EntityViewDisplay',
      $display,
      'Display should be core EntityViewDisplay before modules are installed'
    );

    // Step 2: Install all required modules including lupus_decoupled.
    $module_installer = \Drupal::service('module_installer');
    $result = $module_installer->install([
      'layout_discovery',
      'layout_builder',
      'custom_elements',
      'lupus_decoupled',
      'lupus_decoupled_layout_builder',
    ]);
    $this->assertTrue($result, 'All modules installed successfully');

    // Clear caches and reload storage to get fresh instances.
    drupal_flush_all_caches();
    \Drupal::entityTypeManager()->getStorage('entity_view_display')->resetCache();

    // Load the display again to verify it still works and uses the right class.
    $display2 = EntityViewDisplay::load('node.article.default');
    $this->assertNotNull($display2, 'Entity view display still exists after lupus_decoupled');
    $this->assertInstanceOf(
      'Drupal\lupus_decoupled_layout_builder\CustomElementsLayoutBuilderPreviewEntityViewDisplay',
      $display2,
      'Display should be CustomElementsLayoutBuilderPreviewEntityViewDisplay after lupus_decoupled'
    );
  }

  /**
   * Tests installing layout_builder first, then custom_elements and lupus.
   */
  public function testLayoutBuilderFirstThenLupusDecoupled(): void {
    // Create node types.
    $this->drupalCreateContentType(['type' => 'page']);

    // Step 1: Install layout_builder first.
    $module_installer = \Drupal::service('module_installer');
    $result = $module_installer->install(['layout_discovery', 'layout_builder']);
    $this->assertTrue($result, 'Layout Builder installed successfully');
    $display = EntityViewDisplay::load('node.page.default');
    $this->assertNotNull($display, 'Entity view display exists after layout_builder');

    // Step 2: Install custom_elements and lupus_decoupled together.
    $result = $module_installer->install([
      'custom_elements',
      'lupus_decoupled',
      'lupus_decoupled_layout_builder',
    ]);
    $this->assertTrue($result, 'Custom elements and lupus modules installed successfully');

    // Clear caches and reload storage to get fresh instances.
    drupal_flush_all_caches();
    \Drupal::entityTypeManager()->getStorage('entity_view_display')->resetCache();

    // Verify it now uses the lupus preview display class.
    $display2 = EntityViewDisplay::load('node.page.default');
    $this->assertNotNull($display2, 'Entity view display still exists');
    $this->assertInstanceOf(
      'Drupal\lupus_decoupled_layout_builder\CustomElementsLayoutBuilderPreviewEntityViewDisplay',
      $display2,
      'Display should be CustomElementsLayoutBuilderPreviewEntityViewDisplay'
    );
  }

}

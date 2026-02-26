<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_elements\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests Layout Builder module installation after Custom Elements.
 *
 * Ensures issue #3504944 remains fixed.
 *
 * @group custom_elements
 */
class LayoutBuilderModuleInstallationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'node',
    'layout_discovery',
    // Only custom_elements is enabled initially, not layout_builder.
    'custom_elements',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that layout_builder can be installed after custom_elements.
   */
  public function testLayoutBuilderInstallationAfterCustomElements(): void {
    // Create node types.
    $this->drupalCreateContentType(['type' => 'article']);
    $this->drupalCreateContentType(['type' => 'page']);

    // Step 1: Verify entity_view_display works with just custom_elements.
    // Load the display that was created automatically.
    $display = EntityViewDisplay::load('node.article.default');
    $this->assertNotNull($display, 'Entity view display exists with custom_elements enabled');
    $this->assertInstanceOf(
      'Drupal\custom_elements\CustomElementsEntityViewDisplay',
      $display,
      'Display should be instance of CustomElementsEntityViewDisplay'
    );

    // Step 2: Install layout_builder and verify entity_view_display works.
    $module_installer = \Drupal::service('module_installer');
    $result = $module_installer->install(['layout_builder']);
    $msg = 'Layout Builder installed successfully after custom_elements';
    $this->assertTrue($result, $msg);

    // Load existing display to verify it works after layout_builder install.
    $display2 = EntityViewDisplay::load('node.page.default');
    $msg = 'Entity view display can be created after installing layout_builder';
    $this->assertNotNull($display2->id(), $msg);
    $this->assertInstanceOf(
      'Drupal\custom_elements\CustomElementsLayoutBuilderEntityViewDisplay',
      $display2,
      'Display should use CustomElementsLayoutBuilderEntityViewDisplay'
    );
  }

}

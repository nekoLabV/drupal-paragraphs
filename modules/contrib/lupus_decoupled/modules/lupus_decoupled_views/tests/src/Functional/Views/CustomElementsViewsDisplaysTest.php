<?php

declare(strict_types=1);

namespace Drupal\Tests\lupus_decoupled_views\Functional\Views;

use Drupal\custom_elements\CustomElement;
use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Views;
use Drupal\Core\Render\RenderContext;
use Drupal\views\Plugin\Block\ViewsBlock;

/**
 * Tests the custom elements views display plugins.
 *
 * @group lupus_decoupled_views
 */
class CustomElementsViewsDisplaysTest extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'node',
    'views',
    'file',
    'menu_link_content',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'lupus_decoupled_views',
    'lupus_decoupled_views_test',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static array $testViews = ['custom_elements_view_test'];

  /**
   * Nodes to use during this test.
   *
   * @var array
   */
  protected $nodes = [];

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['lupus_decoupled_views_test']): void {
    parent::setUp($import_test_views, $modules);
    $this->renderer = $this->container->get('renderer');
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    // Create test nodes with unique titles and creation dates (for sorting).
    $node = [
      'title' => 'Test node 1',
      'status' => TRUE,
      'published' => TRUE,
      'type' => 'page',
      'created' => \Drupal::time()->getRequestTime(),
    ];
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test node 2';
    $node['created']--;
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test node 3';
    $node['created']--;
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test article 1';
    $node['created']--;
    $node['type'] = 'article';
    $this->nodes[] = $this->drupalCreateNode($node);
  }

  /**
   * Test custom elements page view.
   */
  public function testCustomElementsPageView(): void {
    // Set config to use preview by default - API responses should still use
    // 'markup' variant to ensure custom elements markup is generated.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('default_render_variant', 'preview:markup')
      ->save();

    // Test json response.
    $json_response = json_decode($this->drupalGet('ce-api/view/test-ce', ['query' => ['_content_format' => 'json']]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('CE view page', $json_response['title'] ?? []);
    $this->assertSame('drupal-view-custom-elements-view-test-page', $json_response['content']['element'] ?? []);
    $this->assertCount(4, $json_response['content']['slots']['rows'] ?? []);
    $this->assertStringContainsString('Test node 1', $json_response['content']['slots']['rows'][0]['props']['title'] ?? '');
    $this->assertStringContainsString('Test node 2', $json_response['content']['slots']['rows'][1]['props']['title'] ?? '');
    $this->assertStringContainsString('Test node 3', $json_response['content']['slots']['rows'][2]['props']['title'] ?? '');
    $this->assertStringContainsString('Test article', $json_response['content']['slots']['rows'][3]['props']['title'] ?? '');
    $this->assertSame('custom_elements_page_1', $json_response['content']['props']['displayId'] ?? []);
    $this->assertSame('custom_elements_view_test', $json_response['content']['props']['viewId'] ?? []);
    // Verify rows_wrapper attribute is empty for default 'drupal-markup'.
    $this->assertSame('', $json_response['content']['props']['rowsWrapper'] ?? 'not-set');

    // Test custom element name override.
    $view = Views::getView('custom_elements_view_test');
    $view->setDisplay('custom_elements_page_1');
    $view->initDisplay();
    $view->display_handler->options['custom_element_name'] = 'my-custom-view';
    $view->execute();
    $output = $view->render();
    $custom_element = $output['#custom_element'];
    $this->assertEquals('my-custom-view', $custom_element->getTag(),
      'Custom element name should be used when configured');

    // Test markup response.
    $markup_response = json_decode($this->drupalGet('ce-api/view/test-ce', ['query' => ['_content_format' => 'markup']]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('CE view page', $markup_response['title'] ?? '');
    $this->assertStringContainsString('</drupal-view-custom-elements-view-test-page>', $markup_response['content'] ?? '');
    $this->assertStringContainsString('Test node 3', $markup_response['content'] ?? '');
    $this->assertStringContainsString('Test node 2', $markup_response['content'] ?? '');
    $this->assertStringContainsString('Test node 1', $markup_response['content'] ?? '');
    $this->assertStringContainsString('display-id="custom_elements_page_1"', $markup_response['content'] ?? '');
    $this->assertStringContainsString('view-id="custom_elements_view_test"', $markup_response['content'] ?? '');
    // Verify API responses use 'markup' variant despite preview config:
    // Custom element markup should be present (not preview containers).
    $this->assertStringNotContainsString('custom-elements-preview', $markup_response['content'] ?? '');
  }

  /**
   * Tests that CE page display renders correctly with empty results.
   */
  public function testCustomElementsPageViewWithEmptyResults(): void {
    $view = Views::getView('custom_elements_view_test');
    $view->setDisplay('custom_elements_page_1');
    $view->initDisplay();

    // Add a filter that will exclude all nodes.
    $view->display_handler->setOption('filters', [
      'title' => [
        'id' => 'title',
        'table' => 'node_field_data',
        'field' => 'title',
        'relationship' => 'none',
        'operator' => '=',
        'value' => 'non-existing-value',
        'plugin_id' => 'string',
      ],
    ]);
    $view->save();

    // Test JSON response with empty results.
    $json_response = json_decode($this->drupalGet('ce-api/view/test-ce', ['query' => ['_content_format' => 'json']]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('CE view page', $json_response['title'] ?? '');
    $this->assertSame('drupal-view-custom-elements-view-test-page', $json_response['content']['element'] ?? '');
    $this->assertCount(0, $json_response['content']['slots']['rows'] ?? [], 'JSON response should have empty rows');

    // Test markup response with empty results.
    $markup_response = json_decode($this->drupalGet('ce-api/view/test-ce', ['query' => ['_content_format' => 'markup']]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertStringContainsString('</drupal-view-custom-elements-view-test-page>', $markup_response['content'] ?? '');
  }

  /**
   * Tests the custom elements block display plugin directly.
   */
  public function testCustomElementsBlockPlugin(): void {
    // Get the block plugin manager.
    $block_manager = $this->container->get('plugin.manager.block');

    // Check if the test view exists and has the expected display.
    $view = Views::getView('custom_elements_view_test');
    $this->assertNotNull($view, 'Test view "custom_elements_view_test" should be loaded');

    // Check available displays.
    $view->setDisplay('custom_elements_block_1');
    $display = $view->getDisplay();
    $this->assertEquals('custom_elements_block', $display->getPluginId(), 'View should have the custom_elements_block_1 display');

    // Create the block plugin instance directly with the correct ID.
    $block_plugin_id = 'views_block:custom_elements_view_test-custom_elements_block_1';
    $block_plugin = $block_manager->createInstance($block_plugin_id, [
      'label' => 'Custom Elements Test Block',
      'provider' => 'views',
      'label_display' => 'visible',
      'views_label' => 'Custom Block Title',
      'items_per_page' => 3,
    ]);

    $this->assertInstanceOf(ViewsBlock::class, $block_plugin);

    // Build the block content.
    $renderer = $this->renderer;
    $build = $renderer->executeInRenderContext(new RenderContext(), function () use ($block_plugin) {
      return $block_plugin->build();
    });

    // Check the basic render array structure with the custom element.
    $this->assertNotEmpty($build);
    $this->assertIsArray($build);
    $this->assertArrayHasKey('#custom_element', $build);

    // Verify the custom element's attributes.
    $element = $build['#custom_element'];
    assert($element instanceof CustomElement);
    $this->assertEquals('drupal-view-custom-elements-view-test-block', $element->getTag());

    $this->assertEquals('Custom Block Title', $element->getAttribute('title'));
    $this->assertEquals('custom_elements_view_test', $element->getAttribute('view-id'));
    $this->assertEquals('custom_elements_block_1', $element->getAttribute('display-id'));
    // Verify rows_wrapper attribute is empty for default 'drupal-markup'.
    $this->assertEquals('', $element->getAttribute('rows-wrapper'));

    // Test with custom wrapper configured.
    $view_custom = Views::getView('custom_elements_view_test');
    $view_custom->setDisplay('custom_elements_block_1');
    $view_custom->initDisplay();
    $view_custom->initStyle();
    $view_custom->style_plugin->options['rows_wrapper'] = 'HumanifyGrid';
    $view_custom->execute();
    $output_custom = $view_custom->render();
    $element_custom = $output_custom['#custom_element'];
    $this->assertEquals('HumanifyGrid', $element_custom->getAttribute('rows-wrapper'),
      'Custom rows wrapper should be set as attribute');

    // Test custom element name override.
    $view_custom_name = Views::getView('custom_elements_view_test');
    $view_custom_name->setDisplay('custom_elements_block_1');
    $view_custom_name->initDisplay();
    $view_custom_name->display_handler->options['custom_element_name'] = 'my-block-view';
    $view_custom_name->execute();
    $output_custom_name = $view_custom_name->render();
    $element_custom_name = $output_custom_name['#custom_element'];
    $this->assertEquals('my-block-view', $element_custom_name->getTag(),
      'Custom element name should be used when configured');

    // Verify the slot content.
    $slot_data = $element->getSortedSlotsByName();
    $this->assertIsArray($slot_data);
    $this->assertArrayHasKey('rows', $slot_data);

    $this->assertEquals('Test node 1', $slot_data['rows'][0]['content']->getAttribute('title'));
    $this->assertEquals('Test node 2', $slot_data['rows'][1]['content']->getAttribute('title'));
    $this->assertEquals('Test node 3', $slot_data['rows'][2]['content']->getAttribute('title'));

    // Check once again on the rendered output of the block.
    $this->assertEquals('custom_element', $build['#theme']);
    $renderer = $this->renderer;
    $output = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($renderer, $build) {
      return $renderer->render($build);
    });
    $this->assertStringContainsString('drupal-view-custom-elements-view-test', (string) $output);
    $this->assertStringContainsString('Custom Block Title', (string) $output);
    $this->assertStringContainsString('Test node 1', (string) $output);
    $this->assertStringContainsString('Test node 2', (string) $output);
    $this->assertStringContainsString('Test node 3', (string) $output);
    $this->assertStringNotContainsString('Test article 1', (string) $output);

    // Verify the cacheability metadata.
    $this->assertContains('node:' . $this->nodes[0]->id(), $element->getCacheTags());
    $this->assertContains('node:' . $this->nodes[1]->id(), $element->getCacheTags());
    $this->assertContains('node:' . $this->nodes[2]->id(), $element->getCacheTags());
    $this->assertNotContains('node:' . $this->nodes[3]->id(), $element->getCacheTags());

    // Verify the view's configuration title is taken when no the block does
    // not override it.
    $block_plugin = $block_manager->createInstance($block_plugin_id, [
      'provider' => 'views',
      'items_per_page' => 3,
    ]);
    $element = $this->renderBlockPluginToCustomElement($block_plugin);
    $this->assertEquals('CE view page', $element->getAttribute('title'));

    // Verify "display title"/label_display option is respected.
    $block_plugin = $block_manager->createInstance($block_plugin_id, [
      'provider' => 'views',
      'label_display' => FALSE,
      'items_per_page' => 3,
    ]);
    $element = $this->renderBlockPluginToCustomElement($block_plugin);
    $this->assertEquals('', $element->getAttribute('title'));

    // Test pager data.
    $pager = $element->getAttribute('pager');
    $this->assertIsArray($pager);
    $this->assertEquals(2, $pager['total_pages']);
    $this->assertEquals(0, $pager['current']);

    // Test block with page=1.
    $block_plugin = $block_manager->createInstance($block_plugin_id, [
      'provider' => 'views',
      'items_per_page' => 3,
    ]);
    $block_plugin->getViewExecutable()->setCurrentPage(1);
    $element = $this->renderBlockPluginToCustomElement($block_plugin);
    $pager = $element->getAttribute('pager');
    $this->assertIsArray($pager);
    $this->assertEquals(2, $pager['total_pages']);
    $this->assertEquals(1, $pager['current']);
    $this->assertNotContains('node:' . $this->nodes[0]->id(), $element->getCacheTags());
    $this->assertNotContains('node:' . $this->nodes[1]->id(), $element->getCacheTags());
    $this->assertNotContains('node:' . $this->nodes[2]->id(), $element->getCacheTags());
    $this->assertContains('node:' . $this->nodes[3]->id(), $element->getCacheTags());
  }

  /**
   * Tests that CE block display renders correctly with empty results.
   */
  public function testCustomElementsBlockPluginWithEmptyResults(): void {
    $view = Views::getView('custom_elements_view_test');
    $view->setDisplay('custom_elements_block_1');
    $view->initDisplay();

    // Add a filter that will exclude all nodes.
    $view->display_handler->setOption('filters', [
      'title' => [
        'id' => 'title',
        'table' => 'node_field_data',
        'field' => 'title',
        'relationship' => 'none',
        'operator' => '=',
        'value' => 'non-existing-value',
        'plugin_id' => 'string',
      ],
    ]);
    $view->save();

    $block_manager = $this->container->get('plugin.manager.block');
    $block_plugin_id = 'views_block:custom_elements_view_test-custom_elements_block_1';

    $block_plugin = $block_manager->createInstance($block_plugin_id, [
      'label' => 'Custom Elements Test Block',
      'provider' => 'views',
      'label_display' => 'visible',
      'views_label' => 'Custom Block Title',
      'items_per_page' => 3,
    ]);

    $this->assertInstanceOf(ViewsBlock::class, $block_plugin);

    $renderer = $this->renderer;
    $build = $renderer->executeInRenderContext(new RenderContext(), function () use ($block_plugin) {
      return $block_plugin->build();
    });

    $this->assertNotEmpty($build);
    $this->assertIsArray($build);
    $this->assertArrayHasKey('#custom_element', $build, 'Block should have a custom element even with no results');

    $element = $build['#custom_element'];
    assert($element instanceof CustomElement);
    $this->assertEquals('drupal-view-custom-elements-view-test-block', $element->getTag());
    $this->assertEquals('Custom Block Title', $element->getAttribute('title'));
    $this->assertEquals('custom_elements_view_test', $element->getAttribute('view-id'));
    $this->assertEquals('custom_elements_block_1', $element->getAttribute('display-id'));

    $slot_data = $element->getSortedSlotsByName();
    $this->assertIsArray($slot_data);
    $this->assertArrayHasKey('rows', $slot_data);
    $this->assertCount(0, $slot_data['rows'], 'Rows should be empty when view has no results');

    $this->assertEquals('custom_element', $build['#theme']);
    $output = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($renderer, $build) {
      return $renderer->render($build);
    });
    $this->assertStringContainsString('drupal-view-custom-elements-view-test', (string) $output);
    $this->assertStringContainsString('Custom Block Title', (string) $output);
  }

  /**
   * Tests custom wrapper is rendered in regular page displays.
   *
   * Verifies that when a regular (non-CE) page display uses the Custom
   * Elements style with a custom wrapper configured, the wrapper element
   * is rendered directly in the output (not unwrapped).
   */
  public function testCustomWrapperInRegularPageDisplay(): void {
    // Use the pre-configured regular page display.
    $view = Views::getView('custom_elements_view_test');
    $view->setDisplay('page_1');
    $view->initDisplay();
    $view->initStyle();

    // Configure custom wrapper.
    $view->style_plugin->options['rows_wrapper'] = 'custom-grid';

    // Execute and render.
    $view->execute();
    $output = $view->render();

    // Render to HTML.
    $rendered = $this->renderer->renderRoot($output);
    $rendered_html = (string) $rendered;

    // Verify the custom-grid wrapper element is in the rendered output.
    $this->assertStringContainsString('<custom-grid', $rendered_html,
      'Custom wrapper element should be rendered in regular page display');
    $this->assertStringContainsString('</custom-grid>', $rendered_html,
      'Custom wrapper element should have closing tag');

    // Verify nodes are rendered inside the wrapper.
    $this->assertStringContainsString('Test node 1', $rendered_html);
  }

  /**
   * Tests validation error when CE display used without CE style.
   */
  public function testValidationForMissingCustomElementsStyle(): void {
    // Load the view and change style to non-CE.
    $view = Views::getView('custom_elements_view_test');
    $view->setDisplay('custom_elements_page_1');
    $view->display_handler->setOption('style', [
      'type' => 'default',
    ]);
    $errors = $view->display_handler->validate();

    // Verify validation error is present.
    $this->assertNotEmpty($errors, 'Validation errors should be present');
    $found_error = FALSE;
    foreach ($errors as $error) {
      if (strpos((string) $error, 'Custom Elements display') !== FALSE) {
        $found_error = TRUE;
        $this->assertStringContainsString('"Custom Elements" style', (string) $error);
        break;
      }
    }
    $this->assertTrue($found_error, 'Validation error about missing CE style should be present');
  }

  /**
   * Renders the given block plugin to a custom element.
   *
   * @param \Drupal\views\Plugin\Block\ViewsBlock $block_plugin
   *   The block to render.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The generated element.
   */
  private function renderBlockPluginToCustomElement(ViewsBlock $block_plugin): CustomElement {
    $context = new RenderContext();
    $build = $this->renderer->executeInRenderContext($context, function () use ($block_plugin) {
      return $block_plugin->build();
    });
    $this->assertArrayHasKey('#custom_element', $build);
    return $build['#custom_element'];
  }

}

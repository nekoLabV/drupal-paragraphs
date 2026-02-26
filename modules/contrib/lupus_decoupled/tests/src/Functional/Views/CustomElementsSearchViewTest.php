<?php

declare(strict_types=1);

namespace Drupal\Tests\lupus_decoupled\Functional\Views;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\Utility\Utility;
use Drupal\Tests\search_api\Functional\ExampleContentTrait;
use Drupal\Tests\views\Functional\ViewTestBase;

/**
 * Testing custom elements search view.
 */
class CustomElementsSearchViewTest extends ViewTestBase {

  use ExampleContentTrait;

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
    'lupus_decoupled_search_view_test',
    'block',
    'search_api',
    'search_api_db',
    'search_api_exclude',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The ID of the search index used for this test.
   *
   * @var string
   */
  protected string $indexId = 'content';

  /**
   * Nodes to use during this test.
   *
   * @var array
   */
  protected array $nodes = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = FALSE, $modules = ['lupus_decoupled_views_test']): void {
    parent::setUp(FALSE, $modules);
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'third_party_settings' => [
        'search_api_exclude' => [
          'enabled' => TRUE,
        ],
      ],
    ]);
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
      'third_party_settings' => [
        'search_api_exclude' => [
          'enabled' => TRUE,
        ],
      ],
    ]);
    $node = [
      'title' => 'Test node 1',
      'status' => TRUE,
      'published' => TRUE,
      'type' => 'page',
      'sae_exclude' => FALSE,
    ];
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test node 2';
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test node 3';
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test article 1';
    $node['type'] = 'article';
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test node excluded';
    $node['sae_exclude'] = TRUE;
    $this->nodes[] = $this->drupalCreateNode($node);
    $node['title'] = 'Test node unpublished';
    $node['published'] = FALSE;
    $node['status'] = FALSE;
    $node['sae_exclude'] = FALSE;
    $this->nodes[] = $this->drupalCreateNode($node);
    // Trigger indexing.
    \Drupal::getContainer()
      ->get('search_api.index_task_manager')
      ->addItemsAll(Index::load($this->indexId));
    $this->indexItems($this->indexId);

    // Do not use a batch for tracking the initial items after creating an
    // index when running the tests via the GUI. Otherwise, it seems Drupal's
    // Batch API gets confused and the test fails.
    if (!Utility::isRunningInCli()) {
      \Drupal::state()->set('search_api_use_tracking_batch', FALSE);
    }

    $this->rebuildContainer();
  }

  /**
   * Test search view.
   */
  public function testCustomElementsSearchPageView(): void {
    $json_response = json_decode($this->drupalGet('ce-api/ce-search', [
      'query' => [
        '_content_format' => 'json',
        'text' => 'node',
      ],
    ]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('Search', $json_response['title'] ?? []);
    $this->assertSame('drupal-view-search-page', $json_response['content']['element'] ?? []);
    $this->assertCount(3, $json_response['content']['slots']['rows'] ?? []);
    $this->assertStringContainsString('Test node', $json_response['content']['slots']['rows'][0]['props']['title'] ?? '');
    $this->assertSame('custom_elements_page_1', $json_response['content']['props']['displayId'] ?? []);
    $this->assertSame('search', $json_response['content']['props']['viewId'] ?? []);
    // Unpublished and excluded node should not be in the search results.
    $titles = array_column(array_map(fn($row) => $row['props'] ?? [], $json_response['content']['slots']['rows'] ?? []), 'title');
    $this->assertEmpty(array_intersect($titles, ['Test node excluded', 'Test node unpublished']));
    $json_response = json_decode($this->drupalGet('ce-api/ce-search', [
      'query' => [
        '_content_format' => 'json',
        'text' => 'article',
      ],
    ]), TRUE);
    $this->assertCount(1, $json_response['content']['slots']['rows'] ?? []);
  }

}

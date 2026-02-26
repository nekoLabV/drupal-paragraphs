<?php

declare(strict_types=1);

namespace Drupal\Tests\lupus_decoupled_views\Functional\Views;

use Drupal\Tests\block_content\Functional\BlockContentTestBase;
use Drupal\block_content\BlockContentInterface;
use Drupal\block_content\Entity\BlockContent;

/**
 * Testing custom elements page view.
 */
class CustomElementsBlockTest extends BlockContentTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'node',
    'block',
    'block_content',
    'field',
    'menu_link_content',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
    'lupus_decoupled_block',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $node = [
      'title' => 'Test node 1',
      'status' => TRUE,
      'published' => TRUE,
      'type' => 'page',
    ];
    $this->drupalCreateNode($node);
  }

  /**
   * Create test block with lorem ipsum body.
   *
   * @return \Drupal\block_content\BlockContentInterface
   *   The block.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTestBlock(): BlockContentInterface {
    $test_block_content = [
      'type' => 'basic',
      'status' => 1,
      'info' => 'Testing content blocks',
      'langcode' => 'en',
      'body' => [
        'value' => '<p>Molestias aut provident assumenda cumque et velit. Asperiores dolorem qui voluptas. Quibusdam voluptate est odio. Aut quidem adipisci amet et quia et sit qui.</p><p>Excepturi id explicabo nihil labore. Sint aliquam error quo consequatur quis illo similique dolores. Qui quibusdam sed ad ea quae nobis laboriosam minus. Id enim veritatis voluptatem rerum itaque aut in mollitia.</p>',
        'summary' => '',
        'format' => 'plain_text',
      ],
    ];
    $block_content = BlockContent::create($test_block_content);
    $block_content->save();
    return $block_content;
  }

  /**
   * Test custom elements page response contains blocks property.
   */
  public function testCustomElementsPageView(): void {
    $test_block_content = $this->createTestBlock();
    $this->placeBlock('block_content:' . $test_block_content->uuid(), [
      'id' => 'test_block_id',
      'region' => 'content',
      'label' => 'Test block content',
    ]);

    // Test JSON response - blocks should be returned as normalized JSON data.
    $json_response = json_decode($this->drupalGet('ce-api/node/1', ['query' => ['_content_format' => 'json']]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSame('Test node 1', $json_response['title'] ?? []);
    $this->assertArrayHasKey('blocks', $json_response);
    $this->assertArrayHasKey('test_block_id', $json_response['blocks']['content'] ?? []);

    $block = $json_response['blocks']['content']['test_block_id'];
    // In JSON format, block should be normalized custom element data.
    $this->assertIsArray($block, 'Block should be an array in JSON format');
    $this->assertArrayHasKey('element', $block, 'Block should have element key in JSON format');
    $this->assertEquals('drupal-markup', $block['element'] ?? '');
    // Content should be in slots, not as rendered markup.
    $this->assertArrayHasKey('slots', $block);

    // Test markup response - blocks should be rendered HTML strings.
    $markup_response = json_decode($this->drupalGet('ce-api/node/1', ['query' => ['_content_format' => 'markup']]), TRUE);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertArrayHasKey('blocks', $markup_response);
    $this->assertArrayHasKey('test_block_id', $markup_response['blocks']['content'] ?? []);

    $block_markup = $markup_response['blocks']['content']['test_block_id'];
    // In markup format, block should be a rendered HTML string.
    $this->assertIsString($block_markup, 'Block should be a string in markup format');
    $this->assertStringContainsString('Molestias aut provident', $block_markup);
    $this->assertStringContainsString('<drupal-markup>', $block_markup);
  }

}

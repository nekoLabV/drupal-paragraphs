<?php

namespace Drupal\Tests\custom_elements\Kernel;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\custom_elements\CustomElement;
use Drupal\Tests\custom_elements\Traits\TestHelperTrait;

/**
 * Tests custom elements output generation directly from CustomElement classes.
 *
 * This is the base test that other 'render tests' can rely on. It contains
 * - some assertions on standalone CustomElement objects, though not as
 *   separate test methods. Since CustomElement is just a value object, this is
 *   not the most interesting part. (These could be extracted into a separate
 *   unit test file, if needed.)
 * - tests for rendering CustomElement structures into all supported output
 *   formats.
 *
 * JSON output needs CustomElementNormalizer, so this tests the combination of
 * CustomElement + CustomElementNormalizer classes.
 *
 * Not all details of CustomElementNormalizer have test coverage yet.
 *
 * @group custom_elements
 */
class CustomElementBaseRenderTest extends KernelTestBase {

  use TestHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['custom_elements'];

  /**
   * The main custom element instance under test.
   *
   * @var \Drupal\custom_elements\CustomElement
   */
  protected CustomElement $customElement;

  /**
   * The custom elements normalizer service.
   *
   * @var \Drupal\custom_elements\CustomElementNormalizer
   */
  protected $normalizer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['custom_elements']);

    // These tests expect legacy format.
    \Drupal::configFactory()
      ->getEditable('custom_elements.settings')
      ->set('json_format', 'legacy')
      ->save();

    $this->customElement = CustomElement::create('teaser-listing');
    $this->normalizer = $this->container->get('custom_elements.normalizer');
  }

  /* -------------------------------------------------------------------------
   *  Test Helpers
   * ---------------------------------------------------------------------- */

  /**
   * Renders a custom element to HTML string.
   */
  protected function renderCustomElementToString(CustomElement $element): string {
    $render_array = $element->toRenderArray();
    return (string) $this->container->get(RendererInterface::class)
      ->renderInIsolation($render_array);
  }

  /**
   * Asserts two HTML strings are equivalent after normalization.
   */
  protected function assertHtmlEquals(string $expected, string $actual, string $message = ''): void {
    $this->assertSame(
      $this->normalizeHtmlWhitespace($expected),
      $this->normalizeHtmlWhitespace($actual),
      $message ?: 'Rendered HTML should match expected output'
    );
  }

  /**
   * Normalizes HTML whitespace for consistent comparisons.
   */
  protected function normalizeHtmlWhitespace(string $html): string {
    $html = preg_replace("/ *\n */m", "", $html);
    return preg_replace("/> +</", "><", $html);
  }

  /**
   * Tests rendering for both Vue versions.
   */
  protected function runVueVersionTests(CustomElement $element, array $expected): void {
    foreach (['vue-2' => FALSE, 'vue-3' => TRUE] as $version => $isVue3) {
      $this->config('custom_elements.settings')->set('markup_style', $version)->save();

      $output = $this->renderCustomElementToString($element);
      $expectedHtml = $isVue3 ? $expected['vue3'] : $expected['vue2'];

      $this->assertHtmlEquals(
        $expectedHtml,
        $output,
        "HTML output mismatch for $version rendering"
      );
    }
  }

  /* -------------------------------------------------------------------------
   *  Test Cases
   * ---------------------------------------------------------------------- */

  /**
   * Tests basic element creation with attributes only.
   */
  public function testBasicElementWithAttributesWithoutChildrenOrSlots(): void {
    // Configure element with attributes.
    $this->customElement
      ->setAttribute('title', 'Latest news')
      ->setAttribute('tags', ['news', 'breaking']);

    // Verify element configuration.
    $this->assertEquals(
      'teaser-listing',
      $this->customElement->getTag(),
      'Element tag name should match constructor value'
    );
    $this->assertEquals(
      'Latest news',
      $this->customElement->getAttribute('title'),
      'Title attribute should be stored correctly'
    );
    $this->assertEquals(
      ['news', 'breaking'],
      $this->customElement->getAttribute('tags'),
      'Array attributes should be stored correctly'
    );
    $this->assertEmpty(
      $this->customElement->getSlots(),
      'New element should not have any slots'
    );

    // Test Vue version rendering differences.
    $this->runVueVersionTests($this->customElement, [
      'vue2' => '<teaser-listing title="Latest news" tags="[&quot;news&quot;,&quot;breaking&quot;]"></teaser-listing>',
      'vue3' => '<teaser-listing title="Latest news" :tags="[&quot;news&quot;,&quot;breaking&quot;]"></teaser-listing>',
    ]);

    // Verify JSON normalization.
    $normalized = $this->normalizer->normalize($this->customElement);
    $this->assertEquals(
      [
        'element' => 'teaser-listing',
        'title' => 'Latest news',
        'tags' => ['news', 'breaking'],
      ],
      $normalized,
      'Normalized JSON should match expected structure'
    );
  }

  /**
   * Tests element with a single custom element child in a slot.
   */
  public function testSingleSlotWithCustomElementChild(): void {
    $child = CustomElement::create('article-teaser')
      ->setAttribute('href', 'https://example.com/news/1')
      ->setAttribute('excerpt', 'Breaking news');

    // setSlot() without $index argument explicity marks the slot as having a
    // single value. addSlot() or setSlot() with an index do not do this,
    // meaning the slot stays an array value.
    $random_index = 5;
    $element1 = CustomElement::create('teaser-listing')
      ->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->setSlot('teasers', $child);
    $element2 = CustomElement::create('teaser-listing')
      ->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->setSlot('teasers', $child, 'div', [], $random_index);
    $element3 = CustomElement::create('teaser-listing')
      ->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->addSlot('teasers', $child);

    // Verify slot configuration.
    foreach ([$element1, $element2, $element3] as $index => $element) {
      // Quickly hardcoded: only $element2 has index 5 populated.
      $slot_index = $index == 1 ? $random_index : 0;
      $slots = $element->getSlots();
      $this->assertArrayHasKey(
        'teasers',
        $slots,
        'Element should have slot with specified name'
      );
      $this->assertCount(
        1,
        $slots['teasers'],
        'Slot should contain exactly one entry'
      );
      $this->assertInstanceOf(
        CustomElement::class,
        $slots['teasers'][$slot_index]['content'],
        'Slot content should be a CustomElement instance'
      );
      $this->assertEquals(
        'Breaking news',
        $slots['teasers'][$slot_index]['content']->getAttribute('excerpt'),
        'Child element should maintain configured attributes'
      );
    }

    // Markup output does not differ for 'single value' slots.
    $expected = [
      'vue2' => '<teaser-listing title="Latest news" icon="news">
    <article-teaser href="https://example.com/news/1" excerpt="Breaking news" slot="teasers"></article-teaser>
  </teaser-listing>',
      'vue3' => '<teaser-listing title="Latest news" icon="news">
    <template #teasers>
      <article-teaser href="https://example.com/news/1" excerpt="Breaking news" slot="teasers"></article-teaser>
    </template>
  </teaser-listing>',
    ];
    $this->runVueVersionTests($element1, $expected);
    $this->runVueVersionTests($element2, $expected);
    $this->runVueVersionTests($element3, $expected);

    // JSON output differs for 'single value' slots.
    $singleValueTeaser = [
      'element' => 'teaser-listing',
      'title' => 'Latest news',
      'icon' => 'news',
      'teasers' => [
        'element' => 'article-teaser',
        'href' => 'https://example.com/news/1',
        'excerpt' => 'Breaking news',
      ],
    ];
    $arrayTeaser = [
      'element' => 'teaser-listing',
      'title' => 'Latest news',
      'icon' => 'news',
      'teasers' => [
        [
          'element' => 'article-teaser',
          'href' => 'https://example.com/news/1',
          'excerpt' => 'Breaking news',
        ],
      ],
    ];
    $this->assertEquals($singleValueTeaser, $this->normalizer->normalize($element1), 'Normalized JSON should preserve nested element structure');
    $this->assertEquals($arrayTeaser, $this->normalizer->normalize($element2), 'Normalized JSON should preserve nested element structure');
    $this->assertEquals($arrayTeaser, $this->normalizer->normalize($element3), 'Normalized JSON should preserve nested element structure');
  }

  /**
   * Tests element with raw HTML content in a slot.
   */
  public function testSlotWithHtmlContent(): void {
    $markup = '<p>Introduction content</p>';

    // Test addSlot() vs setSlot() with similar setup as above.
    $random_index = 5;
    $element1 = CustomElement::create('teaser-listing')
      ->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->setSlot('introduction', $markup);
    $element2 = CustomElement::create('teaser-listing')
      ->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->setSlot('introduction', $markup, 'div', [], $random_index);
    $element3 = CustomElement::create('teaser-listing')
      ->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->addSlot('introduction', $markup);

    // Verify slot configuration.
    foreach ([$element1, $element2, $element3] as $index => $element) {
      // Quickly hardcoded: only $element2 has index 5 populated.
      $slot_index = $index == 1 ? $random_index : 0;
      $slots = $element->getSlots();
      $this->assertArrayHasKey(
        'introduction',
        $slots,
        'Element should have slot for HTML content'
      );
      $this->assertCount(
        1,
        $slots['introduction'],
        'HTML slot should contain single entry'
      );
      $this->assertEquals(
        $markup,
        (string) $slots['introduction'][$slot_index]['content'],
        'Slot content should preserve raw HTML'
      );
    }

    // Markup output does not differ for 'single value' slots.
    $expected = [
      'vue2' => '<teaser-listing title="Latest news" icon="news">'
      . '<div slot="introduction"><p>Introduction content</p></div>'
      . '</teaser-listing>',
      'vue3' => '<teaser-listing title="Latest news" icon="news">'
      . '<template #introduction><p>Introduction content</p></template>'
      . '</teaser-listing>',
    ];
    $this->runVueVersionTests($element1, $expected);
    $this->runVueVersionTests($element2, $expected);
    $this->runVueVersionTests($element3, $expected);

    // JSON output differs for 'single value' slots.
    $singleValueIntro = [
      'element' => 'teaser-listing',
      'title' => 'Latest news',
      'icon' => 'news',
      'introduction' => $markup,
    ];
    $arrayIntro = [
      'element' => 'teaser-listing',
      'title' => 'Latest news',
      'icon' => 'news',
      'introduction' => [$markup],
    ];
    $this->assertEquals($singleValueIntro, $this->normalizer->normalize($element1), 'Normalized JSON should include raw HTML content');
    $this->assertEquals($arrayIntro, $this->normalizer->normalize($element2), 'Normalized JSON should include raw HTML content');
    $this->assertEquals($arrayIntro, $this->normalizer->normalize($element3), 'Normalized JSON should include raw HTML content');
  }

  /**
   * Tests slot operations with different method call orders.
   */
  public function testSlotOperationsWithMultipleChildren(): void {
    // Create test elements.
    $child1 = CustomElement::create('test-element')->setAttribute('id', '1');
    $child2 = CustomElement::create('test-element')->setAttribute('id', '2');
    $child3 = CustomElement::create('test-element')->setAttribute('id', '3');

    $element1 = CustomElement::create('test-container');
    $element1->addSlot('main', $child1)
      ->addSlot('main', $child2)
      ->addSlot('main', $child3);
    // Test setSlot() without $index, combined with other addSlot() / setSlot()
    // calls; see behavior below.
    $element2 = CustomElement::create('test-container');
    $element2->setSlot('main', $child1)
      ->addSlot('main', $child2)
      ->addSlot('main', $child3);

    // Verify slot content counts.
    $this->assertCount(3, $element1->getSlots()['main'], 'setSlot sequence should have 3 elements');
    $this->assertCount(3, $element2->getSlots()['main'], 'addSlot sequence should have 3 elements');

    // Markup output does not differ for 'single value' slots.
    $expected = [
      'vue2' => '<test-container>
  <test-element id="1" slot="main"></test-element>
  <test-element id="2" slot="main"></test-element>
  <test-element id="3" slot="main"></test-element>
</test-container>',
      'vue3' => '<test-container>
  <template #main>
    <test-element id="1" slot="main"></test-element>
    <test-element id="2" slot="main"></test-element>
    <test-element id="3" slot="main"></test-element>
  </template>
</test-container>',
    ];
    $this->runVueVersionTests($element1, $expected);

    // JSON output contains only the 'single value' slot; all slots added (with
    // the same slot name) after the setSlot() are ignored.
    $singleSlotValue = [
      'element' => 'test-container',
      'main' => ['element' => 'test-element', 'id' => '1'],
    ];
    $allSlotValues = [
      'element' => 'test-container',
      'main' => [
        ['element' => 'test-element', 'id' => '1'],
        ['element' => 'test-element', 'id' => '2'],
        ['element' => 'test-element', 'id' => '3'],
      ],
    ];
    $this->assertEquals($allSlotValues, $this->normalizer->normalize($element1), 'JSON mismatch');
    $this->assertEquals($singleSlotValue, $this->normalizer->normalize($element2), 'JSON mismatch with expected single-value slot output');
  }

  /**
   * Tests complex element with multiple slots containing various children.
   */
  public function testMultipleSlotsWithVariousChildren(): void {
    // Create test elements.
    $teasers = [
      CustomElement::create('article-teaser')
        ->setAttribute('href', 'https://example.com/news/1')
        ->setAttribute('excerpt', 'Top story'),
      CustomElement::create('article-teaser')
        ->setAttribute('href', 'https://example.com/news/2')
        ->setAttribute('excerpt', 'Secondary story'),
    ];

    $footer = CustomElement::create('content-footer')
      ->setAttribute('text', 'More news');

    // Configure parent element with multiple slots.
    $this->customElement->setAttribute('title', 'Latest news')
      ->setAttribute('icon', 'news')
      ->addSlot('teasers', $teasers[0])
      ->addSlot('teasers', $teasers[1])
      ->setSlot('footer', $footer);

    // Verify slot configuration.
    $slots = $this->customElement->getSlots();
    $this->assertCount(
      2,
      $slots['teasers'],
      'Main content slot should contain all teasers'
    );
    $this->assertCount(
      1,
      $slots['footer'],
      'Footer slot should contain single element'
    );

    // Test Vue version rendering differences.
    $this->runVueVersionTests($this->customElement, [
      'vue2' => '<teaser-listing title="Latest news" icon="news">
    <article-teaser href="https://example.com/news/1" excerpt="Top story" slot="teasers"></article-teaser>
    <article-teaser href="https://example.com/news/2" excerpt="Secondary story" slot="teasers"></article-teaser>
    <content-footer text="More news" slot="footer"></content-footer>
  </teaser-listing>',
      'vue3' => '<teaser-listing title="Latest news" icon="news">
    <template #teasers>
      <article-teaser href="https://example.com/news/1" excerpt="Top story" slot="teasers"></article-teaser>
      <article-teaser href="https://example.com/news/2" excerpt="Secondary story" slot="teasers"></article-teaser>
    </template>
    <template #footer>
      <content-footer text="More news" slot="footer"></content-footer>
    </template>
  </teaser-listing>',
    ]);

    // Verify JSON normalization.
    $normalized = $this->normalizer->normalize($this->customElement);
    $this->assertEquals(
      [
        'element' => 'teaser-listing',
        'title' => 'Latest news',
        'icon' => 'news',
        'teasers' => [
          ['element' => 'article-teaser', 'href' => 'https://example.com/news/1', 'excerpt' => 'Top story'],
          ['element' => 'article-teaser', 'href' => 'https://example.com/news/2', 'excerpt' => 'Secondary story'],
        ],
        'footer' => ['element' => 'content-footer', 'text' => 'More news'],
      ],
      $normalized,
      'Normalized JSON should maintain separate slot structures'
    );
  }

  /**
   * Tests the toArray() helper method.
   */
  public function testToArrayHelper() {
    // Create a custom element with slots and attributes.
    $element = CustomElement::create('test-component');
    $element->setAttribute('id', 'test-123');
    $element->setAttribute('data_role', 'main');
    $element->setSlot('header', 'Header Content');
    $element->setSlot('body', 'Body Content');

    // Test default behavior (camelCase conversion).
    $array = $element->toArray();
    $this->assertIsArray($array);
    $this->assertEquals('test-component', $array['element']);
    $this->assertEquals('test-123', $array['id']);
    // Check that underscore is converted to camelCase.
    $this->assertEquals('main', $array['dataRole']);
    $this->assertArrayHasKey('header', $array);
    $this->assertArrayHasKey('body', $array);

    // Test with preserve_keys = TRUE.
    $array_preserved = $element->toArray(TRUE);
    $this->assertIsArray($array_preserved);
    $this->assertEquals('test-component', $array_preserved['element']);
    // Check that underscore was converted to dash in attribute name
    // (this happens during setAttribute).
    $this->assertEquals('main', $array_preserved['data-role']);

    // Test with cache metadata collection.
    $cache_metadata = new BubbleableMetadata();
    $element->addCacheTags(['custom_element:test']);
    $array_with_cache = $element->toArray(FALSE, $cache_metadata);
    $this->assertIsArray($array_with_cache);
    // Verify cache metadata was collected.
    $this->assertContains('custom_element:test', $cache_metadata->getCacheTags());
  }

  /**
   * Tests the toMarkup() helper method.
   */
  public function testToMarkupHelper() {
    // Create a custom element with content.
    $element = CustomElement::create('my-card');
    $element->setAttribute('class', 'featured');
    $element->setSlot('title', 'Test Title');
    $element->setSlot('content', 'Test content goes here');

    // Get the markup using the helper.
    $markup = $element->toMarkup();

    // Verify it returns a MarkupInterface object.
    $this->assertInstanceOf('\Drupal\Component\Render\MarkupInterface', $markup);

    // Test the actual markup output.
    $expected = '<my-card class="featured">
      <div slot="title">Test Title</div>
      <div slot="content">Test content goes here</div>
    </my-card>';
    $this->assertMarkupEquals($expected, (string) $markup, 'Basic element markup matches expected structure');

    // Test with nested custom elements.
    $parent = CustomElement::create('parent-element');
    $child = CustomElement::create('child-element');
    $child->setSlot('default', 'Child content');
    $parent->setSlotFromCustomElement('slot-name', $child);

    $parent_markup = $parent->toMarkup();

    // Verify nested structure.
    // Note: default slot content is rendered directly without wrapper.
    $expected_nested = '<parent-element>
      <child-element slot="slot-name">
        Child content
      </child-element>
    </parent-element>';
    $this->assertMarkupEquals($expected_nested, (string) $parent_markup, 'Nested element markup matches expected structure');
  }

}

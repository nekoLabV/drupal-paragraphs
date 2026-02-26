<?php

namespace Drupal\Tests\custom_elements\Functional;

use Drupal\custom_elements\CustomElement;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Test rendering custom elements into Vue3 markup.
 *
 * @group custom_elements
 */
class CustomElementsRenderMarkupVue3Test extends CustomElementsRenderMarkupTest {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $config = $this->config('custom_elements.settings');
    $config->set('markup_style', 'vue-3');
    $config->save();
  }

  /**
   * Test nested elements rendering.
   */
  public function testNestedElementsRendering() {
    $listing_element = CustomElement::create('test-list');
    $paragraphs[] = Paragraph::create([
      'title' => 'First Paragraph',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some example text for first paragraph',
      ],
    ]);
    $paragraphs[] = Paragraph::create([
      'title' => 'Second Paragraph',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some another example text',
      ],
    ]);
    $nested_elements = [];
    foreach ($paragraphs as $key => $paragraph) {
      $nested_elements[$key] = $this->getCustomElementGenerator()->generate($paragraph, 'full');
    }
    $listing_element->setSlotFromNestedElements('paragraphs', $nested_elements);
    $markup = $this->renderCustomElement($listing_element);

    $expected_markup = <<<EOF
<test-list>
  <template #paragraphs>
    <paragraph-text><p>Some example text for first paragraph</p></paragraph-text>
    <paragraph-text><p>Some another example text</p></paragraph-text>
  </template>
</test-list>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);
  }

  /**
   * @covers \Drupal\custom_elements\Processor\DefaultContentEntityProcessor
   */
  public function testNodeRendering() {
    // Test rendering with new vue-3 style.
    // Unlike in the parent method, the node does not (yet?) get a media field
    // value.
    $paragraph = Paragraph::create([
      'title' => 'Title',
      'type' => 'text',
      'field_text' => [
        'value' => 'Some example text',
      ],
    ]);
    $this->node->field_paragraphs = [
      $paragraph,
    ];

    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<node-article created="{$this->node->created->value}" title="test" uid="0">
  <template #paragraphs>
    <paragraph-text>
      <p>Some example text</p>
    </paragraph-text>
  </template>
</node-article>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);

    // Test behavior with auto-processing enabled.
    $this->ceDisplayStorage->load('node.article.default')
      ->setForceAutoProcessing(TRUE)
      ->save();

    $custom_element = $this->getCustomElementGenerator()
      ->generate($this->node, 'full');
    $markup = $this->renderCustomElement($custom_element);
    $expected_markup = <<<EOF
<node-article uid="0" title="test" created="{$this->node->created->value}">
  <template #paragraphs>
    <paragraph-text>
      <p>Some example text</p>
    </paragraph-text>
  </template>
</node-article>
EOF;
    $this->assertMarkupEquals($expected_markup, $markup);
  }

}

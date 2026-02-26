<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_elements\Traits;

use Drupal\custom_elements\CustomElement;

/**
 * Helps with testing custom elements.
 */
trait TestHelperTrait {

  /**
   * Renders a custom element.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   A custom element.
   *
   * @return string
   *   Rendered markup.
   */
  protected function renderCustomElement(CustomElement $element) {
    $build = $element->toRenderArray();
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');
    return (string) $renderer->renderRoot($build);
  }

  /**
   * Asserts that two markup strings are equal.
   */
  protected function assertMarkupEquals($expected, $actual, $message = '') {
    $expected = trim($expected);
    $actual = trim($actual);

    // Remove newlines and their surrounding spaces.
    $expected = preg_replace('/\s*\n\s*/', '', $expected);
    $actual = preg_replace('/\s*\n\s*/', '', $actual);

    // Remove spaces between tags.
    $expected = preg_replace('/>\s+</', '><', $expected);
    $actual = preg_replace('/>\s+</', '><', $actual);

    // Normalize remaining whitespace to single spaces.
    $expected = preg_replace('/\s+/', ' ', $expected);
    $actual = preg_replace('/\s+/', ' ', $actual);

    $this->assertEquals($expected, $actual, $message);
  }

}

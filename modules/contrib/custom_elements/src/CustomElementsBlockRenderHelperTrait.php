<?php

namespace Drupal\custom_elements;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;

/**
 * Helper trait for CE elements content rendering.
 */
trait CustomElementsBlockRenderHelperTrait {

  /**
   * Converts a single block render array to a custom element.
   *
   * @param array $block_build
   *   The block render array.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The custom element. Returns empty renderless-container for empty blocks,
   *   which preserves cache metadata.
   */
  public function convertBlockRenderArray(array $block_build): CustomElement {
    // Handle empty cache-only entries.
    if (isset($block_build['#cache']) && count($block_build) == 1) {
      $empty = CustomElement::create('renderless-container');
      $empty->addCacheableDependency(BubbleableMetadata::createFromRenderArray($block_build));
      return $empty;
    }

    // Check if content has a custom element.
    if (isset($block_build['content']['#custom_element'])) {
      $block_element = $block_build['content']['#custom_element'];
    }
    else {
      // Wrap block in drupal-markup.
      $block_element = CustomElement::create('drupal-markup');
      $block_element->setSlotFromRenderArray('default', $block_build);
    }

    // Add cache metadata if present.
    if (isset($block_build['#cache'])) {
      $block_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($block_build));
    }

    return $block_element;
  }

  /**
   * Converts the render array of multiple blocks into custom elements.
   *
   * @param array $build
   *   The render array.
   * @param \Drupal\custom_elements\CustomElement $parent_element
   *   The parent custom element to which content will be added.
   *
   * @return \Drupal\custom_elements\CustomElement[]
   *   The list of generated custom elements.
   */
  public function getElementsFromBlockContentRenderArray(array $build, CustomElement $parent_element) {
    $elements = [];

    foreach (Element::children($build, TRUE) as $key) {
      $block_element = $this->convertBlockRenderArray($build[$key]);
      $elements[] = $block_element;
    }
    return $elements;
  }

}

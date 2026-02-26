<?php

namespace Drupal\custom_elements\RenderConverter;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsBlockRenderHelperTrait;

/**
 * Service for converting Canvas render arrays to custom elements.
 */
class CanvasRenderConverter {

  use CustomElementsBlockRenderHelperTrait;

  /**
   * SDC component IDs that should be rendered as custom elements.
   *
   * By default, SDCs are rendered via twig. This associative array maps
   * component IDs to TRUE for SDCs that should be converted to custom
   * elements instead.
   *
   * @var array<string, bool>
   */
  protected $sdcCustomElementComponents = [];

  /**
   * Converts a Canvas field render array to a custom elements tree.
   *
   * @param array $render_array
   *   The render array from a Canvas field.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The generated custom element. Returns an empty renderless-container
   *   if nothing to render, which can carry cache metadata.
   */
  public function convertRenderArray(array $render_array): CustomElement {
    // Create empty renderless-container to hold cache metadata if needed.
    $empty_container = CustomElement::create('renderless-container');

    // Check access first if present.
    if (isset($render_array['#access']) && $render_array['#access'] instanceof AccessResultInterface) {
      $empty_container->addCacheableDependency($render_array['#access']);
      if (!$render_array['#access']->isAllowed()) {
        return $empty_container;
      }
    }

    // Check if a CustomElement is already provided (e.g., from canvas_extjs).
    if (isset($render_array['#custom_element']) && $render_array['#custom_element'] instanceof CustomElement) {
      $custom_element = $render_array['#custom_element'];

      // Process slots from render array if present.
      // This handles slots for API output where #pre_render hasn't run yet.
      if (!empty($render_array['#slots'])) {
        foreach ($render_array['#slots'] as $slot_name => $slot_content) {
          if (!is_array($slot_content)) {
            continue;
          }
          // Recursively convert slot content to CustomElement.
          $slot_element = $this->convertRenderArray($slot_content);
          $custom_element->setSlotFromCustomElement($slot_name, $slot_element);
        }
      }
      return $custom_element;
    }

    // Process the render array based on its type.
    switch ($render_array['#type'] ?? '') {
      case 'component':
        return $this->convertComponent($render_array);

      case 'component_container':
        return $this->convertComponentContainer($render_array);

      default:
        if ($this->isBlockRenderArray($render_array)) {
          return $this->convertBlockRenderArray($render_array);
        }

        // Process render arrays with children (drilling down).
        return $this->processGeneralRenderArray($render_array);
    }
  }

  /**
   * Normalizes a component ID to "module:component" format.
   *
   * @param string $component_id
   *   The component ID to normalize.
   *
   * @return string
   *   The normalized component ID in "module:component" format.
   */
  private function normalizeComponentId(string $component_id): string {
    // Transform 'sdc.module.component' -> 'module:component'.
    if (str_starts_with($component_id, 'sdc.')) {
      return preg_replace('/^sdc\.([^.]+)\.(.+)$/', '$1:$2', $component_id);
    }
    return $component_id;
  }

  /**
   * Sets which SDC components should be rendered as custom elements.
   *
   * This is useful for overriding SDCs regular twig-based rendering with a
   * custom elements based rendering approach. Can be called multiple times
   * to add or remove components.
   *
   * @param string[] $component_ids
   *   Array of SDC component IDs to configure.
   * @param bool $enable
   *   TRUE to render as custom element, FALSE to render via twig (default).
   */
  public function setSdcCustomElementComponents(array $component_ids, bool $enable = TRUE): void {
    foreach ($component_ids as $component_id) {
      // Normalize to "module:component" format for internal storage.
      $normalized_id = $this->normalizeComponentId($component_id);

      if ($enable) {
        $this->sdcCustomElementComponents[$normalized_id] = TRUE;
      }
      else {
        unset($this->sdcCustomElementComponents[$normalized_id]);
      }
    }
  }

  /**
   * Determines if an SDC component should be rendered as a custom element.
   *
   * @param string $component_id
   *   The component ID to check.
   *
   * @return bool
   *   TRUE if the component should be rendered as a custom element, FALSE if
   *   it should be rendered via twig.
   */
  protected function shouldRenderSdcAsCustomElement(string $component_id): bool {
    // Normalize component ID to "module:component" format.
    $normalized_id = $this->normalizeComponentId($component_id);

    // Non-SDC components (those without ':') always render as custom elements.
    if (!str_contains($normalized_id, ':')) {
      return TRUE;
    }

    // SDC components render via twig by default, unless explicitly configured.
    return isset($this->sdcCustomElementComponents[$normalized_id]);
  }

  /**
   * Converts a component render array to custom element.
   *
   * @param array $render_array
   *   The component render array.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The custom element.
   */
  protected function convertComponent(array $render_array): CustomElement {
    $component_id = $render_array['#component'] ?? ($render_array['#component_id'] ?? '');

    // Check if this SDC should be rendered via twig or as custom element.
    if (!$this->shouldRenderSdcAsCustomElement($component_id)) {
      return $this->renderSdcComponentAsTwig($render_array);
    }

    // Get the custom element tag for this component.
    $tag = $this->getCustomElementTag($render_array);
    $element = CustomElement::create($tag);

    // Add component props as attributes.
    if (isset($render_array['#props']) && is_array($render_array['#props'])) {
      foreach ($render_array['#props'] as $key => $value) {
        // Skip Canvas internal props (still use xb_ prefix for compatibility).
        if (str_starts_with($key, 'xb_')) {
          continue;
        }
        // Pass through all props, including non-scalar ones.
        $element->setAttribute($key, $value);
      }
    }

    // Process slots. Each slot has its own render array that we process.
    if (isset($render_array['#slots']) && is_array($render_array['#slots'])) {
      foreach ($render_array['#slots'] as $slot_name => $slot_content) {
        if (is_array($slot_content)) {
          $slot_element = $this->convertRenderArray($slot_content);
          $element->setSlotFromCustomElement($slot_name, $slot_element);
        }
      }
    }
    return $element;
  }

  /**
   * Converts a component container render array to custom element.
   *
   * @param array $render_array
   *   The component container render array.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The custom element. Returns empty renderless-container if no component.
   */
  protected function convertComponentContainer(array $render_array): CustomElement {
    // If there's a #component key, this is actually a component wrapped in a
    // container.
    if (isset($render_array['#component']) && is_array($render_array['#component'])) {
      // Delegate to the main conversion method which handles all types.
      return $this->convertRenderArray($render_array['#component']);
    }

    // If there's no #component key, return empty renderless-container.
    $empty = CustomElement::create('renderless-container');
    // Preserve cache metadata if present.
    if (isset($render_array['#cache'])) {
      $empty->addCacheableDependency(BubbleableMetadata::createFromRenderArray($render_array));
    }
    return $empty;
  }

  /**
   * Renders an SDC component using twig.
   *
   * @param array $render_array
   *   The component render array.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The custom element wrapping the twig-rendered content.
   */
  protected function renderSdcComponentAsTwig(array $render_array): CustomElement {
    // Create a drupal-markup element to wrap the rendered content.
    $markup_element = CustomElement::create('drupal-markup');

    // Use setSlotFromRenderArray which handles rendering internally.
    // This renders the SDC component via Drupal's render system and
    // sets it as the default slot content.
    // For now, we keep the #slots untouched and let them render as is.
    $markup_element->setSlotFromRenderArray('default', $render_array);

    // Cache metadata is preserved automatically by setSlotFromRenderArray.
    return $markup_element;
  }

  /**
   * Gets the custom element tag name for a component render array.
   *
   * @param array $render_array
   *   The component render array.
   *
   * @return string
   *   The custom element tag name.
   */
  protected function getCustomElementTag(array $render_array): string {
    $tag = $render_array['#component'] ?? ($render_array['#component_id'] ?? '');
    // Convert to valid custom element tag format.
    $tag = str_replace([':', '.', '_'], '-', $tag);
    return strtolower($tag);
  }

  /**
   * Checks if a render array represents a block.
   *
   * @param array $render_array
   *   The render array to check.
   *
   * @return bool
   *   TRUE if this is a block render array.
   */
  protected function isBlockRenderArray(array $render_array): bool {
    $theme = $render_array['#theme'] ?? '';
    return $theme === 'block' || strpos($theme, 'block_') === 0;
  }

  /**
   * Processes a general render array while handling children.
   *
   * @param array $render_array
   *   The render array to process.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The converted custom element.
   */
  protected function processGeneralRenderArray(array $render_array): CustomElement {
    $children = Element::children($render_array);

    // When there is a special type for which we have no mapping, fallback to
    // rendering it and wrapping the result in drupal-markup.
    if (isset($render_array['#type']) || isset($render_array['#theme']) || empty($children)) {
      return CustomElement::createFromRenderArray($render_array);
    }

    // Single child with no other properties - simply drill down.
    if (count($children) === 1) {
      $first_child = reset($children);

      if (isset($render_array[$first_child]) && is_array($render_array[$first_child])) {
        $child_element = $this->convertRenderArray($render_array[$first_child]);
        // Add cache metadata if present.
        if (isset($render_array['#cache'])) {
          $child_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($render_array));
        }
        return $child_element;
      }
    }

    // For multiple children use renderless-container.
    $parent_element = CustomElement::create('renderless-container');

    // Add cache metadata if present.
    if (isset($render_array['#cache'])) {
      $parent_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($render_array));
    }

    $elements = [];
    foreach ($children as $child_key) {
      if (isset($render_array[$child_key]) && is_array($render_array[$child_key])) {
        $child_element = $this->convertRenderArray($render_array[$child_key]);
        $elements[] = $child_element;
      }
    }

    // If we have elements, add them to the renderless-container.
    if ($elements) {
      $parent_element->setSlotFromNestedElements('default', $elements);
    }

    return $parent_element;
  }

}

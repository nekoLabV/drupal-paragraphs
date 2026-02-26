<?php

namespace Drupal\custom_elements\Plugin\CustomElementsPreviewProvider;

use drunomics\ServiceUtils\Core\Render\RendererTrait;
use Drupal\Component\Utility\Html;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderBase;

/**
 * Provides a preview using a Nuxt frontend.
 *
 * @CustomElementsPreviewProvider(
 *   id = "nuxt",
 *   label = @Translation("JavaScript - Nuxt"),
 *   description = @Translation("Generates a preview using the Nuxt component-preview module")
 * )
 */
class NuxtPreviewProvider extends CustomElementsPreviewProviderBase {

  use RendererTrait;

  /**
   * {@inheritdoc}
   */
  public function preview(CustomElement $element): array {
    // The base URL should always be set when this method is called,
    // as isApplicable() checks for it.
    assert(!empty($this->baseUrl), 'Base URL must be set when preview() is called');

    // Generate a unique element ID for this preview container.
    $element_id = Html::getUniqueId('nuxt-preview-' . $element->getTag());

    $build = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => $element_id,
        'class' => ['nuxt-preview-container'],
        'data-component-name' => $element->getPrefixedTag(),
        'data-component-props' => json_encode($element->getAttributes()),
      ],
    ];

    // Add slots, keyed with slot name.
    foreach ($element->getSortedSlots() as $slot_entry) {
      $slot_key = 'slot_' . $slot_entry['key'];
      // Add wrapping div for processing slots in JavaScript. Until processed
      // we make them visually hidden.
      if (!isset($build[$slot_key])) {
        $build[$slot_key] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['visually-hidden'],
            'data-slot' => $slot_entry['key'],
          ],
          'content' => [],
        ];
      }
      // Append content to the slot container.
      if ($slot_entry['content'] instanceof CustomElement) {
        $result = $this->preview($slot_entry['content']);
        $build[$slot_key]['content'][] = $result;
      }
      else {
        $build[$slot_key]['content'][] = [
          '#type' => 'markup',
          '#markup' => $slot_entry['content'],
        ];
      }
    }

    // Add attachments after cache metadata to prevent overwriting.
    $build['#attached']['library'][] = 'custom_elements/nuxt_preview';
    $build['#attached']['drupalSettings']['customElementsNuxtPreview']['baseUrl'] = $this->baseUrl;
    return $build;
  }

}

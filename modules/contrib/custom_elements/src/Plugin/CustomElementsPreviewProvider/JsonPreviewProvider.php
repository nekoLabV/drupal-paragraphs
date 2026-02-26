<?php

namespace Drupal\custom_elements\Plugin\CustomElementsPreviewProvider;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a preview by displaying the custom element as JSON.
 *
 * This provider shows the custom element data structure in JSON format,
 * useful for debugging and API development.
 *
 * @CustomElementsPreviewProvider(
 *   id = "json",
 *   label = @Translation("JSON structure"),
 *   description = @Translation("Displays the custom element data as JSON")
 * )
 */
class JsonPreviewProvider extends CustomElementsPreviewProviderBase {

  /**
   * {@inheritdoc}
   */
  public function preview(CustomElement $element): array {
    // Convert custom element to JSON-ready array.
    // Uses the normalizer's configured json_format setting.
    $data = $element->toJson();

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['custom-elements-preview', 'custom-elements-preview--json'],
        'style' => 'border: 1px solid #ddd; padding: 1rem; background: #f9f9f9; overflow: auto;',
      ],
      'content' => [
        '#prefix' => '<pre>',
        '#plain_text' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        '#suffix' => '</pre>',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(Request $request): bool {
    // This provider is always applicable.
    return TRUE;
  }

}

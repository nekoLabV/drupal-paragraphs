<?php

namespace Drupal\custom_elements\Plugin\CustomElementsPreviewProvider;

use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a preview by displaying the custom element markup directly.
 *
 * This provider works without any external dependencies and shows
 * the raw custom element HTML markup.
 *
 * @CustomElementsPreviewProvider(
 *   id = "markup",
 *   label = @Translation("Markup code"),
 *   description = @Translation("Displays the raw custom element markup")
 * )
 */
class MarkupPreviewProvider extends CustomElementsPreviewProviderBase {

  /**
   * {@inheritdoc}
   */
  public function preview(CustomElement $element): array {

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['custom-elements-preview', 'custom-elements-preview--markup'],
        'style' => 'border: 1px solid #ddd; padding: 1rem; background: #f9f9f9; overflow: auto;',
      ],
      'content' => [
        '#prefix' => '<code style="display: block; white-space: pre;">',
        '#plain_text' => $element->toMarkup(),
        '#suffix' => '</code>',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(Request $request): bool {
    // This provider is always applicable as a fallback.
    return TRUE;
  }

}

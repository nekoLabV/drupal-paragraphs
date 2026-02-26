<?php

namespace Drupal\custom_elements\PreviewProvider;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\custom_elements\CustomElement;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for Custom Elements Preview Provider plugins.
 *
 * Previews are rendering the custom element in Drupal-UI context, e.g. in the
 * administrative interface. This could be a simple markup preview, or a full
 * client-side rendered preview using a JavaScript framework.
 *
 * For each request, the appropriate preview provider is determined by the
 * CustomElementsPreviewResolver service, which selects among available
 * preview-provider services. Services can be configured from selected plugins
 * with specific configurations (base-URL).
 *
 * ## Plugin vs Services Architecture
 *
 * - **Plugins**: Defined via the @CustomElementsPreviewProvider annotation.
 *   Used for discovering available preview-variants, that site builders can
 *   select from. Modules would take selected plugins and create configured
 *   service instances.
 *
 * - **Services**: Tagged with 'custom_elements.preview_provider'.
 *   Collected by the resolver to determine the appropriate provider per
 *   request.
 *
 * @see \Drupal\custom_elements\CustomElementsPreviewProviderManager
 * @see \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver
 */
interface CustomElementsPreviewProviderInterface extends PluginInspectionInterface {

  /**
   * Generates a preview render array for a custom element.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element to preview.
   *
   * @return array
   *   A render array for the preview.
   */
  public function preview(CustomElement $element): array;

  /**
   * Sets the base URL for the preview provider.
   *
   * @param string $baseUrl
   *   The base URL to use for generating preview URLs. Both absolute and
   *   relative URLs are supported.
   */
  public function setBaseUrl(string $baseUrl): void;

  /**
   * Determines if this provider is applicable for the given request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return bool
   *   TRUE if this provider should handle the preview, FALSE otherwise.
   */
  public function isApplicable(Request $request): bool;

}

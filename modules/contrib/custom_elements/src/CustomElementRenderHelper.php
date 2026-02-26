<?php

namespace Drupal\custom_elements;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper service for custom element rendering operations.
 *
 * Provides utilities for determining appropriate render variants based on
 * request context, integrating with lupus_ce_renderer when available, and
 * managing render array conversions.
 */
class CustomElementRenderHelper {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs a CustomElementRenderHelper.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the default render variant for the current request.
   *
   * Determines the appropriate render variant based on request context:
   * 1. If this is an API response: Returns 'markup' to ensure custom elements
   *    markup is always generated when rendering for API consumers. JSON
   *    output is obtained differently, via CustomElement::toJson().
   * 2. Otherwise: Returns the configured default from custom_elements.settings.
   *    The caller must add suiting cache metadata as needed.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   (optional) The request. If not provided, uses current request from
   *   stack.
   *
   * @return string
   *   The render variant. One of:
   *   - 'markup': Custom Elements Markup rendering
   *   - 'preview': Auto-select preview provider
   *   - 'preview:<provider>': Specific preview provider, e.g. 'preview:nuxt'.
   */
  public function getDefaultRenderVariant(?Request $request = NULL): string {
    if (!isset($request)) {
      $request = $this->requestStack->getCurrentRequest();
    }

    // Check if this is an API response (lupus_ce_renderer context).
    // This matches the logic in ApiResponseTrait::isApiResponse().
    $is_api_response = $request
      && ($request->attributes->get('lupus_ce_renderer')
        || $request->getRequestFormat() === 'custom_elements');

    if ($is_api_response) {
      // For API responses, always use 'markup' to ensure custom elements
      // markup is generated for markup-format API responses.
      return 'markup';
    }

    // Use configured default for non-API contexts.
    $config = $this->configFactory->get('custom_elements.settings');
    return $config->get('default_render_variant') ?? 'markup';
  }

}

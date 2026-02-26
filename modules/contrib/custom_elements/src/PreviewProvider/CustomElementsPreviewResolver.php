<?php

namespace Drupal\custom_elements\PreviewProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for resolving which preview provider to use for a request.
 *
 * This resolver collects all tagged preview provider services and determines
 * which one should be used for the current request based on their
 * isApplicable() method.
 *
 * ## Service Collection
 *
 * Provider services are collected via the 'custom_elements.preview_provider'
 * tag and ordered by priority. The first applicable provider is used.
 *
 * @see \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface
 * @see \Drupal\custom_elements\CustomElementsPreviewProviderManager
 */
class CustomElementsPreviewResolver {

  /**
   * Request attribute key for caching the provider.
   */
  const REQUEST_ATTRIBUTE = '_custom_elements_preview_provider';

  /**
   * The preview providers.
   *
   * @var iterable
   */
  protected iterable $providers;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Constructs a CustomElementsPreviewResolver.
   *
   * @param iterable $providers
   *   The tagged preview provider services.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(iterable $providers, RequestStack $request_stack) {
    $this->providers = $providers;
    $this->requestStack = $request_stack;
  }

  /**
   * Gets the appropriate preview provider for the current request.
   *
   * Always returns a provider since the MarkupPreviewProvider is configured
   * as a fallback that is always applicable.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   (optional) The request. If not provided, the current request from the
   *   request stack will be used.
   *
   * @return \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface
   *   The preview provider. Always returns a provider since
   *   MarkupPreviewProvider acts as a fallback.
   */
  public function getProvider(?Request $request = NULL): CustomElementsPreviewProviderInterface {
    if (!isset($request)) {
      $request = $this->requestStack->getCurrentRequest();
    }
    // Check if already cached in request attributes.
    if ($request->attributes->has(self::REQUEST_ATTRIBUTE)) {
      $cached = $request->attributes->get(self::REQUEST_ATTRIBUTE);
      if ($cached !== NULL) {
        return $cached;
      }
    }

    $selectedProvider = NULL;
    foreach ($this->providers as $provider) {
      if ($provider && $provider->isApplicable($request)) {
        $selectedProvider = $provider;
        break;
      }
    }
    if (!$selectedProvider) {
      throw new \LogicException('No preview provider available. The markup provider should always be available as a fallback.');
    }

    // Cache in request attributes.
    $request->attributes->set(self::REQUEST_ATTRIBUTE, $selectedProvider);
    return $selectedProvider;
  }

  /**
   * Gets all available preview providers.
   *
   * @return \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface[]
   *   Array of preview providers, keyed by provider ID.
   */
  public function getProviders(): array {
    $providers = [];
    foreach ($this->providers as $provider) {
      if ($provider) {
        $providers[$provider->getPluginId()] = $provider;
      }
    }
    return $providers;
  }

  /**
   * Gets a specific preview provider by ID.
   *
   * @param string $provider_id
   *   The provider plugin ID.
   *
   * @return \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface|null
   *   The provider instance, or NULL if not found.
   */
  public function getProviderById(string $provider_id): ?CustomElementsPreviewProviderInterface {
    foreach ($this->providers as $provider) {
      if ($provider && $provider->getPluginId() === $provider_id) {
        return $provider;
      }
    }
    return NULL;
  }

}

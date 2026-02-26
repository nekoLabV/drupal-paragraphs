<?php

namespace Drupal\lupus_decoupled_ce_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface;
use Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderManager;

/**
 * Factory for creating the configured preview provider service.
 */
class LupusDecoupledPreviewProviderFactory {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The preview provider plugin manager.
   *
   * @var \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderManager
   */
  protected $previewProviderManager;

  /**
   * The base URL provider.
   *
   * @var \Drupal\lupus_decoupled_ce_api\BaseUrlProvider
   */
  protected $baseUrlProvider;

  /**
   * Constructs a LupusDecoupledPreviewProviderFactory.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderManager $preview_provider_manager
   *   The preview provider plugin manager.
   * @param \Drupal\lupus_decoupled_ce_api\BaseUrlProvider $base_url_provider
   *   The base URL provider.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CustomElementsPreviewProviderManager $preview_provider_manager, BaseUrlProvider $base_url_provider) {
    $this->configFactory = $config_factory;
    $this->previewProviderManager = $preview_provider_manager;
    $this->baseUrlProvider = $base_url_provider;
  }

  /**
   * Creates the configured preview provider.
   *
   * @return \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface
   *   The preview provider instance.
   */
  public function create(): CustomElementsPreviewProviderInterface {
    $settings = $this->configFactory->get('lupus_decoupled_ce_api.settings');
    $preview_provider_id = $settings->get('preview_provider') ?? 'markup';

    /** @var \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface $provider */
    $provider = $this->previewProviderManager->createInstance($preview_provider_id);

    // Set base URL from the base URL provider.
    $base_url = $this->baseUrlProvider->getFrontendBaseUrl();
    $provider->setBaseUrl($base_url);

    return $provider;
  }

}

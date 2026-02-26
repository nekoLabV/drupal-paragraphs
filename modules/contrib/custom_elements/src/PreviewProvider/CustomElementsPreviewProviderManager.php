<?php

namespace Drupal\custom_elements\PreviewProvider;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\custom_elements\Annotation\CustomElementsPreviewProvider;

/**
 * Plugin manager for Custom Elements Preview Provider plugins.
 *
 * This manager handles plugin discovery and instantiation. Plugins are
 * discovered via @CustomElementsPreviewProvider annotations.
 *
 * @see \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewResolver
 * @see \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface
 */
class CustomElementsPreviewProviderManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/CustomElementsPreviewProvider',
      $namespaces,
      $module_handler,
      CustomElementsPreviewProviderInterface::class,
      CustomElementsPreviewProvider::class
    );

    $this->alterInfo('custom_elements_preview_provider_info');
    $this->setCacheBackend($cache_backend, 'custom_elements_preview_provider_plugins');
  }

}

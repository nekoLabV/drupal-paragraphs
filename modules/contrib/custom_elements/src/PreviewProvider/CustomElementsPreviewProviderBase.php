<?php

namespace Drupal\custom_elements\PreviewProvider;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for Custom Elements Preview Provider plugins.
 */
abstract class CustomElementsPreviewProviderBase extends PluginBase implements CustomElementsPreviewProviderInterface {

  /**
   * The base URL used for generating preivews.
   *
   * For example, the base URL to be used to load the required JavaScript.
   *
   * @var string
   */
  protected string $baseUrl = '';

  /**
   * {@inheritdoc}
   */
  public function setBaseUrl(string $baseUrl): void {
    $this->baseUrl = $baseUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(Request $request): bool {
    // By default, providers are applicable when they have a configured
    // base URL.
    // @see \Drupal\custom_elements\PreviewProvider\CustomElementsPreviewProviderInterface
    return !empty($this->baseUrl);
  }

}

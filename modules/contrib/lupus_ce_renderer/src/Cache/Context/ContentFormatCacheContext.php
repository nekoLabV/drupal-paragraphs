<?php

namespace Drupal\lupus_ce_renderer\Cache\Context;

use drunomics\ServiceUtils\Symfony\HttpFoundation\RequestStackTrait;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Site\Settings;
use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a cache context for the content format.
 *
 * Cache context ID: 'lupus_ce_renderer_content_format'.
 */
class ContentFormatCacheContext implements CacheContextInterface {

  use RequestStackTrait;

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Lupus CE Renderer Content Format');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    // Any kind of string-value works, but let's prefix with ce to indicate
    // it's about the content format of custom elements.
    return 'ce:' . $this->getContentFormat();
  }

  /**
   * Gets content format from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   (optional) The request to use, or NULL to use the current request.
   *
   * @return string
   *   The content format. Either 'markup' or 'json'.
   *
   * @internal
   */
  public function getContentFormat(?Request $request = NULL) {
    $request = $request ?: $this->getRequestStack()->getCurrentRequest();

    // Get the default content format from settings.
    $content_format_settings = Settings::get('lupus_ce_renderer_default_format', CustomElementsRenderer::CONTENT_FORMAT_MARKUP);

    // Check if content format is set in request attributes.
    $default_content_format = $request->attributes->get('lupus_ce_renderer.content_format', $content_format_settings);

    // Finally, check if it's overridden by a query parameter.
    return $request->query->get('_content_format', $default_content_format);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}

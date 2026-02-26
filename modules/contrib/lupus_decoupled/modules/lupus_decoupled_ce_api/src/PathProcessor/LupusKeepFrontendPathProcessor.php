<?php

namespace Drupal\lupus_decoupled_ce_api\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Drupal\lupus_decoupled_ce_api\ApiResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use drunomics\ServiceUtils\Core\Config\ConfigFactoryTrait;
use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;

/**
 * Keeps certain frontend routes in the frontend when generating absolute URLs.
 */
class LupusKeepFrontendPathProcessor implements OutboundPathProcessorInterface {

  use ApiResponseTrait;
  use BaseUrlProviderTrait;
  use ConfigFactoryTrait;
  use CurrentRouteMatchTrait;

  /**
   * The frontend route paths to keep.
   *
   * @var array
   */
  protected $keepFrontendPaths;

  /**
   * Constructs a LupusKeepFrontendPathProcessor object.
   *
   * @param array $keep_frontend_paths
   *   The route paths to keep as frontend paths.
   */
  public function __construct(array $keep_frontend_paths) {
    $this->keepFrontendPaths = $keep_frontend_paths;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], ?Request $request = NULL, ?BubbleableMetadata $bubbleable_metadata = NULL) {
    if (!$this->isApiResponse($request) || empty($options['absolute'])) {
      return $path;
    }

    // Check if the current route path is in our list of frontend paths.
    if (!empty($options['route']) && in_array($options['route']->getPath(), $this->keepFrontendPaths)) {
      $options['base_url'] = isset($options['entity'])
        ? $this->getBaseUrlProvider()->getFrontendBaseUrlForEntity($options['entity'], $bubbleable_metadata)
        : $this->getBaseUrlProvider()->getFrontendBaseUrl();
    }

    return $path;
  }

}

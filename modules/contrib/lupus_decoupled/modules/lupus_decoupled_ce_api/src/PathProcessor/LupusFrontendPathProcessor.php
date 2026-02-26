<?php

namespace Drupal\lupus_decoupled_ce_api\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Drupal\lupus_decoupled_ce_api\ApiResponseTrait;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use drunomics\ServiceUtils\Core\Config\ConfigFactoryTrait;
use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;

/**
 * Processes paths of non-API (admin UI) responses to point to the frontend.
 */
class LupusFrontendPathProcessor implements OutboundPathProcessorInterface {

  use ApiResponseTrait;
  use BaseUrlProviderTrait;
  use ConfigFactoryTrait;
  use CurrentRouteMatchTrait;

  /**
   * An array of paths to redirect to the frontend.
   *
   * Contains '/node/{node}' and possibly others. Can be configured
   * via service parameters.
   *
   * If a new path is added to frontend_path, also add its route
   * to lupus_decoupled_ce_api.frontend_routes.
   *
   * @var string[]
   */
  protected $frontendPaths;

  /**
   * Path Alias Manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * FrontendRedirectSubscriber constructor.
   *
   * @param string[] $frontendPaths
   *   The paths to redirect.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   Path Alias Manager service.
   */
  public function __construct(array $frontendPaths, AliasManagerInterface $aliasManager) {
    $this->frontendPaths = $frontendPaths;
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], ?Request $request = NULL, ?BubbleableMetadata $bubbleable_metadata = NULL) {
    // If the current route is a front end route and the base URL isn't set
    // already, set it - except do not change relative URLs to absolute in API
    // responses: requesters must handle those correctly.
    if (isset($options['route']) && in_array($options['route']->getPath(), $this->frontendPaths)
        && empty($options['base_url']) && $request) {
      $absolute = $options['absolute'] ?? FALSE;
      if ($absolute || !$this->isApiResponse($request)) {
        $options['base_url'] = isset($options['entity'])
          ? $this->getBaseUrlProvider()->getFrontendBaseUrlForEntity($options['entity'], $bubbleable_metadata)
          : $this->getBaseUrlProvider()->getFrontendBaseUrl();
        $options['absolute'] = TRUE;
      }
    }
    return $path;
  }

}

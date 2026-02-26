<?php

namespace Drupal\lupus_decoupled_webform;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\lupus_decoupled_ce_api\ApiResponseTrait;
use Drupal\system\PathBasedBreadcrumbBuilder;
use drunomics\ServiceUtils\Symfony\HttpFoundation\RequestStackTrait;

/**
 * Class to define the webform page breadcrumb builder.
 */
class WebformPathBasedBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  use ApiResponseTrait;
  use RequestStackTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match, ?CacheableMetadata $cacheable_metadata = NULL) {
    if (str_contains($route_match->getRouteName(), 'entity.webform.canonical') && $this->isApiResponse($this->getCurrentRequest())) {
      return TRUE;
    }
    return FALSE;
  }

}

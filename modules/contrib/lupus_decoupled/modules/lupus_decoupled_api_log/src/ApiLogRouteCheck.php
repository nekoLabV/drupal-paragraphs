<?php

namespace Drupal\lupus_decoupled_api_log;

use Drupal\lupus_decoupled_ce_api\ApiResponseTrait;
use Drupal\rest_log\RestLogRouteCheckInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Connects lupus_decoupled api requests and responses with rest_log.
 */
final class ApiLogRouteCheck implements RestLogRouteCheckInterface {

  use ApiResponseTrait;

  /**
   * Constructs an ApiLogRouteCheck object.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * {@inheritDoc}
   */
  public function applies() {
    return $this->isApiResponse($this->requestStack->getCurrentRequest());
  }

}

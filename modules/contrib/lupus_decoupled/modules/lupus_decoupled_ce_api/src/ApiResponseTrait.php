<?php

namespace Drupal\lupus_decoupled_ce_api;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait with API response helpers.
 *
 * @category Trait
 * @package Drupal\lupus_Decoupled_Ce_Api
 */
trait ApiResponseTrait {

  /**
   * Determines if we are serving an api response.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   The request object.
   *
   * @return bool
   *   Returns TRUE if we are serving an api response.
   */
  protected function isApiResponse(?Request $request): bool {
    if (!$request) {
      return FALSE;
    }
    return $request->attributes->get('lupus_ce_renderer') || $request->getRequestFormat() == 'custom_elements';
  }

}

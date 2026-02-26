<?php

namespace Drupal\lupus_decoupled_form;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Allows routes to set the active theme via the _theme route option.
 */
class RouteThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_object = $route_match->getRouteObject()) {
      return !empty($route_object->hasOption('_theme'));
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    if ($route_object = $route_match->getRouteObject()) {
      return $route_object->getOption('_theme');
    }
    return NULL;
  }

}

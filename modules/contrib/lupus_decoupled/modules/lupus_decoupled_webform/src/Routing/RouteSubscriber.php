<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_webform\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route subscriber.
 */
final class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    // Provide a CE variant of the webform forms.
    if ($route = $collection->get('entity.webform.canonical')) {
      $ce_route = clone $route;
      $ce_route->setRequirement('_format', 'custom_elements');
      $ce_route->setDefault('_controller', 'Drupal\lupus_decoupled_webform\Controller\CustomElementsWebformController::addForm');
      $collection->add('custom_elements.entity.webform.canonical', $ce_route);
    }
    if ($route = $collection->get('entity.webform.confirmation')) {
      $ce_route = clone $route;
      $ce_route->setRequirement('_format', 'custom_elements');
      $ce_route->setDefault('_controller', 'Drupal\lupus_decoupled_webform\Controller\CustomElementsWebformController::confirmation');
      $collection->add('custom_elements.entity.webform.confirmation', $ce_route);
    }
  }

}

<?php

namespace Drupal\lupus_decoupled_user_form\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Creates CE variants for user forms.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Provide CE variants for user forms.
    $form_route_ids = ['user.login', 'user.pass', 'user.register'];
    foreach ($form_route_ids as $form_route_id) {
      $route = $collection->get($form_route_id);
      $ce_route = clone $route;
      $ce_route->setRequirement('_format', 'custom_elements');
      $form = $route->hasDefault('_entity_form') ? 'entity_form' : 'form';
      $ce_route->setDefault('_controller', "lupus_decoupled_form.controller.$form:getContentResult");
      $collection->add("lupus_decoupled.{$form_route_id}", $ce_route);
    }
  }

}

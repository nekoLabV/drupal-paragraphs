<?php

namespace Drupal\lupus_decoupled_contact\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Changes contact forms route controllers.
 *
 * @internal
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Provide a CE variant of the contact form.
    $form_route_ids = ['entity.contact_form.canonical', 'contact.site_page'];
    foreach ($form_route_ids as $form_route_id) {
      $route = $collection->get($form_route_id);
      $ce_route = clone $route;
      $ce_route->setRequirement('_format', 'custom_elements');
      $ce_route->setDefault('_controller', '\Drupal\lupus_decoupled_contact\Controller\ContactController::contactSitePage');
      $collection->add("lupus_decoupled.{$form_route_id}", $ce_route);
    }
  }

}

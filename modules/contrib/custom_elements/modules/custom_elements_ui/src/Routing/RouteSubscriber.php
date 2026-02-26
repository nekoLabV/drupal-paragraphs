<?php

namespace Drupal\custom_elements_ui\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Custom Elements UI routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($route_name = $entity_type->get('field_ui_base_route')) {
        // Try to get the route from the current collection.
        if (!$entity_route = $collection->get($route_name)) {
          continue;
        }
        $path = $entity_route->getPath();

        $options = $entity_route->getOptions();
        if ($bundle_entity_type = $entity_type->getBundleEntityType()) {
          $options['parameters'][$bundle_entity_type] = [
            'type' => 'entity:' . $bundle_entity_type,
          ];
        }
        // Special parameter used to easily recognize all _field_ui routes.
        // routes.
        // Usage example: Drupal\Core\Entity\Enhancer\EntityBundleRouteEnhancer.
        $options['_field_ui'] = TRUE;

        $defaults = [
          'entity_type_id' => $entity_type_id,
        ];

        // If the entity type has no bundles and it doesn't use {bundle} in its
        // admin path, use the entity type.
        if (strpos($path, '{bundle}') === FALSE) {
          $defaults['bundle'] = !$entity_type->hasKey('bundle') ? $entity_type_id : '';
        }

        $route = new Route(
          "$path/ce-display",
          [
            '_entity_form' => 'entity_ce_display.edit',
            '_title' => 'Manage custom element',
            'view_mode_name' => 'default',
          ] + $defaults,
          ['_custom_elements_ui_view_mode_access' => 'administer ' . $entity_type_id . ' custom element display'],
          $options
        );
        $collection->add("entity.entity_ce_display.{$entity_type_id}.default", $route);

        $route = new Route(
          "$path/ce-display/{view_mode_name}",
          [
            '_entity_form' => 'entity_ce_display.edit',
            // @todo Title callback?
            '_title' => 'Manage custom element',
          ] + $defaults,
          ['_custom_elements_ui_view_mode_access' => 'administer ' . $entity_type_id . ' custom element display'],
          $options
        );
        $collection->add("entity.entity_ce_display.{$entity_type_id}.view_mode", $route);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -120];
    return $events;
  }

}

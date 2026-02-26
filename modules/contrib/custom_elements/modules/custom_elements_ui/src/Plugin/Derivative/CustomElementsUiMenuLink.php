<?php

namespace Drupal\custom_elements_ui\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom elements menu link plugin.
 */
class CustomElementsUiMenuLink extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface,
   */
  protected $configFactory;

  /**
   * Creates a CustomElementsUiLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, RouteProviderInterface $route_provider, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->routeProvider = $route_provider;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('router.route_provider'),
      $container->get('config.factory')
    );
  }

  /**
   * Gets derivative definitions.
   *
   * @see \Drupal\admin_toolbar_tools\Plugin\Derivative\ExtraLinks::getDerivativeDefinitions()
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $entity_types = $this->entityTypeManager->getDefinitions();

    if ($this->moduleHandler->moduleExists('admin_toolbar_tools')) {
      // Maximum number of bundle sub-menus to display.
      $max_bundle_number = $this->configFactory->get('admin_toolbar_tools.settings')->get('max_bundle_number');
      $content_entities = [];
      foreach ($entity_types as $key => $entity_type) {
        if ($entity_type->getBundleEntityType() && ($entity_type->get('field_ui_base_route') != '')) {
          $content_entities[$key] = [
            'content_entity' => $key,
            'content_entity_bundle' => $entity_type->getBundleEntityType(),
          ];
        }
      }

      // Adds common links to entities.
      foreach ($content_entities as $entities) {
        $content_entity_bundle = $entities['content_entity_bundle'];
        $content_entity = $entities['content_entity'];
        $content_entity_bundle_storage = $this->entityTypeManager->getStorage($content_entity_bundle);
        $bundles_ids = $content_entity_bundle_storage->getQuery()
          ->accessCheck()
          ->sort('weight')
          ->sort($this->entityTypeManager->getDefinition($content_entity_bundle)->getKey('label'))
          ->pager($max_bundle_number)
          ->execute();
        $bundles = $this->entityTypeManager->getStorage($content_entity_bundle)->loadMultiple($bundles_ids);
        foreach ($bundles as $machine_name => $bundle) {
          // Normally, the edit form for the bundle would be its
          // root link.
          $content_entity_bundle_root = '';
          if ($this->routeExists('entity.' . $content_entity_bundle . '.overview_form')) {
            // Some bundles have an overview/list form that make a better
            // root link.
            $content_entity_bundle_root = 'entity.' . $content_entity_bundle . '.overview_form.' . $machine_name;
          }
          if ($this->routeExists('entity.' . $content_entity_bundle . '.edit_form') && empty($content_entity_bundle_root)) {
            $content_entity_bundle_root = 'entity.' . $content_entity_bundle . '.edit_form.' . $machine_name;
          }
          if ($this->routeExists('entity.entity_ce_display.' . $content_entity . '.default') && $content_entity_bundle_root) {
            $links['entity.entity_ce_display.' . $content_entity . '.default' . $machine_name] = [
              'title' => $this->t('Manage custom element'),
              'route_name' => 'entity.entity_ce_display.' . $content_entity . '.default',
              'parent' => 'admin_toolbar_tools.extra_links:' . $content_entity_bundle_root,
              'route_parameters' => [$content_entity_bundle => $machine_name],
              'weight' => 4,
            ] + $base_plugin_definition;
          }
        }
      }
    }

    return $links;
  }

  /**
   * Determine if a route exists by name.
   *
   * @param string $route_name
   *   The name of the route to check.
   *
   * @return bool
   *   Whether a route with that route name exists.
   */
  public function routeExists($route_name) {
    return (count($this->routeProvider->getRoutesByNames([$route_name])) === 1);
  }

}

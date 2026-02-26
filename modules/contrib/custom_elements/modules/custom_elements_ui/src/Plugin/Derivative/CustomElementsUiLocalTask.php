<?php

namespace Drupal\custom_elements_ui\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Custom Elements local task definitions for all entity bundles.
 *
 * @phpstan-consistent-constructor
 */
class CustomElementsUiLocalTask extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Creates a CustomElementsUiLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->get('field_ui_base_route')) {
        // 'Manage Custom Element display' primary tab.
        $this->derivatives["ce_display_overview_$entity_type_id"] = [
          'route_name' => "entity.entity_ce_display.$entity_type_id.default",
          'weight' => 5,
          'title' => $this->t('Manage custom element'),
          'base_route' => "entity.$entity_type_id.field_ui_fields",
        ];

        // Default Custom element mode as a secondary tab.
        $this->derivatives['field_ce_display_default_' . $entity_type_id] = [
          'title' => 'Default',
          'route_name' => "entity.entity_ce_display.$entity_type_id.default",
          'parent_id' => "custom_elements_ui.local_tasks:ce_display_overview_$entity_type_id",
          'weight' => -1,
        ];

        // Additionally one secondary local task for each mode.
        $weight = 0;
        foreach ($this->entityDisplayRepository->getViewModes($entity_type_id) as $view_mode => $form_mode_info) {
          $this->derivatives['field_ce_display_' . $view_mode . '_' . $entity_type_id] = [
            'title' => $form_mode_info['label'],
            'route_name' => "entity.entity_ce_display.$entity_type_id.view_mode",
            'route_parameters' => [
              'view_mode_name' => $view_mode,
            ],
            'parent_id' => "custom_elements_ui.local_tasks:ce_display_overview_$entity_type_id",
            'weight' => $weight++,
            'cache_tags' => $this->entityTypeManager->getDefinition('entity_ce_display')->getListCacheTags(),
          ];
        }

      }
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $this->derivatives;
  }

  /**
   * Alters the base_route definition for custom_elements_ui local tasks.
   *
   * @param array $local_tasks
   *   An array of local tasks plugin definitions, keyed by plugin ID.
   */
  public function alterLocalTasks(array &$local_tasks) {
    // @todo Not sure why it should be in alter function and not in definition.
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($route_name = $entity_type->get('field_ui_base_route')) {
        $local_tasks["custom_elements_ui.local_tasks:ce_display_overview_$entity_type_id"]['base_route'] = $route_name;
      }
    }
  }

}

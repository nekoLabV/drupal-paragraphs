<?php

namespace Drupal\custom_elements_ui;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the custom_elements_ui module.
 *
 * @phpstan-consistent-constructor
 */
class CustomElementsUiPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CeUiPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of Custom Elements UI permissions.
   *
   * @return array
   *   Array of permission definitions.
   */
  public function ceDisplayPermissions() {
    $permissions = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->get('field_ui_base_route')) {
        // The permissions depend on the module that provides the entity.
        $dependencies = ['module' => [$entity_type->getProvider()]];
        // Create a permission for each fieldable entity to manage custom
        // element display.
        $permissions['administer ' . $entity_type_id . ' custom element display'] = [
          'title' => $this->t('%entity_label: Administer custom element', ['%entity_label' => $entity_type->getLabel()]),
          'dependencies' => $dependencies,
        ];
      }
    }

    return $permissions;
  }

}

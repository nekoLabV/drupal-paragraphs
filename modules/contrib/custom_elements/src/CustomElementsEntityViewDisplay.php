<?php

namespace Drupal\custom_elements;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Customized entity display to take over entity rendering.
 */
class CustomElementsEntityViewDisplay extends EntityViewDisplay {

  use CustomElementsEntityViewDisplayTrait;

  /**
   * Returns whether the entity is rendered via custom elements.
   *
   * @return bool
   *   TRUE if custom element enabled, otherwise FALSE.
   */
  public function isCustomElementsEnabled() {
    return (bool) $this->getThirdPartySetting('custom_elements', 'enabled', FALSE);
  }

  /**
   * Identifies whether it's real display entity of just single field.
   *
   * To generate renderable array for a single field this way
   * $field_item->view($view_mode), FieldItemList::view() imitates process of
   * generating renderable array of entire entity with only difference that
   * entity_view_display entity will contain only one component - needed field.
   * To prevent endless loop we need to know whether it's build of entire entity
   * or just single field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity being displayed.
   *
   * @return bool
   *   TRUE if custom element enabled, otherwise FALSE.
   *
   * @see \Drupal\Core\Field\FieldItemList::view()
   * @see \Drupal\Core\Entity\EntityViewBuilder::viewField()
   */
  public function isSingleFieldDisplay(FieldableEntityInterface $entity): bool {
    // @todo Logic is not perfect but still not sure how to check it better.
    $components = $this->getComponents();
    // Filter out any dynamic components and keep only actual entity fields.
    $field_names = array_keys($entity->getFieldDefinitions());
    $components = array_intersect_key($components, array_flip($field_names));
    if (count($components) === 1) {
      // Load original entity_view_display to compare components.
      $original = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load($this->id());
      if (isset($original)) {
        $original_components = $original->getComponents();
        $original_components = array_intersect_key($original_components, array_flip($field_names));
        // Single field view if original has more than 1 component or if
        // components are different.
        if (count($original_components) !== 1) {
          return TRUE;
        }
        elseif ($original_components == $components) {
          return FALSE;
        }
        else {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function buildMultiple(array $entities) {
    if (empty($entities)) {
      return [];
    }
    // If not enabled or rendering of single field use default building process.
    if (!$this->isCustomElementsEnabled() || $this->isSingleFieldDisplay(reset($entities))) {
      return parent::buildMultiple($entities);
    }
    $build_list = [];
    $this->buildMultipleViaCustomElements($build_list, $entities);
    return $build_list;
  }

}

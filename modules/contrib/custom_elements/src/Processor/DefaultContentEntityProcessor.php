<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\custom_elements\CustomElementsBlockRenderHelperTrait;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;

/**
 * Default processor for content entities using Core's entity display.
 *
 * Since v3 processors on entity-level are used only when
 * "force automatic-processing" is turned in the CE-display settings of the
 * view-mode. Before (2.x) this was default.
 *
 * @see \Drupal\custom_elements\CustomElementGenerator::generate()
 */
class DefaultContentEntityProcessor implements CustomElementProcessorWithKeyInterface {

  use CustomElementGeneratorTrait;
  use CustomElementsBlockRenderHelperTrait;
  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    return $data instanceof ContentEntityInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($data, CustomElement $element, $viewMode, $key = '') {
    assert($data instanceof ContentEntityInterface);
    $entity = $data;

    $displays = EntityViewDisplay::collectRenderDisplays([$entity], $viewMode);
    $display = reset($displays);
    foreach ($display->getComponents() as $field_name => $options) {
      if ($this->fieldIsAccessible($entity, $field_name, $element)) {
        $this->getCustomElementGenerator()
          ->process($entity->get($field_name), $element, $viewMode, $key);
      }
    }
  }

}

<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsProcessorFieldUtilsTrait;

/**
 * Default processor for field items.
 */
class DefaultFieldItemProcessor implements CustomElementProcessorWithKeyInterface {

  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    return $data instanceof FieldItemInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($data, CustomElement $element, $viewMode, $key = '') {
    assert($data instanceof FieldItemInterface);
    $field_item = $data;

    // Add all primitive properties by default; use $key as prefix with '-'
    // separator.
    $prefixed_key = $key ? "$key-" : '';
    foreach ($field_item->getProperties(TRUE) as $name => $property) {
      if ($property instanceof PrimitiveInterface) {
        try {
          $element->setAttribute($prefixed_key . $name, $property->getValue());
        }
        catch (\AssertionError $e) {
          // Workaround for Canvas issue #3556426: Skip properties that fail
          // to compute due to assertion errors in Canvas computed properties.
          if (str_contains($e->getFile(), '/canvas/')) {
            continue;
          }
          // Re-throw if not from Canvas module.
          throw $e;
        }
      }
      elseif ($property instanceof DataReferenceInterface) {
        try {
          // Add links to referenced entities as slot if the entity is
          // accessible and linkable.
          if (($property->getDataDefinition()->getTargetDefinition() instanceof EntityDataDefinitionInterface
            && $property->getTarget() && $entity = $property->getTarget()->getValue())
            && $this->entityIsAccessible($entity, $element) && $url = $entity->toUrl()) {
            $nested_element = new CustomElement();
            $nested_element->setTag('a');
            $generated_url = $url->toString(TRUE);
            $nested_element->addCacheableDependency($generated_url);
            $nested_element->setAttribute('href', $generated_url->getGeneratedUrl());
            $nested_element->setAttribute('type', $entity->getEntityTypeId());
            $nested_element->setSlot('default', $entity->label());
            $nested_element->addCacheableDependency($entity);
            $element->setSlotFromCustomElement($prefixed_key . $name, $nested_element);
          }
        }
        catch (UndefinedLinkTemplateException $exception) {
          // Skip if no link-template is defined.
        }
      }
      // We cannot generically add other properties since we do not know how
      // to render them and they are not primitive. So they are skipped.
    }

    // Add the main property as default slot if no content would be there else.
    // However, do not do this if this the only attribute, because in that
    // case we rather let the default field item list processor optimize the
    // whole tag into a parent attribute.
    if (count($element->getSlots()) == 0 && count($element->getAttributes()) != 1 && $name = $field_item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName()) {
      if ($field_item->get($name) instanceof PrimitiveInterface) {
        $element->setSlot('default', $field_item->get($name)->getValue());
        $element->setAttribute($key ?: $name, NULL);
      }
    }

  }

}

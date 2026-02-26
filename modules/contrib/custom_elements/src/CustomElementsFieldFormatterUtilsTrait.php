<?php

namespace Drupal\custom_elements;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Trait for custom elements field formatters.
 */
trait CustomElementsFieldFormatterUtilsTrait {

  /**
   * Returns property name/value pairs for a single field item, as an array.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $field_item
   *   The field to process.
   * @param string[] $ignore_properties
   *   (Optional) properties to ignore.
   *
   * @return array
   *   The field's property values keyed by property name. Always returned as
   *   an array, for easier extensibility by child classes.
   */
  protected function getFieldItemProperties(FieldItemInterface $field_item, array $ignore_properties = []): array {
    // Collect all values into an array. Note iterating through getValue() can
    // (depending on the field) produce different results than just
    // $values = $field_item->getValue().
    $values = [];
    foreach ($field_item->getValue() as $name => $value) {
      if (!in_array($name, $ignore_properties, TRUE)) {
        $values[$name] = $value;
      }
    }
    return $values;
  }

  /**
   * Sets the value according to the formatter's configuration.
   *
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element to set the value on.
   * @param string $name
   *   The name of the slot or attribute to set.
   * @param mixed $value
   *   The value to set for the slot or attribute.
   */
  protected function setValue(CustomElement $custom_element, string $name, $value) {
    if ($this->isSlot()) {
      $custom_element->addSlot($name, is_array($value) ? implode(', ', $value) : $value);
    }
    else {
      $custom_element->setAttribute($name, $value);
    }
  }

  /**
   * Sets a multiple values according to the formatter's configuration.
   *
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element to set the value on.
   * @param string $name
   *   The name of the slot or attribute to set.
   * @param mixed[] $value
   *   The value to set for the slot or attribute.
   */
  protected function setMultipleValue(CustomElement $custom_element, string $name, array $value) {
    if ($this->isSlot()) {
      foreach ($value as $value_item) {
        $custom_element->addSlot($name, is_array($value_item) ? implode(', ', $value_item) : $value_item);
      }
    }
    else {
      // If output as an attribute, set the value as a single data structure.
      // However, if the value is a single item, we do not keep it as an array.
      if ($this->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() == 1) {
        // Only output the first field item when flattening is enabled.
        $value = reset($value);
      }
      $custom_element->setAttribute($name, $value);
    }
  }

}

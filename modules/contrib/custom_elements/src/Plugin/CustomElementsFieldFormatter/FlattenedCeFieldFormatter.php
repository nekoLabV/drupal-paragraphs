<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;
use Drupal\custom_elements\CustomElementsFieldFormatterUtilsTrait;

/**
 * Implementation of the 'flattened' custom element formatter plugin.
 *
 * A field's property values are set in separate attributes. For multi-value
 * fields, the first field value is taken and others are ignored.
 *
 * @CustomElementsFieldFormatter(
 *   id = "flattened",
 *   label = @Translation("Flattened properties"),
 * )
 */
class FlattenedCeFieldFormatter extends CustomElementsFieldFormatterBase {

  use CustomElementsFieldFormatterUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    // Process single field value. If the value holds multiple properties:
    // - Set the 'main' property in a slot or attribute as configured, with the
    //   configured name - or if that's empty: the original property name.
    // - Add any other properties as attributes, with
    //   "<configured name>-<own name>".
    $field_item = $items->first();
    if ($field_item === NULL) {
      return;
    }
    assert($field_item instanceof FieldItemInterface);

    $element_name = $this->getName();
    $main_property_name = $this->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
    foreach ($this->getFieldItemProperties($field_item) as $name => $value) {
      if ($element_name) {
        $set_name = $name === $main_property_name ? $element_name : "$element_name-$name";
      }
      else {
        $set_name = $name;
      }
      if ($name === $main_property_name && $this->isSlot()) {
        $custom_element->setSlot('default', $value);
        $custom_element->setAttribute($set_name, NULL);
      }
      else {
        $custom_element->setAttribute($set_name, $value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Nothing needed.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Nothing needed.
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() != 1) {
      $summary[] = $this->t('Only the first field value is output.');
    }
    return $summary;
  }

}

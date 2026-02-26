<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;

/**
 * Implementation of the 'raw' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "raw",
 *   label = @Translation("Raw"),
 *   weight = -10
 * )
 */
class RawCeFieldFormatter extends CustomElementsFieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    $values = $this->getFieldItemListValue($items);
    // Make single-value fields flat by default.
    if ($items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() == 1) {
      $values = reset($values);
    }

    if ($this->isSlot()) {
      $custom_element->addSlot($this->getName(), is_scalar($values) || $values instanceof MarkupInterface ? $values : Json::encode($values));
    }
    else {
      $custom_element->setAttribute($this->getName(), $values);
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
   * Returns value for the given field.
   *
   * This is a separate method, for easier extension by child classes.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field to process.
   *
   * @return array
   *   The field's value(s).
   */
  protected function getFieldItemListValue(FieldItemListInterface $items): array {
    return $items->getValue();
  }

}

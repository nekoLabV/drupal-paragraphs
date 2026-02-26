<?php

namespace Drupal\custom_elements_extra_formatters\Plugin\CustomElementsFieldFormatter;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;

/**
 * Implementation of the 'tablefield' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "ce_tablefield",
 *   label = @Translation("Tablefield data"),
 *   Field_types = {
 *     "tablefield"
 *   }
 * )
 */
class TableFieldCeFormatter extends CustomElementsFieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    $value = $this->getFieldItemListValue($items);
    $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();
    if ($cardinality === 1) {
      $value = $value[0];
      $element = [
        'settings' => [
          'row_header' => $this->getSetting('row_header') ?? 0,
          'column_header' => $this->getSetting('column_header') ?? 0,
        ],
      ] + $value;
    }
    else {
      $element = [
        'data' => $value,
        'settings' => [
          'row_header' => $this->getSetting('row_header') ?? 0,
          'column_header' => $this->getSetting('column_header') ?? 0,
        ],
      ];
    }
    if ($this->isSlot()) {
      $custom_element->addSlot($this->getName(), Json::encode($element));
    }
    else {
      $custom_element->setAttribute($this->getName(), $element);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['row_header'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('First row as a table header'),
      '#default_value' => $this->getSetting('row_header'),
    ];
    $form['column_header'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('First column as a table header'),
      '#default_value' => $this->getSetting('column_header'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['row_header'] = $form_state->getValue('row_header');
    $this->configuration['column_header'] = $form_state->getValue('column_header');
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
    $value = [];
    foreach ($items->getValue() as $delta => $table) {
      $caption = $table['caption'] ?? NULL;
      unset($table['value']['caption']);
      $data = $table['value'];
      usort($data, function ($a, $b) {
        $aweight = $a['weight'] ?? 0;
        $bweight = $b['weight'] ?? 0;
        return $aweight - $bweight;
      });
      $value[$delta] = [
        'caption' => $caption,
        'value' => array_map(function ($v) {
          unset($v['weight']);
          return $v;
        }, $data),
      ];
    }
    return $value;
  }

}

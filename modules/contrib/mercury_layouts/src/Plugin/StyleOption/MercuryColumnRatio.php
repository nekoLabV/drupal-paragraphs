<?php

declare(strict_types=1);

namespace Drupal\mercury_layouts\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOption\Property;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "me_column_ratio",
 *   label = @Translation("Column Ratio")
 * )
 */
class MercuryColumnRatio extends Property {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    // Add a fieldset to group the column form elements together.
    $form['column_ratio'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Column Ratio'),
      '#tree' => TRUE,
      '#description' => $this->getConfiguration('description'),
    ];

    $columnCount = $this->getConfiguration('column_count') ?? 2;

    // Add a number input field for each column.
    for ($i = 0; $i < $columnCount; $i++) {
      $form['column_ratio'][$i] = [
        '#type' => 'number',
        '#title' => $this->t('Column @number', ['@number' => $i]),
        '#default_value' => $this->getValue('column_ratio')[$i] ? (int) $this->getValue('column_ratio')[$i] : 1,
        '#min' => 1,
        '#step' => 1,
      ];
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('column_ratio') ?? NULL;
    $columnCount = $this->getConfiguration('column_count') ?? 2;
    $property = $value ?? array_fill(0, $columnCount, 1);
    $property = array_map('intval', $property);

    if (!empty($property)) {
      $build['#' . $this->getOptionId()] = $property;
    }
    return $build;
  }

}

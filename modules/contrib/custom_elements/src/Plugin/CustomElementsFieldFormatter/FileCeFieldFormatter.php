<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;
use Drupal\custom_elements\CustomElementsFieldFormatterUtilsTrait;
use Drupal\custom_elements\CustomElementsImageStyleConfigTrait;

/**
 * Implementation of the 'file' custom element formatter plugin.
 *
 * File/image fields are a combination of a reference to a file/image entity,
 * and several extra properties. For images fields, this is exposed as a
 * separate plugin via the ImageCeFieldFormatter.
 *
 * @CustomElementsFieldFormatter(
 *   id = "file",
 *   label = @Translation("File properties"),
 *   field_types = {
 *     "file",
 *     "image"
 *   }
 * )
 */
class FileCeFieldFormatter extends CustomElementsFieldFormatterBase {

  use CustomElementsImageStyleConfigTrait;
  use CustomElementsFieldFormatterUtilsTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $field_items, CustomElement $custom_element, $langcode = NULL) {
    $output = [];
    foreach ($field_items as $field_item) {
      $values = $this->getFieldItemProperties($field_item, ['target_id']);
      // Add the URL to the file or image style.
      if ($this->getFieldDefinition()->getType() === 'image' && $this->configuration['image_style']) {
        $image_style = $this->loadImageStyle($this->configuration['image_style']);
        $values['url'] = $image_style ? $this->getImageStyleUrl($image_style, $field_item->entity->getFileUri()) : '';
        // If the title field is not used, remove the empty title property.
        if (empty($this->getFieldDefinition()->getSetting('title_field'))) {
          unset($values['title']);
        }
        // Correct width/height to be the styled image's dimensions.
        if ($image_style && !empty($values['width']) && !empty($values['height'])) {
          $this->applyImageStyleDimensions($image_style, $field_item->entity->getFileUri(), $values);
        }
      }
      else {
        // Add url property of the referenced file to the values.
        $values['url'] = $field_item->entity->uri->url;
      }
      $output[] = $values;
    }

    if ($this->configuration['flatten']) {
      // Only output the first field item when flattening is enabled.
      $output = reset($output);

      foreach ($output as $name => $value) {
        $name = !empty($this->configuration['flatten_skip_prefix']) ? $name : "{$this->getName()}-$name";
        $this->setValue($custom_element, $name, $value);
      }
    }
    else {
      $this->setMultipleValue($custom_element, $this->getName(), $output);
    }
  }

  /**
   * Updates width and height in $values to match the image style's output.
   *
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style.
   * @param string $uri
   *   The image file URI.
   * @param array &$values
   *   The values array, must contain 'width' and 'height'.
   */
  private function applyImageStyleDimensions($image_style, $uri, array &$values) {
    $dimensions = [
      'width' => $values['width'],
      'height' => $values['height'],
    ];
    $image_style->transformDimensions($dimensions, $uri);
    $values['width'] = $dimensions['width'];
    $values['height'] = $dimensions['height'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'image_style' => '',
      'flatten' => $this->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() == 1 ? TRUE : FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['flatten'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Flatten output'),
      '#default_value' => $this->configuration['flatten'],
      '#description' => $this->t('Flattens the output data structure to multiple, flat entries.'),
    ];
    $form['flatten_skip_prefix'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Skip name prefix for flattened entries'),
      '#default_value' => !empty($this->configuration['flatten_skip_prefix']),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->getFieldDefinition()->getName() . '][settings_edit_form][form][flatten]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    if ($this->getFieldDefinition()->getType() === 'image') {
      $form += $this->getImageStyleConfigForm($this->configuration['image_style']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['flatten'] = $form_state->getValue('flatten');
    if ($this->configuration['flatten']) {
      $this->configuration['flatten_skip_prefix'] = $form_state->getValue('flatten_skip_prefix');
    }
    if ($this->getFieldDefinition()->getType() === 'image') {
      $this->configuration['image_style'] = $form_state->getValue('image_style');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getFieldDefinition()->getType() === 'image') {
      $summary += $this->getSettingsSummary($this->configuration['image_style']);
    }
    if (!empty($this->configuration['flatten'])) {
      $summary[] = !empty($this->configuration['flatten_skip_prefix']) ? $this->t('Flattened (no prefix)') : $this->t('Flattened');
      if ($this->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() != 1) {
        $summary[] = $this->t('Only the first field value is output.');
      }
    }
    return $summary;
  }

}

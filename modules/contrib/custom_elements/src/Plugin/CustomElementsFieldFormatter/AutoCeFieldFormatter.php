<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGenerator;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;

/**
 * Implementation of the 'auto' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "auto",
 *   label = @Translation("Auto"),
 *   weight = -10
 * )
 */
class AutoCeFieldFormatter extends CustomElementsFieldFormatterBase {

  /**
   * Custom elements generator.
   *
   * @var \Drupal\custom_elements\CustomElementGenerator
   */
  protected CustomElementGenerator $ceGenerator;

  /**
   * Construct.
   *
   * @param object $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param object $plugin_definition
   *   Plugin definition.
   * @param \Drupal\custom_elements\CustomElementGenerator $ce_generator
   *   Custom element generator.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, CustomElementGenerator $ce_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ceGenerator = $ce_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container, array $configuration, $plugin_id, $plugin_definition) {
    // @phpstan-ignore-next-line
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('custom_elements.generator'));
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    $key = $this->getName();
    $this->ceGenerator->process($items, $custom_element, $this->getViewMode(), $key);

    // Post-process the result in order to respect the isSlot configuration.
    // if isSlot is TRUE: ensure content is in slot, not attribute.
    if ($this->isSlot() && $value = $custom_element->getAttribute($key)) {
      $custom_element->setSlot($key, is_array($value) ? json_encode($value) : $value);
      $custom_element->removeAttribute($key);
    }
    // If isSlot is FALSE: ensure content is in attribute, not slot.
    elseif (!$this->isSlot() && $custom_element->getAttribute($key) === NULL) {
      $cache_metadata = new BubbleableMetadata();
      // Simply set the attribute with the normalized-slot output.
      $array = $custom_element->toArray(TRUE, $cache_metadata, FALSE);
      $slot_key = CustomElement::fixSlotKey($key);
      $custom_element->setAttribute($key, $array[$slot_key] ?? NULL);
      $custom_element->addCacheableDependency($cache_metadata);
      $custom_element->removeSlot($key);
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

}

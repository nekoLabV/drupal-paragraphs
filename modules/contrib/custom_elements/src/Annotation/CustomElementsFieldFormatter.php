<?php

namespace Drupal\custom_elements\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a CustomElementsFieldFormatter annotation object.
 *
 * Formatters handle the display of field values as custom element.
 * They are typically instantiated and invoked by an EntityCeDisplay
 * object.
 *
 * @Annotation
 *
 * @see \Drupal\custom_elements\CustomElementFormatterPluginManager
 * @see \Drupal\custom_elements\CustomElementFormatterInterface
 *
 * @ingroup custom_element_formatter
 */
class CustomElementsFieldFormatter extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the custom element formatter type.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The name of the custom element formatter class.
   *
   * This is not provided manually, it will be added by the discovery mechanism.
   *
   * @var string
   */
  public $class;

  /**
   * An array of field types the custom element formatter supports.
   *
   * @var array
   */
  public $field_types = [];

  /**
   * An integer to determine the weight of this custom element formatter.
   *
   * Weight is relative to other formatter in the Field UI when selecting a
   * formatter for a given field instance.
   *
   * This property is optional and it does not need to be declared.
   *
   * @var int
   */
  public $weight = NULL;

}

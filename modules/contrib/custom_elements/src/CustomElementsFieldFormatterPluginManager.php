<?php

namespace Drupal\custom_elements;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for field custom element formatters.
 *
 * @ingroup custom_element_formatter
 *
 * @internal
 *   This class may still change as other (non-field) plugin types are added.
 */
class CustomElementsFieldFormatterPluginManager extends DefaultPluginManager {

  /**
   * An array of formatter options for each field type.
   *
   * @var array
   */
  protected $formatterOptions;

  /**
   * The field type manager to define field.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

  /**
   * Constructs a FormatterPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The 'field type' plugin manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, FieldTypePluginManagerInterface $field_type_manager) {
    parent::__construct('Plugin/CustomElementsFieldFormatter', $namespaces, $module_handler, CustomElementsFieldFormatterInterface::class, 'Drupal\custom_elements\Annotation\CustomElementsFieldFormatter');

    $this->setCacheBackend($cache_backend, 'custom_elements_field_formatter');
    $this->alterInfo('custom_elements_field_formatter_info');
    $this->fieldTypeManager = $field_type_manager;
  }

  /**
   * Returns an array of formatter options for a field type.
   *
   * @param string|null $field_type
   *   (optional) The name of a field type, or NULL to retrieve all formatters.
   *
   * @return array
   *   If no field type is provided, returns a nested array of all formatters,
   *   keyed by field type.
   */
  public function getOptions($field_type = NULL) {
    if (!isset($this->formatterOptions)) {
      $options = [];
      $field_types = $this->fieldTypeManager->getDefinitions();
      $definitions = $this->getDefinitions();
      uasort($definitions, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      foreach ($definitions as $name => $definition) {
        // Empty field_types means that applicable for all.
        if (empty($definition['field_types'])) {
          foreach (array_keys($field_types) as $formatter_field_type) {
            $options[$formatter_field_type][$name] = $definition['label'];
          }
        }
        foreach ($definition['field_types'] as $formatter_field_type) {
          // Check that the field type exists.
          if (isset($field_types[$formatter_field_type])) {
            $options[$formatter_field_type][$name] = $definition['label'];
          }
        }
      }
      $this->formatterOptions = $options;
    }
    if ($field_type) {
      return !empty($this->formatterOptions[$field_type]) ? $this->formatterOptions[$field_type] : [];
    }
    return $this->formatterOptions;
  }

}

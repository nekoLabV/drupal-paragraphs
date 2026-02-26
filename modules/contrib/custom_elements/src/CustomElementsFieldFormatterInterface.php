<?php

namespace Drupal\custom_elements;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\PluginSettingsInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Interface for custom elements field formatters.
 *
 * When rendering an entity into a custom elements,
 * the custom elements field formatters are used
 * to build a tree of custom elements for the given field items.
 *
 * @todo Remove PluginSettingsInterface when
 *   EntityDisplayInterface:getRenderer() does not require it any more.
 *
 * @ingroup custom_element_formatter
 */
interface CustomElementsFieldFormatterInterface extends ConfigurableInterface, DependentPluginInterface, PluginFormInterface, PluginSettingsInterface {

  /**
   * Allows formatters to load information for field values being displayed.
   *
   * This should be used when a formatter needs to load additional information
   * from the database in order to render a field, for example a reference
   * field that displays properties of the referenced entities such as name or
   * type.
   *
   * This method operates on multiple entities. The $entities_items parameter
   * is an array keyed by entity ID. For performance reasons, information for
   * all involved entities should be loaded in a single query where possible.
   *
   * Changes or additions to field values are done by directly altering the
   * items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface[] $entities_items
   *   An array with the field values from the multiple entities being rendered.
   */
  public function prepareBuild(array $entities_items);

  /**
   * Adds formatted field items into a custom element.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values to build.
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   Custom element object.
   * @param string $langcode
   *   (optional) The language that should be used to render the field. Defaults
   *   to the current content language.
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL);

  /**
   * Returns if the formatter can be used for the provided field.
   *
   * @param string $plugin_id
   *   The formatter plugin ID.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition that should be checked.
   *
   * @return bool
   *   TRUE if the formatter can be used, FALSE otherwise.
   */
  public static function isApplicable(string $plugin_id, FieldDefinitionInterface $field_definition);

  /**
   * Returns a short summary for the current formatter settings.
   *
   * @return string[]
   *   A short summary of the formatter settings.
   */
  public function settingsSummary();

  /**
   * Allows a plugin to define whether it should be removed.
   *
   * If this method returns TRUE then the plugin should be removed.
   *
   * @param array $dependencies
   *   An array of dependencies that will be deleted keyed by dependency type.
   *   Dependency types are, for example, entity, module and theme.
   *
   * @return bool
   *   TRUE if the plugin instance should be removed.
   *
   * @see \Drupal\Core\Config\Entity\ConfigDependencyManager
   * @see \Drupal\Core\Config\ConfigEntityBase::preDelete()
   * @see \Drupal\Core\Config\ConfigManager::uninstall()
   * @see \Drupal\Core\Entity\EntityDisplayBase::onDependencyRemoval()
   */
  public function onDependencyRemoval(array $dependencies);

}

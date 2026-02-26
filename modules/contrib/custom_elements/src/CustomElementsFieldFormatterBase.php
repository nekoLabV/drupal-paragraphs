<?php

namespace Drupal\custom_elements;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for 'CustomElementsFieldFormatter' plugin implementations.
 *
 * We have to support PluginSettingsInterface. For that we map its API
 * for getting and retrieving settings to its new API: plugin configuration.
 * That way, plugin settings and configuration are synonmous.
 *
 * Furthermore, third-party-settings are not supported by default.
 *
 * @ingroup custom_element_formatter
 */
abstract class CustomElementsFieldFormatterBase extends PluginBase implements CustomElementsFieldFormatterInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @phpstan-ignore-next-line
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $entities_items) {
    // Nothing by default.
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(string $plugin_id, FieldDefinitionInterface $field_definition) {
    // By default, formatters are available for all fields.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Gets field definition object.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   The field definition of the field being formatted.
   */
  protected function getFieldDefinition() {
    // @see \Drupal\custom_elements\Entity\EntityCeDisplay::getRenderer()
    return $this->configuration['field_definition'];
  }

  /**
   * Gets the current view mode.
   *
   * @return string
   *   The view mode being rendered.
   */
  protected function getViewMode() {
    // @see \Drupal\custom_elements\Entity\EntityCeDisplay::getRenderer()
    return $this->configuration['view_mode'];
  }

  /**
   * Gets the display component's configured name.
   *
   * @return string
   *   The configured name of the slot or attribute to be added.
   */
  protected function getName() {
    return $this->configuration['name'] ?? '';
  }

  /**
   * Checks that item is a slot or not.
   *
   * @return bool
   *   Whether the item is a slot or not.
   */
  protected function isSlot() {
    return (bool) ($this->configuration['is_slot'] ?? FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Sets the configuration for this plugin instance.
   *
   * @param array $configuration
   *   An associative array containing the plugin's configuration, that must
   *   contain at least 'name', 'view_mode' and 'field_definition'.
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   *
   * DO NOT USE. Use non-static defaultConfiguration() instead.
   */
  public static function defaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * This duplicates getConfiguration(), because we have to implement both
   * ConfigurableInterface and PluginSettingsInterface.
   *
   * @see \Drupal\custom_elements\CustomElementsFieldFormatterBase::getConfiguration()
   */
  public function getSettings() {
    return $this->configuration ?? [];
  }

  /**
   * {@inheritdoc}
   *
   * This duplicates getConfiguration()[$key], provided a value for $key is
   * present in defaultConfiguration().
   *
   * @see \Drupal\custom_elements\CustomElementsFieldFormatterBase::getConfiguration()
   */
  public function getSetting($key) {
    // Unlike core field formatters, this base class has already merged default
    // configuration into $this->configuration.
    return $this->configuration[$key] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings) {
    // Prevent deleting base configuration values that are necessary for
    // operation / that get special treatment by EntityCeDisplay::getRenderer().
    $this->configuration = array_intersect_key($this->configuration, array_flip(
      ['field_definition', 'view_mode', 'name', 'is_slot']
    ));
    $this->configuration = array_merge($this->configuration, $settings);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSetting($key, $value) {
    $this->configuration[$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySettings($module = NULL) {
    // Note: Not supported by default, but core-field formatters need it.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySetting($module, $key, $default = NULL) {
    // Note: Not supported by default, but core-field formatters need it.
    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function setThirdPartySetting($module, $key, $value) {
    // Note: Not supported by default, but core-field formatters need it.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetThirdPartySetting($module, $key) {
    // Note: Not supported by default, but core-field formatters need it.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartyProviders() {
    // Note: Not supported by default, but core-field formatters need it.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    // Note: Not supported by default, but core-field formatters need it.
    return FALSE;
  }

}

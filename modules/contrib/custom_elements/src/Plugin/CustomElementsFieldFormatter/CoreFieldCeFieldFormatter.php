<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;

/**
 * Implementation for core field formatters.
 *
 * @CustomElementsFieldFormatter(
 *   id = "field",
 *   deriver = "Drupal\custom_elements\Plugin\CustomElementsFieldFormatter\CoreFieldCeFieldFormatterDeriver"
 * )
 */
class CoreFieldCeFieldFormatter extends CustomElementsFieldFormatterBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The core field formatter manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Construct.
   *
   * @param object $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param object $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Field\FormatterPluginManager $formatterManager
   *   Custom element generator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, FormatterPluginManager $formatterManager, LanguageManagerInterface $languageManager, RendererInterface $renderer, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formatterManager = $formatterManager;
    $this->languageManager = $languageManager;
    $this->renderer = $renderer;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.field.formatter'),
      $container->get('language_manager'),
      $container->get('renderer'),
      $container->get('module_handler')
    );
  }

  /**
   * Gets the instance of the configured field formatter.
   *
   * @return \Drupal\Core\Field\FormatterInterface
   *   The configured field formatter instance.
   */
  protected function getCoreFormatter() {
    // Get the formatter ID out of the plugin ID. Derivative plugins have IDs
    // in the form of "field:<formatter_id>".
    [, $formatter_id] = explode(':', $this->getPluginId());
    return $this->formatterManager->getInstance([
      'field_definition' => $this->getFieldDefinition(),
      'view_mode' => $this->getViewMode(),
      'configuration' => [
        'type' => $formatter_id,
        'label' => 'hidden',
        'settings' => $this->configuration['settings'] ?? [],
        'third_party_settings' => $this->configuration['third_party_settings'] ?? [],
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $entities_items) {
    $this->getCoreFormatter()->prepareView($entities_items);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    // @see \Drupal\Core\Field\FormatterBase::view()
    // Default the language to the current content language.
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    }
    $elements = $this->getCoreFormatter()->viewElements($items, $langcode);

    // Field item lists, in particular for computed fields, may carry cacheable
    // metadata which must be bubbled.
    if ($items instanceof CacheableDependencyInterface) {
      $custom_element->addCacheableDependency($items);
    }
    if ($this->isSlot()) {
      $custom_element->setSlotFromRenderArray($this->getName(), $elements);
    }
    else {
      // Render into markup and set the string value as attribute.
      /** @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = $this->renderer;
      $markup = DeprecationHelper::backwardsCompatibleCall(
        currentVersion: \Drupal::VERSION,
        deprecatedVersion: '10.3',
        currentCallable: fn() => $renderer->renderInIsolation($elements),
        deprecatedCallable: fn() => $renderer->renderPlain($elements),
      );
      $custom_element->setAttribute($this->getName(), (string) $markup);
      $custom_element->addCacheableDependency(CacheableMetadata::createFromRenderArray($elements));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $formatter = $this->getCoreFormatter();
    // Core field formatter forms are used to getting the complete form.
    // So let's give it to them.
    if ($form_state instanceof SubformState) {
      $form_state = $form_state->getCompleteFormState();
    }
    $form['settings'] = $formatter->settingsForm($form, $form_state);
    // @see \Drupal\field_ui\Form\EntityViewDisplayEditForm::thirdPartySettingsForm
    $this->moduleHandler->invokeAllWith(
      'field_formatter_third_party_settings_form',
      function (callable $hook, string $module) use (&$formatter, &$form, &$form_state) {
        $form['settings'][$module] = $hook(
          $formatter,
          $this->getFieldDefinition(),
          $this->getViewMode(),
          $form,
          $form_state,
        );
      }
    );
    // If "empty_fields" module is installed, drop it. It does not work CE
    // display, since it assume a regular entity-rendering pipeline.
    unset($form['settings']['empty_fields']);
    // If neither settingsForm() nor any hook added settings, then don't
    // return 'settings' as an empty array (which means nothing in a form).
    if (empty($form['settings'])) {
      unset($form['settings']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // @see \Drupal\field_ui\Form\EntityDisplayFormBase::copyFormValuesToEntity
    // Core field formatters simply copy over the relevant form values as
    // part of submission, so we need to do the same here.
    $values = $form_state->getValues();
    $default_settings = $this->getCoreFormatter()->defaultSettings();
    $this->configuration['settings'] = isset($values['settings']) ? array_intersect_key($values['settings'], $default_settings) : [];
    $this->configuration['third_party_settings'] = $values['third_party_settings'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(string $plugin_id, FieldDefinitionInterface $field_definition) {
    [, $formatter_id] = explode(':', $plugin_id);
    $definition = \Drupal::service('plugin.manager.field.formatter')->getDefinition($formatter_id);
    if ($definition) {
      $plugin_class = DefaultFactory::getPluginClass($formatter_id, $definition);
      return $plugin_class::isApplicable($field_definition);
    }
    return FALSE;
  }

  /**
   * Returns the array of settings, not including defaults for missing settings.
   *
   * CE field formatters which wrap Core field formatters, unlike others,
   * return a different value vs getConfiguration(); this method returns only
   * the 'settings' sub array, i.e. the settings for the Core field formatter.
   */
  public function getSettings() {
    return $this->configuration['settings'] ?? [];
  }

  /**
   * Returns the value of a setting, but not its default value if absent.
   */
  public function getSetting($key) {
    return $this->configuration['settings'][$key] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings(array $settings) {
    $this->configuration['settings'] = $settings;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setSetting($key, $value) {
    $this->configuration['settings'][$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->getCoreFormatter()->settingsSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySettings($module = NULL) {
    return $this->getCoreFormatter()->getThirdPartySettings($module);
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartySetting($module, $key, $default = NULL) {
    return $this->getCoreFormatter()->getThirdPartySettings($module, $key, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function setThirdPartySetting($module, $key, $value) {
    return $this->getCoreFormatter()->setThirdPartySetting($module, $key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function unsetThirdPartySetting($module, $key) {
    return $this->getCoreFormatter()->unsetThirdPartySetting($module, $key);
  }

  /**
   * {@inheritdoc}
   */
  public function getThirdPartyProviders() {
    return $this->getCoreFormatter()->getThirdPartyProviders();
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    return $this->getCoreFormatter()->onDependencyRemoval($dependencies);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return $this->getCoreFormatter()->calculateDependencies();
  }

}

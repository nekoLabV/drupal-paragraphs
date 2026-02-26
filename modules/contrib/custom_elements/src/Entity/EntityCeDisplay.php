<?php

namespace Drupal\custom_elements\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityDisplayBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultLazyPluginCollection as DefaultLazyPluginCollectionAlias;
use Drupal\custom_elements\CustomElementGeneratorTrait;

/**
 * Custom element display configuration entity.
 *
 * Contains custom element display options for all components of a rendered
 * entity in a given view mode.
 *
 * @ConfigEntityType(
 *   id = "entity_ce_display",
 *   label = @Translation("Entity custom element display"),
 *   entity_keys = {
 *     "id" = "id",
 *     "status" = "status"
 *   },
 *   handlers = {
 *     "access" = "\Drupal\custom_elements\Entity\Access\EntityCeDisplayAccessControlHandler",
 *   },
 *   config_export = {
 *     "id",
 *     "targetEntityType",
 *     "bundle",
 *     "mode",
 *     "customElementName",
 *     "useLayoutBuilder",
 *     "forceAutoProcessing",
 *     "content",
 *   }
 * )
 */
class EntityCeDisplay extends EntityDisplayBase implements EntityCeDisplayInterface {

  use CustomElementGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  protected $displayContext = 'view';

  /**
   * Whether this display is enabled or not.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * Custom element name to be displayed.
   *
   * @var string
   */
  protected string $customElementName = '';

  /**
   * Whether to build using layout (if enabled in appropriate display).
   *
   * @var bool
   */
  protected bool $useLayoutBuilder = FALSE;

  /**
   * Whether to build using processors instead of display components.
   *
   * @var bool
   */
  protected bool $forceAutoProcessing = FALSE;

  /**
   * The entity display repository.
   */
  protected EntityDisplayRepositoryInterface $entityDisplayRepository;

  /**
   * The entity field manager.
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The module handler.
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    // Parent constructor requires plugin manager, so don't use 'lazy' getter.
    $this->pluginManager = \Drupal::service('custom_elements.plugin.manager.field.custom_element_formatter');
    parent::__construct($values, $entity_type);
  }

  /**
   * Sets the originally requested view mode, when building a CE display.
   *
   * @deprecated in custom_elements:3.0.1 and is removed from
   *   custom_elements:4.0.0. use set('originalMode', $viewMode).
   * @see https://www.drupal.org/project/custom_elements/issues/3484476
   */
  public function setOriginalMode(string $viewMode): self {
    $this->originalMode = $viewMode;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUseLayoutBuilder(): bool {
    return !empty($this->useLayoutBuilder);
  }

  /**
   * {@inheritdoc}
   */
  public function setUseLayoutBuilder(bool $status): self {
    $this->useLayoutBuilder = $status;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getForceAutoProcessing(): bool {
    return !empty($this->forceAutoProcessing);
  }

  /**
   * {@inheritdoc}
   */
  public function setForceAutoProcessing(bool $status): self {
    $this->forceAutoProcessing = $status;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomElementName(): string {
    return $this->customElementName;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomElementName($name): self {
    $this->set('customElementName', $name);
    return $this;
  }

  /**
   * Gets the formatter for a CE display component.
   *
   * @param string $field_name
   *   The name for the CE display component. NOTE this not the field name;
   *   it's only called $field_name because of the existing
   *   EntityDisplayInterfaceinterface. (Other implementations also effectively
   *   pass the 'component name', because the only components are fields.)
   * @param bool $get_actual_field_name
   *   (Optional) Actually DO treat $field_name as a field name. DO NOT USE
   *   THIS; it's a temporary measure to support 'hidden'/new field rows in the
   *   UI, until they are removed / redone as component rows, in #3446485.
   *
   * @return \Drupal\custom_elements\CustomElementsFieldFormatterInterface|null
   *   A formatter plugin or NULL if the component does not exist.
   *
   * @todo remove $get_actual_field_name in #3446485.
   *
   * @internal
   *    This method's return value may still be widened as other (non-field)
   *    plugin types are added.
   */
  public function getRenderer($field_name, bool $get_actual_field_name = FALSE) {
    if (isset($this->plugins[$field_name])) {
      return $this->plugins[$field_name];
    }
    $component = NULL;
    if (!$get_actual_field_name) {
      $component_name = $field_name;
      $component = $this->getComponent($component_name);
    }

    // Instantiate the formatter object from the stored display properties.
    $formatter = NULL;
    if ($component && isset($component['formatter'])) {
      // @todo When implementing static properties per #3446287, this code
      //   should know not to get a field name/definition, and the plugin
      //   manager should return something that is not a
      //   CustomElementsFieldFormatterInterface. Distinguish by the
      //   'formatter' property having a prefix + colon.
      // This is the actual field name.
      if (!isset($component['field_name'])) {
        // @todo Improve logging?
        return NULL;
      }
      $definition = $this->getFieldDefinition($component['field_name']);
      if ($definition) {
        $component += ['configuration' => [], 'name' => $component_name, 'is_slot' => FALSE];
        $formatter = $this->pluginManager->createInstance($component['formatter'], [
          'field_definition' => $definition,
          'view_mode' => $this->originalMode,
          'name' => $component['name'],
          'is_slot' => $component['is_slot'],
        ] + $component['configuration']
        );
      }
    }

    // Persist the formatter object.
    $this->plugins[$field_name] = $formatter;
    return $formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    $configurations = [];
    foreach ($this->getComponents() as $component_name => $component) {
      // @todo see getRenderer() when implementing #3446287; if() may need to
      //   change.
      if (isset($component['field_name'])) {
        $field_name = $component['field_name'];
        if (!empty($component['formatter']) && ($field_definition = $this->getFieldDefinition($field_name))) {
          $component += ['configuration' => [], 'name' => $component_name, 'is_slot' => FALSE];
          $configurations[$field_name] = [
            'id' => $component['formatter'],
            'field_definition' => $field_definition,
            'view_mode' => $this->originalMode,
            'name' => $component['name'],
            'is_slot' => $component['is_slot'],
          ] + $component['configuration'];
        }
      }
    }
    return [
      'formatters' => new DefaultLazyPluginCollectionAlias($this->pluginManager, $configurations),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    $tags = parent::getCacheTagsToInvalidate();
    // $tags is a single tag, ending in the view mode. When using the default
    // display to build a specific view mode, also add a tag for that view mode.
    $tag = current($tags);
    if ($this->originalMode !== 'default' && substr($tag, -8) === '.default') {
      $tags[] = substr($tag, 0, strlen($tag) - 7) . $this->originalMode;
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    // Override parent method: we don't need its display defaults.
    if (!$this->targetEntityType) {
      return;
    }

    if (!$this->getCustomElementName()) {
      $custom_element = $this->getCustomElementGenerator()->getViewModeDefaults($this->targetEntityType, $this->bundle, $this->mode);
      $this->setCustomElementName($custom_element->getPrefixedTag());
    }

    $initialized = !empty($this->content) || $this->forceAutoProcessing || $this->getUseLayoutBuilder();
    if (!$initialized) {
      // Enable components that are enabled in the corresponding entity view
      // display, which can be a display for our own view mode or if that isn't
      // enabled, for the default view mode. This is necessary for proper
      // building/generating of custom elements. (When copying a new non-default
      // display from a default one, this is overwritten by createCopy().)
      $entity_view_display = $this->loadEntityViewDisplay($this->mode, TRUE);
      $field_definitions = $this->getFieldDefinitions();

      // @todo Add support for statically set values.
      // Enable every component with "auto" that is enabled in the display.
      foreach ($entity_view_display->getComponents() as $field_name => $component) {
        // Ignore extra-fields.
        if (isset($field_definitions[$field_name])) {
          $component_name = str_starts_with($field_name, 'field_') ? substr($field_name, strlen('field_')) : $field_name;
          // @todo Generalize the solution over at issue #3550120.
          $is_canvas = $field_definitions[$field_name]->getType() == 'component_tree';
          $this->setComponent($component_name, [
            'field_name' => $field_name,
            'formatter' => $is_canvas ? 'canvas' : 'auto',
            'weight' => $component['weight'],
            'is_slot' => str_starts_with($field_definitions[$field_name]->getType(), 'text') || $is_canvas ? 1 : 0,
          ]);
        }
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function createCopy($mode) {
    $copy = parent::createCopy($mode);
    $custom_element = $this->getCustomElementGenerator()->getViewModeDefaults($this->targetEntityType, $this->bundle, $mode);
    $copy->setCustomElementName($custom_element->getPrefixedTag());
    if ($copy->getUseLayoutBuilder()) {
      // Disable LB if the corresponding entity view display does not have it
      // enabled. The 'enabled' value is ignored, but is still confusing.
      $display = $this->loadEntityViewDisplay($mode);
      if (!$display || !$display->getThirdPartySetting('layout_builder', 'enabled')) {
        $copy->setUseLayoutBuilder(FALSE);
      }
    }
    return $copy;
  }

  /**
   * {@inheritdoc}
   */
  public function setComponent($name, array $options = []) {
    // If no weight specified, make sure the field sinks at the bottom.
    if (!isset($options['weight'])) {
      $max = $this->getHighestWeight();
      $options['weight'] = isset($max) ? $max + 1 : 0;
    }
    $this->content[$name] = $options;
    unset($this->plugins[$name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeComponent($name) {
    // Skip over parent method, to ignore $this->hidden.
    unset($this->content[$name]);
    unset($this->plugins[$name]);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // Skip over parent method: we don't have regions. Only sort content.
    ksort($this->content);
    ConfigEntityBase::preSave($storage);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    // Skip over parent method: all fields' display is configurable, no field
    // must be disallowed because of its definitions.
    return ConfigEntityBase::toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // Skip over parent method: fields need to be calculated differently.
    ConfigEntityBase::calculateDependencies();

    // Depend on the bundle.
    $target_entity_type = $this->entityTypeManager()->getDefinition($this->targetEntityType);
    $bundle_config_dependency = $target_entity_type->getBundleConfigDependency($this->bundle);
    $this->addDependency($bundle_config_dependency['type'], $bundle_config_dependency['name']);

    // Depend on fields: names are in 'field_name' properties instead of keys.
    if ($this->getModuleHandler()->moduleExists('field')) {
      $fieldnames_as_keys = array_flip(array_filter(array_map(
        fn($component) => $component['field_name'] ?? NULL,
        $this->getComponents()
      )));
      $field_definitions = $this->getEntityFieldManager()->getFieldDefinitions($this->targetEntityType, $this->bundle);
      foreach (array_intersect_key($field_definitions, $fieldnames_as_keys) as $field_definition) {
        if ($field_definition instanceof ConfigEntityInterface && $field_definition->getEntityTypeId() == 'field_config') {
          $this->addDependency('config', $field_definition->getConfigDependencyName());
        }
      }
    }

    // Depend on configured modes.
    if ($this->mode != 'default') {
      $mode_entity = $this->entityTypeManager()->getStorage('entity_' . $this->displayContext . '_mode')->load($target_entity_type->id() . '.' . $this->mode);
      $this->addDependency('config', $mode_entity->getConfigDependencyName());
    }

    // Depend on related entity view displays.
    foreach ($this->getConfigDependencyEntityViewDisplaysInternal() as $display) {
      $this->addDependency('config', $display->getConfigDependencyName());
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = FALSE;
    foreach ($dependencies['config'] as $entity) {
      if ($entity->getEntityTypeId() == 'field_config') {
        // Find and remove components that use this field.
        $field_name = $entity->getName();
        foreach ($this->getComponents() as $component_name => $component) {
          if (isset($component['field_name']) && $component['field_name'] === $field_name) {
            $this->removeComponent($component_name);
            $changed = TRUE;
          }
        }
      }
    }
    // Let parent handle other dependencies (modules, plugins, etc.).
    return parent::onDependencyRemoval($dependencies) || $changed;
  }

  /**
   * Gets the entity view displays that are this entity's config dependencies.
   *
   * @return \Drupal\Core\Entity\Display\EntityDisplayInterface[]
   *   Entity views displays which this CE display depends on.
   *
   * @deprecated in custom_elements:3.0.1 and is removed from
   *   custom_elements:4.0.0. There's no public replacement.
   * @see https://www.drupal.org/project/custom_elements/issues/3475342
   */
  public function getConfigDependencyEntityViewDisplays(): array {
    return $this->getConfigDependencyEntityViewDisplaysInternal();
  }

  /**
   * Gets the entity view displays that are this entity's config dependencies.
   *
   * @return \Drupal\Core\Entity\Display\EntityDisplayInterface[]
   *   Entity views displays which this CE display depends on.
   */
  private function getConfigDependencyEntityViewDisplaysInternal(): array {
    $displays = [];
    if ($this->getUseLayoutBuilder()) {
      // Building a custom element depends on the "use layout builder" setting
      // in corresponding entity view displays. Note that, if that setting is
      // disabled, this CE display's useLayoutBuilder property is ignored.
      // There can be two ways of reasoning about this:
      // - This situation is exactly the same as if the entity view display
      //   didn't exist, therefore such an entity view display is not a
      //   dependency. (Just like we cannot declare a dependency on a
      //   nonexistent entity view display object.)
      // - If the setting is re-enabled, this influences the output (because
      //   useLayoutBuilder property is not ignored anymore), therefore such an
      //   entity view display IS a dependency.
      // We choose the latter.
      if ($this->mode !== 'default') {
        // This entity is used when building a custom element using our own
        // view mode; this uses either the entity view display with our view
        // mode (preferred), or the default. Depend only on that display.
        // Disabled displays are ignored. (If no displays are enabled, Core
        // auto-generates the default ones, so we have 0 dependencies.)
        $display = $this->loadEntityViewDisplay();
        $displays = $display ? [$display] : [];
      }
      else {
        // This entity is used for all view modes that have no own CE display,
        // so all corresponding entity view displays are also dependencies, if
        // enabled.
        $query = $this->getEntityQuery('entity_view_display')
          ->condition('id', $this->targetEntityType . '.' . $this->bundle . '.', 'STARTS_WITH')
          ->condition('status', TRUE)
          ->condition('id', $this->targetEntityType . '.' . $this->bundle . '.default', '<>');
        $other_active_display_ids = $query->execute();
        if ($other_active_display_ids) {
          // Filter out view modes with their own CE display.
          $active_ce_display_ids = $this->getEntityQuery('entity_ce_display')
            ->condition('id', $this->targetEntityType . '.' . $this->bundle . '.', 'STARTS_WITH')
            ->condition('status', TRUE)
            ->execute();
          $other_active_display_ids = array_diff($other_active_display_ids, $active_ce_display_ids);
          if ($other_active_display_ids) {
            $storage = $this->entityTypeManager()->getStorage('entity_view_display');
            $displays += $storage->loadMultiple($other_active_display_ids);
          }
        }
      }
    }
    return $displays;
  }

  /**
   * Loads an entity view display containing settings used by this CE display.
   *
   * @param string $mode
   *   (Optional) view mode. Default is the current view mode (note, not the
   *   'requested' view mode during the build/render phase).
   * @param bool $default_if_not_exists
   *   (Optional) If the display is not enabled (for either the view mode or
   *   "default"), return a default display instead of NULL.
   *
   * @return \Drupal\Core\Entity\Entity\EntityViewDisplay|null
   *   The display, or NULL if none exist in active config / none are enabled,
   *   and $default_if_not_exist is false.
   */
  protected function loadEntityViewDisplay(string $mode = '', bool $default_if_not_exists = FALSE): ?EntityViewDisplay {
    // entityDisplayRepository::>getViewDisplay() cannot check if a display
    // actually exists, so do 'generic' loading.
    // Optimization: possibly load two at once, instead of doing two loads.
    $displays = $this->entityTypeManager()
      ->getStorage('entity_view_display')
      ->loadMultiple([
        $this->targetEntityType . '.' . $this->bundle . '.' . ($mode ?: $this->mode),
        $this->targetEntityType . '.' . $this->bundle . '.default',
      ]);
    // Disabled displays are ignored. (If no displays are enabled, Core
    // auto-generates the default ones, so we have 0 dependencies.)
    $displays = array_filter(
      $displays,
      fn($display) => $display->status()
    );
    if (count($displays) > 1) {
      unset($displays[$this->targetEntityType . '.' . $this->bundle . '.default']);
    }
    if (!$displays && $default_if_not_exists) {
      // Return a dynamically created default display. (There is no difference
      // with a '$mode' display, in terms of the fields that are initialized.
      // Do default, only because it feels a bit strange to be able to create
      // displays for nonexistent view modes.)
      $displays = [
        $this->getEntityDisplayRepository()
          ->getViewDisplay($this->targetEntityType, $this->bundle, 'default'),
      ];
    }

    return reset($displays) ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldDefinitions() {
    // Override parent method: do not filter field definitions, all fields'
    // display is configurable.
    if (!isset($this->fieldDefinitions)) {
      $this->fieldDefinitions = $this->getEntityFieldManager()->getFieldDefinitions($this->targetEntityType, $this->bundle);
    }

    return $this->fieldDefinitions;
  }

  /**
   * Gets an entity query.
   *
   * @param string $entity_type
   *   The entity type for which the query object should be returned.
   */
  protected function getEntityQuery($entity_type): QueryInterface {
    return $this->entityTypeManager()->getStorage($entity_type)->getQuery();
  }

  /**
   * Gets the entity field manager.
   */
  protected function getEntityFieldManager(): EntityFieldManagerInterface {
    if (!isset($this->entityFieldManager)) {
      $this->entityFieldManager = \Drupal::service('entity_field.manager');
    }
    return $this->entityFieldManager;
  }

  /**
   * Gets the entity display repository.
   */
  protected function getEntityDisplayRepository(): EntityDisplayRepositoryInterface {
    if (!isset($this->entityDisplayRepository)) {
      $this->entityDisplayRepository = \Drupal::service('entity_display.repository');
    }
    return $this->entityDisplayRepository;
  }

  /**
   * Gets the module handler.
   */
  protected function getModuleHandler(): ModuleHandlerInterface {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

}

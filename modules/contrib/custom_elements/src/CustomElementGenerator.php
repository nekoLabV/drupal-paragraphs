<?php

namespace Drupal\custom_elements;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\custom_elements\Entity\EntityCeDisplayInterface;
use Drupal\custom_elements\Processor\CustomElementProcessorInterface;

/**
 * Service to preprocess template variables for custom elements.
 */
class CustomElementGenerator {

  use CustomElementsBlockRenderHelperTrait;
  use CustomElementsProcessorFieldUtilsTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Array of all processors and their priority.
   *
   * @var array
   */
  protected $processorByPriority = [];

  /**
   * Sorted list of registered processors.
   *
   * @var \Drupal\custom_elements\Processor\CustomElementProcessorInterface[]
   */
  protected $sortedProcessors;

  /**
   * CustomElementGenerator constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, EntityRepositoryInterface $entity_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->moduleHandler = $moduleHandler;
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Adds a processor.
   *
   * @param \Drupal\custom_elements\Processor\CustomElementProcessorInterface $processor
   *   The processor to add.
   * @param int $priority
   *   The priority for the processor.
   */
  public function addProcessor(CustomElementProcessorInterface $processor, $priority = 0) {
    $this->processorByPriority[$priority][] = $processor;
    // Force the processors to be re-sorted.
    $this->sortedProcessors = NULL;
  }

  /**
   * Gets an array of processors, sorted by their priority.
   *
   * @return \Drupal\custom_elements\Processor\CustomElementProcessorInterface[]
   *   Returns sorted processors.
   */
  public function getSortedProcessors() {
    if (!isset($this->sortedProcessors)) {
      // Sort the processors according to priority.
      krsort($this->processorByPriority);

      // Merge nested processors from $this->processors
      // into $this->sortedProviders.
      $this->sortedProcessors = [];
      foreach ($this->processorByPriority as $processors) {
        $this->sortedProcessors = array_merge($this->sortedProcessors, $processors);
      }
    }
    return $this->sortedProcessors;
  }

  /**
   * Gets the defaults to apply for the given entity type, bundle and view mode.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   A new custom element with the defaults set.
   */
  public function getViewModeDefaults(string $entityType, string $bundle, string $viewMode) {
    $bundle_key = $this->entityTypeManager->getDefinition($entityType)
      ->getKey('bundle');
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->entityTypeManager->getStorage($entityType)
      ->create($bundle_key && $bundle ? [$bundle_key => $bundle] : []);

    return $this->getEntityDefaults($entity, $viewMode);
  }

  /**
   * Gets the defaults to apply for the given entity and view mode.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $viewMode
   *   The view mode.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   A new custom element with the defaults set.
   */
  public function getEntityDefaults(ContentEntityInterface $entity, $viewMode) {
    $custom_element = new CustomElement();
    $custom_element->addCacheableDependency($entity);

    // By default output tags like {entity}-{bundle}-{view_mode} or
    // {entity}-{view_mode} if the entity type does not make use of
    // bundles.
    $tag = $entity->getEntityTypeId();
    if (!empty($entity->getEntityType()->getKeys()['bundle'])) {
      $tag .= '-' . $entity->bundle();
    }
    if ($viewMode != 'default') {
      $tag .= '-' . $viewMode;
    }
    $custom_element->setTag($tag);
    $custom_element->setTagPrefix('');

    // Allow altering the element for the given before and after.
    $this->moduleHandler->alter('custom_element_entity_defaults', $custom_element, $entity, $viewMode);
    return $custom_element;
  }

  /**
   * Generates a custom element from entity, possibly after translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to process.
   * @param string $viewMode
   *   View mode used for rendering field values into slots.
   * @param string|null $langcode
   *   (optional) For which language the entities should be rendered. Defaults
   *   to the current content language.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access when rendering fields.
   *   Defaults to the current user.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Custom element containing entity properties as attributes and slots.
   */
  public function generate(ContentEntityInterface $entity, string $viewMode, ?string $langcode = NULL, ?AccountInterface $account = NULL) {
    $custom_elements = $this->generateMultiple([$entity], $viewMode, $langcode, $account);
    return current($custom_elements);
  }

  /**
   * Generates custom elements from entities, possibly after translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   Entities to process.
   * @param string $viewMode
   *   View mode used for rendering field values into slots.
   * @param string|null $langcode
   *   (optional) For which language the entities should be rendered. Defaults
   *   to the current content language.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access when rendering fields.
   *   Defaults to the current user.
   *
   * @return \Drupal\custom_elements\CustomElement[]
   *   Custom elements containing entities' properties as attributes and slots,
   *   keyed by the same value as the corresponding entities.
   */
  public function generateMultiple(array $entities, string $viewMode, ?string $langcode = NULL, ?AccountInterface $account = NULL): array {
    foreach ($entities as &$entity) {
      $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);
    }

    return $this->buildEntityContent($entities, $viewMode, $account);
  }

  /**
   * Generates custom elements from entities.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   Entities to process.
   * @param string $viewMode
   *   View mode used for rendering field values into slots.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user.
   *
   * @return \Drupal\custom_elements\CustomElement[]
   *   Custom elements containing entities' properties as attributes and slots,
   *   keyed by the same value as the corresponding entities.
   */
  public function buildEntityContent(array $entities, string $viewMode, ?AccountInterface $account = NULL): array {
    return $this->doBuildEntityContent($entities, $viewMode, NULL, $account);
  }

  /**
   * Generates a custom element from entity, possibly after translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to process.
   * @param \Drupal\custom_elements\Entity\EntityCeDisplayInterface $display
   *   View mode or ce display used for rendering field values into slots.
   * @param string|null $langcode
   *   (optional) For which language the entities should be rendered. Defaults
   *   to the current content language.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access when rendering fields.
   *   Defaults to the current user.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Custom element containing entity properties as attributes and slots.
   */
  public function generateWithCeDisplay(ContentEntityInterface $entity, EntityCeDisplayInterface $display, ?string $langcode = NULL, ?AccountInterface $account = NULL) {
    $custom_elements = $this->generateMultipleWithCeDisplay([$entity], $display, $langcode, $account);
    return current($custom_elements);
  }

  /**
   * Generates custom elements from entities, possibly after translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   Entities to process.
   * @param \Drupal\custom_elements\Entity\EntityCeDisplayInterface $display
   *   View mode or ce display used for rendering field values into slots.
   * @param string|null $langcode
   *   (optional) For which language the entities should be rendered. Defaults
   *   to the current content language.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access when rendering fields.
   *   Defaults to the current user.
   *
   * @return \Drupal\custom_elements\CustomElement[]
   *   Custom elements containing entities' properties as attributes and slots,
   *   keyed by the same value as the corresponding entities.
   */
  public function generateMultipleWithCeDisplay(array $entities, EntityCeDisplayInterface $display, ?string $langcode = NULL, ?AccountInterface $account = NULL): array {
    foreach ($entities as &$entity) {
      $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);
    }

    return $this->buildEntityContentWithCeDisplay($entities, $display, $account);
  }

  /**
   * Generates custom elements from entities.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   Entities to process.
   * @param \Drupal\custom_elements\Entity\EntityCeDisplayInterface $ce_display
   *   View mode or ce display used for rendering field values into slots.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user.
   *
   * @return \Drupal\custom_elements\CustomElement[]
   *   Custom elements containing entities' properties as attributes and slots,
   *   keyed by the same value as the corresponding entities.
   */
  public function buildEntityContentWithCeDisplay(array $entities, EntityCeDisplayInterface $ce_display, ?AccountInterface $account = NULL): array {
    return $this->doBuildEntityContent($entities, $ce_display->getMode(), $ce_display, $account);
  }

  /**
   * Core implementation for building entity content into custom elements.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   Entities to process.
   * @param string $viewMode
   *   The requested view mode.
   * @param \Drupal\custom_elements\Entity\EntityCeDisplayInterface|null $provided_display
   *   (optional) A CE display to use. If matches entity type/bundle,
   *   it will be used. Otherwise, appropriate displays will be loaded.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user.
   *
   * @return \Drupal\custom_elements\CustomElement[]
   *   Custom elements containing entities' properties as attributes and slots,
   *   keyed by the same value as the corresponding entities.
   */
  private function doBuildEntityContent(array $entities, string $viewMode, ?EntityCeDisplayInterface $provided_display, ?AccountInterface $account): array {
    // Process custom elements by bundle, because most CE displays require
    // components to be prepared by bundle. Retain original order of entities
    // in the return value.
    $custom_elements = [];
    $entities_by_type_bundle_key = [];

    foreach ($entities as $key => $entity) {
      $entities_by_type_bundle_key[$entity->getEntityTypeId()][$entity->bundle()][$key] = $entity;
      $custom_elements[$key] = $this->getEntityDefaults($entity, $viewMode);
    }

    foreach ($entities_by_type_bundle_key as $entity_type_id => $entities_by_bundle_key) {
      foreach ($entities_by_bundle_key as $bundle => $entities) {
        // Load the appropriate CE display for this entity type/bundle.
        if ($provided_display
            && $provided_display->getTargetEntityTypeId() === $entity_type_id
            && $provided_display->getTargetBundle() === $bundle) {
          $ce_display = $provided_display;
        }
        elseif (!$provided_display) {
          $ce_display = $this->getEntityCeDisplay($entity_type_id, $bundle, $viewMode);
          $ce_display->set('originalMode', $viewMode);
        }
        else {
          throw new \LogicException('Either appropriate CE display or view mode should be provided.');
        }

        // Set the custom element name and add cacheable dependencies.
        $ce_name = $ce_display->getCustomElementName();
        foreach ($entities as $key => $entity) {
          $custom_elements[$key]->addCacheableDependency($ce_display);
          $custom_elements[$key]->setTag($ce_name);
        }

        if ($entity_view_display = $this->checkLayoutBuilderDisplay($ce_display, current($entities))) {
          // Build the layout builder content for the entities.
          foreach ($entities as $key => $entity) {
            $this->buildLayoutBuilderContent($entity, $custom_elements[$key], $entity_view_display);
          }
          // Additionally process fields according to CE display.
          $this->buildEntityComponentFields($entities, $custom_elements, $ce_display, $account);
        }
        elseif ($ce_display->getForceAutoProcessing()) {
          // Only do auto-processing on entity-level; skip processing of
          // display components. This was default behaviour in 2.x.
          foreach ($entities as $key => $entity) {
            $this->process($entity, $custom_elements[$key], $viewMode);
          }
        }
        else {
          // Build entity components, one by one, for this bundle's entities.
          $this->buildEntityComponentFields($entities, $custom_elements, $ce_display, $account);
        }

        foreach ($entities as $key => $entity) {
          $this->moduleHandler->alter('custom_element_entity', $custom_elements[$key], $entity, $viewMode);
        }
      }
    }

    return $custom_elements;
  }

  /**
   * Builds entity component fields for the given entities.
   *
   * @param array $entities
   *   The entities to process.
   * @param array $custom_elements
   *   The custom elements to update. Keys must match the keys in $entities.
   * @param \Drupal\custom_elements\Entity\EntityCeDisplayInterface $ce_display
   *   The CE display to use.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user for which to check access.
   */
  protected function buildEntityComponentFields(array $entities, array $custom_elements, EntityCeDisplayInterface $ce_display, ?AccountInterface $account = NULL) {
    foreach ($ce_display->getComponents() as $component_name => $display_component) {
      if ($formatter = $ce_display->getRenderer($component_name)) {
        $grouped_items = [];
        $field_name = $display_component['field_name'];
        foreach ($entities as $key => $entity) {
          if ($this->fieldIsAccessible($entity, $field_name, $custom_elements[$key], $account)) {
            $items = $entity->get($field_name);
            $items->filterEmptyItems();
            $grouped_items[$key] = $items;
          }
        }
        if ($grouped_items) {
          $formatter->prepareBuild($grouped_items);

          foreach ($grouped_items as $key => $items) {
            $formatter->build($items, $custom_elements[$key]);
          }
        }
      }
    }
  }

  /**
   * Processes the given data and adds it to the custom element.
   *
   * @param mixed $data
   *   The data.
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element to which to add it.
   * @param string $viewMode
   *   The current view mode.
   * @param string $key
   *   (Optional) Name to use for adding data into (a slot / attribute in) the
   *   custom element. Processors can use this how they see fit. Most use it
   *   as a literal key value; some may use it as e.g. a key prefix to add
   *   several values.
   */
  public function process($data, CustomElement $custom_element, $viewMode, $key = '') {
    foreach ($this->getSortedProcessors() as $processor) {
      if ($processor->supports($data, $viewMode)) {
        $processor->addtoElement($data, $custom_element, $viewMode, $key);
        break;
      }
    }
  }

  /**
   * Checks if both CE and Core display enable Layout Builder;.
   */
  private function checkLayoutBuilderDisplay(EntityCeDisplayInterface $ceDisplay, ContentEntityInterface $entity): ?CustomElementsLayoutBuilderEntityViewDisplay {
    if ($ceDisplay->getUseLayoutBuilder()) {
      // 'Use layout builder' must also be enabled on the corresponding core
      // display (for view mode or default).
      $entity_view_displays = EntityViewDisplay::collectRenderDisplays([$entity], $ceDisplay->getOriginalMode());
      $entity_view_display = $entity_view_displays[$entity->bundle()];
      if ($entity_view_display->getThirdPartySetting('layout_builder', 'enabled')) {
        assert($entity_view_display instanceof CustomElementsLayoutBuilderEntityViewDisplay);
        return $entity_view_display;
      }
    }

    return NULL;
  }

  /**
   * Builds an entity's content a custom element using its layout's regions.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\custom_elements\CustomElement $custom_element
   *   The custom element.
   * @param \Drupal\custom_elements\CustomElementsLayoutBuilderEntityViewDisplay $display
   *   The view display of the current view mode.
   */
  protected function buildLayoutBuilderContent(EntityInterface $entity, CustomElement $custom_element, CustomElementsLayoutBuilderEntityViewDisplay $display) {
    $section_elements = [];
    $build = $display->buildLayoutSections($entity);

    // Loop over sections and convert render array back to custom elements
    // if blocks render into custom elements.
    foreach (Element::children($build, TRUE) as $key) {
      $section_element = CustomElement::create('drupal-layout');
      $section_build = $build[$key];
      /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
      $layout = $section_build['#layout'];
      $section_element->setAttribute('layout', $layout->id());
      foreach ($layout->getRegions() as $region_name => $region) {
        if (!empty($section_build[$region_name])) {
          $elements = $this->getElementsFromBlockContentRenderArray($section_build[$region_name], $section_element);
          $section_element->addSlotFromNestedElements($region_name, $elements);
        }
      }
      $section_element->setAttribute('settings', $section_build['#settings']);
      $section_elements[] = $section_element;
    }

    $custom_element->setSlotFromNestedElements('sections', $section_elements);
    $custom_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($build));
    $custom_element->addCacheableDependency($display);
  }

  /**
   * Gets a Custom Elements display for the entity type, bundle and view mode.
   *
   * @param string $entityTypeId
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   * @param string $viewMode
   *   The view mode.
   *
   * @return \Drupal\custom_elements\Entity\EntityCeDisplayInterface
   *   The applicable CE display. If none exists / is enabled, a suiting
   *   display is auto-generated.
   */
  public function getEntityCeDisplay(string $entityTypeId, string $bundle, string $viewMode) {
    /** @var \Drupal\custom_elements\Entity\EntityCeDisplayInterface $entity_ce_display */
    $entity_ce_display = $this->entityTypeManager
      ->getStorage('entity_ce_display')
      ->load("$entityTypeId.$bundle.$viewMode");

    if (!$entity_ce_display || !$entity_ce_display->status()) {
      // Fall back to default view mode. If it's not in use, auto-create one.
      $entity_ce_display = $this->entityTypeManager
        ->getStorage('entity_ce_display')
        ->load("$entityTypeId.$bundle.default");

      if (!$entity_ce_display || !$entity_ce_display->status()) {
        // Always auto-create using the default view mode, so that the
        // behaviour is not changed after creating the default-view-mode
        // config in the UI. This will add all fields from the corresponding
        // Core entity view display enabled by default.
        $entity_ce_display = $this->entityTypeManager->getStorage('entity_ce_display')->create([
          'id' => "$entityTypeId.$bundle.$viewMode",
          'targetEntityType' => $entityTypeId,
          'bundle' => $bundle,
          'mode' => 'default',
          'status' => TRUE,
        ]);
      }
    }
    return $entity_ce_display;
  }

}

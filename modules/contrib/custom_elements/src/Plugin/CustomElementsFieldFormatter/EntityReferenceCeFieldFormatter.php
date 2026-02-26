<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGenerator;
use Drupal\custom_elements\CustomElementNormalizer;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;

/**
 * Implementation of the 'entity_ce_render' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "entity_ce_render",
 *   label = @Translation("Custom element: Rendered entity"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions"
 *   },
 *   weight = -5
 * )
 */
class EntityReferenceCeFieldFormatter extends CustomElementsFieldFormatterBase {

  /**
   * Custom elements generator.
   *
   * @var \Drupal\custom_elements\CustomElementGenerator
   */
  protected CustomElementGenerator $ceGenerator;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The custom element normalizer.
   *
   * @var \Drupal\custom_elements\CustomElementNormalizer
   */
  protected CustomElementNormalizer $normalizer;

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
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\custom_elements\CustomElementNormalizer $normalizer
   *   The custom element normalizer.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, CustomElementGenerator $ce_generator, EntityDisplayRepositoryInterface $entity_display_repository, CustomElementNormalizer $normalizer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ceGenerator = $ce_generator;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->normalizer = $normalizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container, array $configuration, $plugin_id, $plugin_definition) {
    // @phpstan-ignore-next-line
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('custom_elements.generator'),
      $container->get('entity_display.repository'),
      $container->get('custom_elements.normalizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    if ($items instanceof EntityReferenceFieldItemListInterface) {
      $entities = $items->referencedEntities();
      // 'Flatten' implies outputting the first value only for multi-value
      // fields (like the 'flattened' formatter).
      if (!empty($this->configuration['flatten'])) {
        $entities = [reset($entities)];
      }
      $nested_elements = [];
      foreach ($entities as $entity) {
        $nested_elements[] = $this->ceGenerator->generate($entity, $this->configuration['mode'], $langcode);
      }

      if ($this->isSlot() && empty($this->configuration['flatten'])) {
        $custom_element->setSlotFromNestedElements($this->getName(), $nested_elements);
      }
      else {
        // Use implicit format for nested elements in props - explicit format
        // with props/slots separation doesn't make sense for nested elements
        // that are output as prop values.
        $context = [
          'cache_metadata' => new BubbleableMetadata(),
          'key_casing' => 'ignore',
          'explicit' => FALSE,
        ];
        // NULL means FALSE for backwards compatibility with existing config.
        $hide_element = $this->configuration['hide_element'] ?? FALSE;
        $is_single_value = $this->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() == 1
            || !empty($this->configuration['flatten']);
        if ($is_single_value) {
          $value = $this->normalizer->normalize($nested_elements[0], NULL, $context);
          if ($hide_element) {
            unset($value['element']);
          }
        }
        else {
          $value = [];
          foreach ($nested_elements as $nested_element) {
            $normalized = $this->normalizer->normalize($nested_element, NULL, $context);
            if ($hide_element) {
              unset($normalized['element']);
            }
            $value[] = $normalized;
          }
        }
        $custom_element->addCacheableDependency($context['cache_metadata']);

        if (empty($this->configuration['flatten'])) {
          // Set normalized value.
          $custom_element->setAttribute($this->getName(), $value);
        }
        else {
          // Move separated properties into current element, except 'element'.
          // Ignore $this->isSlot().
          unset($value['element']);
          $element_name = $this->getName();
          foreach ($value as $name => $property) {
            $custom_element->setAttribute($element_name ? "$element_name-$name" : $name, $property);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'mode' => $this->getViewMode(),
      'flatten' => FALSE,
      'hide_element' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $target_type = $this->getFieldDefinition()->getFieldStorageDefinition()->getSetting('target_type');
    $form['mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($target_type),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['mode'],
      '#required' => TRUE,
    ];
    $form['flatten'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Flatten'),
      '#default_value' => $this->configuration['flatten'],
    ];
    $form['hide_element'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide element key'),
      '#description' => $this->t('Hide the "element" key in the output. Only applies when configured as prop, not as slot.'),
      '#default_value' => $this->configuration['hide_element'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['mode'] = $form_state->getValue('mode');
    $this->configuration['flatten'] = $form_state->getValue('flatten') ?? FALSE;
    $this->configuration['hide_element'] = (bool) $form_state->getValue('hide_element');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $target_type = $this->getFieldDefinition()->getFieldStorageDefinition()->getSetting('target_type');
    $options = $this->entityDisplayRepository->getViewModeOptions($target_type);
    $summary[] = $this->t('View mode: @mode', ['@mode' => $options[$this->configuration['mode']] ?? $this->configuration['mode']]);
    if (!empty($this->configuration['flatten'])) {
      $summary[] = $this->t('Add fields directly into current element.');
      if ($this->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() != 1) {
        $summary[] = $this->t('Only the first field value is output.');
      }
    }
    if (!empty($this->configuration['hide_element'])) {
      $summary[] = $this->t('Hide element key in output.');
    }
    return $summary;
  }

}

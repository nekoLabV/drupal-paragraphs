<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementNormalizer;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;
use Drupal\custom_elements\RenderConverter\CanvasRenderConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'canvas' formatter.
 *
 * @CustomElementsFieldFormatter(
 *   id = "canvas",
 *   label = @Translation("Canvas: Component tree"),
 *   field_types = {
 *     "component_tree"
 *   },
 *   weight = -10
 * )
 */
class CanvasFormatter extends CustomElementsFieldFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The Canvas render converter service.
   *
   * @var \Drupal\custom_elements\RenderConverter\CanvasRenderConverter
   */
  protected $canvasConverter;

  /**
   * The custom elements normalizer service.
   *
   * @var \Drupal\custom_elements\CustomElementNormalizer
   */
  protected $normalizer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CanvasRenderConverter $canvas_converter, CustomElementNormalizer $normalizer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->canvasConverter = $canvas_converter;
    $this->normalizer = $normalizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('custom_elements.canvas_render_converter'),
      $container->get('custom_elements.normalizer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    // Get the render array from the field items.
    $entity = $items->getEntity();
    $canvas_render_array = $items->toRenderable($entity);

    // Convert the Canvas render array to custom elements.
    $converted_element = $this->canvasConverter->convertRenderArray($canvas_render_array, $custom_element);

    if ($converted_element) {
      if ($this->isSlot()) {
        $custom_element->setSlotFromCustomElement($this->getName(), $converted_element);
      }
      else {
        $normalized = $this->normalizer->normalize($converted_element);
        $custom_element->setAttribute($this->getName(), $normalized);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // No configuration to save.
  }

}

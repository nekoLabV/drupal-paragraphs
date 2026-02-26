<?php

namespace Drupal\lupus_decoupled_views\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementGeneratorTrait;
use Drupal\views\Attribute\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * The style plugin for custom elements format.
 */
#[ViewsStyle(
  id: "custom_elements",
  title: new TranslatableMarkup("Custom Elements"),
  help: new TranslatableMarkup("Generates the view as JSON."),
  theme: "views_view_custom_elements",
  display_types: ["normal"],
)]
class CustomElements extends StylePluginBase {

  use CustomElementGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['rows_wrapper'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // The Serializer parent offers JSON (default) and XML.
    // We only want to make use of JSON so hide the option.
    unset($form['formats']);

    // Hide the 'uses_fields' option as it doesn't work properly.
    if (isset($form['uses_fields'])) {
      $form['uses_fields']['#access'] = FALSE;
    }

    $form['rows_wrapper'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rows wrapper element'),
      '#description' => $this->t('Optionally, a custom element used for wrapping rows. For example, a custom grid component.'),
      '#default_value' => $this->options['rows_wrapper'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    $row_plugin = $this->view->rowPlugin->getPluginId();
    $fields_plugin = $row_plugin === 'fields';

    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    foreach ($this->view->result as $row) {
      if ($fields_plugin) {
        $custom_element = new CustomElement();
        foreach ($this->view->field as $name => $value) {
          // @todo Field row plugin is not yet supported and is broken for
          // multi value entity reference fields.
          $custom_element->setAttribute($name, $value->render($row));
        }
      }
      else {
        $custom_element = $this->getCustomElementGenerator()->generate($row->_entity, $this->view->rowPlugin->options['view_mode'] ?? $this->getViewMode($row->_entity->getEntityTypeId(), $row->_entity->bundle()));
      }
      // Use toRenderArray() to respect the render variant configuration.
      // Note: toRenderArray() always attaches '#custom_element' to the render
      // array (for all variants), which enables the "custom_elements_page"
      // display plugin and other code to detect and extract the custom element.
      // @see \Drupal\custom_elements\CustomElement::toRenderArray()
      $rows[] = $custom_element->toRenderArray();
    }

    // Wrap rows in a custom element for the configured wrapper.
    $wrapper_tag = $this->options['rows_wrapper'] ?: 'drupal-markup';
    $wrapper_element = CustomElement::create($wrapper_tag);
    $row_elements = array_filter(array_map(
      fn($row) => $row['#custom_element'] ?? NULL,
      $rows
    ));
    $wrapper_element->setSlotFromNestedElements('default', $row_elements);
    $build = $wrapper_element->toRenderArray();
    $build['#row_elements'] = $row_elements;
    return $build;
  }

  /**
   * Get view mode for Rendered entity view row plugin.
   *
   * @param string $entity_type_id
   *   Entity type id.
   * @param string $bundle
   *   Entity bundle.
   *
   * @return string|null
   *   The view mode must have custom elements display available.
   */
  protected function getViewMode(string $entity_type_id, string $bundle) {
    $options = $this->view->rowPlugin->options;
    if (isset($options['view_modes'])) {
      return $options['view_modes']["entity:$entity_type_id"][$bundle] ?? $options['view_modes']["entity:$entity_type_id"][':default'];
    }
    return NULL;
  }

}

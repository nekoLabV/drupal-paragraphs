<?php

namespace Drupal\lupus_decoupled_views\Plugin\views\display;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\custom_elements\CustomElement;
use Drupal\views\Plugin\views\pager\None;
use Drupal\views\Plugin\views\pager\Some;
use Drupal\views\ViewExecutable;

/**
 * Provides helpers for the custom-element views display plugins.
 *
 * @internal
 */
trait CustomElementsViewsDisplayTrait {

  /**
   * Defines custom element specific options for display plugins.
   *
   * @return array
   *   Options array.
   */
  protected function defineCustomElementOptions() {
    return [
      'custom_element_name' => ['default' => ''],
    ];
  }

  /**
   * Builds form elements for custom element options.
   *
   * @param array $form
   *   The form array to add elements to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function buildCustomElementOptionsForm(array &$form, FormStateInterface $form_state) {
    $form['custom_element_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom element name'),
      '#description' => $this->t('Optionally override the auto-generated element name (e.g., "my-custom-view"). Leave empty to use "drupal-view-{view_id}-{display_type}".'),
      '#default_value' => $this->options['custom_element_name'] ?? '',
    ];
  }

  /**
   * Validates that the Custom Elements style plugin is being used.
   *
   * @return array
   *   An array of error strings. Empty array if valid.
   */
  protected function validateCustomElementsStyleUsage() {
    $errors = [];
    $style_type = $this->getOption('style')['type'] ?? NULL;

    if ($style_type !== 'custom_elements') {
      $errors[] = $this->t('This display uses a Custom Elements display plugin but not the "Custom Elements" style. Please select "Custom Elements" as the style for proper output.');
    }

    return $errors;
  }

  /**
   * Build a custom element from a pre-rendered view.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view.
   * @param array $render
   *   Pre-rendered render array of the view.
   * @param array $rows
   *   The rendered rows of the view.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Custom element.
   */
  public function buildCustomElement(ViewExecutable $view, array $render, array $rows) {
    // Check for custom element name override.
    $custom_name = $view->display_handler->options['custom_element_name'] ?? '';
    if ($custom_name) {
      $custom_element = CustomElement::create($custom_name);
    }
    else {
      // Auto-generate element name from view ID and display type.
      $display_type = strpos($view->getDisplay()
        ->getPluginId(), 'custom_elements_') === 0 ?
        substr($view->getDisplay()->getPluginId(), 16) :
        $view->getDisplay()->getPluginId();
      $custom_element = CustomElement::create('drupal-view-' . str_replace('_', '-', $view->id() . '-' . $display_type));
    }

    $custom_element->setAttribute('title', $view->getTitle());
    $custom_element->setAttribute('view_id', $view->id());
    $custom_element->setAttribute('display_id', $view->getDisplay()->display['id']);
    if (!empty($view->args)) {
      $custom_element->setAttribute('args', $view->args);
    }

    // Extract rows wrapper from the style plugin's wrapper element.
    // The style plugin wraps rows in a custom element with a configured tag
    // and provides the raw rows in '#row_elements', passed as $row_elements.
    // Instead of simply outputting the wrapper element, we pass the rows and
    // wrapper-element-name separately. That way the view's frontend component
    // can do the wrapping and stays more flexible.
    $rows_wrapper_element = $render['#rows']['#custom_element'] ?? NULL;
    // Set empty string for default 'drupal-markup', pass custom wrapper name.
    $rows_wrapper = $rows_wrapper_element && $rows_wrapper_element->getTag() !== 'drupal-markup' ? $rows_wrapper_element->getTag() : '';
    $custom_element->setAttribute('rows_wrapper', $rows_wrapper);
    $custom_element->setSlotFromNestedElements('rows', $rows);

    // @todo Add header, footer areas.
    $custom_element->setAttribute('pager', !empty($view->pager) ? $this->getPaginationData($view->pager, $rows) : NULL);

    $custom_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($render));
    return $custom_element;
  }

  /**
   * Gets relevant data from the pager plugin that already 'built' it.
   *
   * @return array|null
   *   Returns data for the "pager" attribute.
   */
  private function getPaginationData($pager, $rows) {
    $pagination = [];
    $class = get_class($pager);
    if ($class === NULL) {
      return NULL;
    }
    if (method_exists($pager, 'getPagerTotal')) {
      $pagination['total_pages'] = $pager->getPagerTotal();
    }
    if (method_exists($pager, 'getCurrentPage')) {
      $pagination['current'] = $pager->getCurrentPage() ?? 0;
    }
    if ($pager instanceof None) {
      $pagination['items_per_page'] = $pager->getTotalItems();
    }
    elseif ($pager instanceof Some) {
      $pagination['total_items'] = count($rows);
    }
    return $pagination;
  }

}

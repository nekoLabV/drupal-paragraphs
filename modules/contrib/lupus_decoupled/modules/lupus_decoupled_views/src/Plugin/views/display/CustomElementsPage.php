<?php

namespace Drupal\lupus_decoupled_views\Plugin\views\display;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsDisplay;
use Drupal\views\Plugin\views\display\Page;

/**
 * The plugin that handles a full page for a custom elements view.
 *
 * @ingroup views_display_plugins
 */
#[ViewsDisplay(
  id: "custom_elements_page",
  title: new TranslatableMarkup("Custom Elements Page"),
  help: new TranslatableMarkup("Display the view as page rendered with the custom_elements format."),
  uses_menu_links: TRUE,
  uses_route: TRUE,
  contextual_links_locations: ["custom_elements_page"],
  theme: "views_view",
  admin: new TranslatableMarkup("Custom Elements Page"),
)]
class CustomElementsPage extends Page {

  use CustomElementsViewsDisplayTrait;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options += $this->defineCustomElementOptions();
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    $options['custom_element_name'] = [
      'category' => 'page',
      'title' => $this->t('Custom element'),
      'value' => $this->options['custom_element_name'] ?: $this->t('Default'),
      'desc' => $this->t('Override the auto-generated custom element tag name.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    if ($form_state->get('section') == 'custom_element_name') {
      $this->buildCustomElementOptionsForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);

    if ($form_state->get('section') == 'custom_element_name') {
      $this->setOption('custom_element_name', $form_state->getValue('custom_element_name'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = parent::validate();
    $errors = array_merge($errors, $this->validateCustomElementsStyleUsage());
    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $render = parent::render();
    // Manually pre-render the element, such that we can convert results into
    // the custom element.
    $render = $this->elementPreRender($render);
    $custom_element = $this->buildCustomElement($this->view, $render, $render['#rows']['#row_elements'] ?? []);
    // Render the element to a JSON preview in preview mode, if we are in
    // preview mode. Else, keep the configured default rendering variant.
    $render = $custom_element->toRenderArray(!empty($this->view->live_preview) ? 'preview:json' : NULL);
    $this->moduleHandler()->alter('lupus_decoupled_views_page_alter', $custom_element, $view, $render);
    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function preview() {
    $element = parent::preview();
    $element['#prefix'] = '<div class="preview-section">';
    $element['#suffix'] = '</div>';
    return $element;
  }

}

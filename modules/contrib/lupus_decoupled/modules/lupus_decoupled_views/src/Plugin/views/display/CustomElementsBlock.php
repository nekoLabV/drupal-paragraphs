<?php

namespace Drupal\lupus_decoupled_views\Plugin\views\display;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Element\View;
use Drupal\views\Plugin\Block\ViewsBlock;
use Drupal\views\Plugin\views\display\Block;

/**
 * The plugin that handles a block.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "custom_elements_block",
 *   title = @Translation("Custom Elements Block"),
 *   help = @Translation("Display the view as a custom elements block."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_hook_block = TRUE,
 *   contextual_links_locations = {"block"},
 *   admin = @Translation("Block")
 * )
 *
 * @see \Drupal\views\Plugin\Block\ViewsBlock
 * @see \Drupal\views\Plugin\Derivative\ViewsBlock
 */
class CustomElementsBlock extends Block {

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
      'category' => 'block',
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
  public function preBlockBuild(ViewsBlock $block) {
    parent::preBlockBuild($block);

    $config = $block->getConfiguration();
    // Make sure the block title-related configuration is respected.
    if (empty($config['label_display'])) {
      $this->view->setTitle('');
    }
    elseif (!empty($config['views_label'])) {
      // Inject the overridden block title into the view. This is normally done
      // by ViewsBlock::build() but it only sets it after we generated the CE.
      // @see static::buildRenderable()
      $this->view->setTitle($config['views_label']);
    }
    // Else the default title of the view is used.
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\views\Plugin\Block\ViewsBlock::build()
   */
  public function buildRenderable(array $args = [], $cache = TRUE) {
    // Called from ViewsBlock::build() when calling $view->buildRenderable().
    // We already take care of pre-rendering the View here, instead of having
    // ViewsBlock::build() do it, such that we can control the process and
    // make sure we provide the custom-element as the top-level element that is
    // provided by the block.
    $render = parent::buildRenderable($args, $cache);

    // Restore the (possibly overridden) title from the view. This needs to be
    // done before the view is rendered, as during view-rendering a title
    // override would be lost.
    // @see static::preBlockBuild()
    $title = isset($this->view->build_info['title']) && $this->view->build_info['title'] === '' ? '' : $this->view->getTitle();

    // Pre-render view like ViewsBlock::build does it. It sets #pre_rendered
    // such that it will be executed only once.
    $render = View::preRenderViewElement($render);

    // Now, we can process the render-array as needed.
    // The render() method has pre-rendered into the custom element.
    if (!empty($render['view_build']['#custom_element'])) {
      $custom_element = $render['view_build']['#custom_element'];
      $custom_element->setAttribute('title', $title);
      // Use toRenderArray() to respect the render variant configuration.
      // Merge with existing render array to preserve cache metadata.
      $render = $custom_element->toRenderArray() + $render;
    }
    return $render;
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
    return $custom_element->toRenderArray(!empty($this->view->live_preview) ? 'preview:json' : NULL);
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

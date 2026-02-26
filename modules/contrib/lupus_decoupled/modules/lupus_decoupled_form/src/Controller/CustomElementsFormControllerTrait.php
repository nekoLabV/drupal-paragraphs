<?php

namespace Drupal\lupus_decoupled_form\Controller;

use Drupal\custom_elements\CustomElement;

/**
 * Trait for custom-elements enabled form controllers.
 *
 * Wraps the form into a lupus-form element containing the rendered form within
 * a nested lupus-form-content element (in default slot).
 */
trait CustomElementsFormControllerTrait {

  /**
   * The custom elements form controller service.
   *
   * @var \Drupal\lupus_decoupled_form\Controller\CustomElementsFormController
   */
  protected $formService;

  /**
   * The custom elements entity form controller service.
   *
   * @var \Drupal\lupus_decoupled_form\Controller\CustomElementsEntityFormController
   */
  protected $entityFormService;

  /**
   * Prepare the content result wrapped in custom elements.
   *
   * @param array $form
   *   The prepared and processed form render array.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   The lupus-form custom element.
   */
  public function getCustomElementsContentResult(array $form): CustomElement {
    // Remove the theme-wrapper which adds the wrapping <form> element, such
    // that the frontend is able to add and control it.
    unset($form['#theme_wrappers']);
    $element = CustomElement::createFromRenderArray($form)
      ->setTag('drupal-form-' . str_replace('_', '-', $form['#form_id']));
    foreach (['form_id', 'attributes', 'method'] as $key) {
      $element->setAttribute($key, $form['#' . $key] ?? NULL);
    }
    return $element;
  }

  /**
   * Gets the custom elements form controller service.
   *
   * @return \Drupal\lupus_decoupled_form\Controller\CustomElementsFormController
   *   The form service.
   */
  public function getCustomElementsFormController() {
    if (empty($this->formService)) {
      $this->formService = \Drupal::service('lupus_decoupled_form.custom_elements.controller.form');
    }
    return $this->formService;
  }

  /**
   * Gets the custom elements form controller service.
   *
   * @return \Drupal\lupus_decoupled_form\Controller\CustomElementsEntityFormController
   *   The form service.
   */
  public function getCustomElementsEntityFormController() {
    if (empty($this->entityFormService)) {
      $this->entityFormService = \Drupal::service('lupus_decoupled_form.custom_elements.controller.entity_form');
    }
    return $this->entityFormService;
  }

}

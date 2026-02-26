<?php

namespace Drupal\lupus_decoupled_webform;

use Drupal\lupus_decoupled_form\Controller\CustomElementsFormControllerTrait;
use Drupal\webform\WebformInterface;

/**
 * Provides custom elements output for webforms.
 *
 * @see lupus_decoupled_form/src/Controller/CustomElementsFormControllerTrait.php
 */
trait CustomElementsWebformTrait {

  use CustomElementsFormControllerTrait;

  /**
   * Renders the webform using custom elements.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   The webform.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Custom element for response.
   */
  protected function getCustomElementsWebform(WebformInterface $webform) {
    $ce_webform = $this->getCustomElementsContentResult($webform->getSubmissionForm());
    $ce_webform->setAttribute('type', 'webform');
    $ce_webform->setAttribute('title', $webform->label() ?? '');
    return $ce_webform;
  }

}

<?php

namespace Drupal\lupus_decoupled_webform\Controller;

use Drupal\custom_elements\CustomElement;
use Drupal\lupus_decoupled_webform\CustomElementsWebformTrait;
use Drupal\webform\Controller\WebformEntityController;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a custom-elements enabled form controller.
 *
 * @see \Drupal\Core\Routing\Enhancer\FormRouteEnhancer
 */
class CustomElementsWebformController extends WebformEntityController {

  use CustomElementsWebformTrait;

  /**
   * {@inheritdoc}
   */
  public function addForm(Request $request, WebformInterface $webform) {
    return $this->getCustomElementsWebform($webform);
  }

  /**
   * {@inheritDoc}
   */
  public function confirmation(Request $request, ?WebformInterface $webform = NULL, ?WebformSubmissionInterface $webform_submission = NULL) {
    $result = parent::confirmation($request, $webform, $webform_submission);
    return CustomElement::createFromRenderArray($result);
  }

}

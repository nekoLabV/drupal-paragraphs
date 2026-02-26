<?php

namespace Drupal\lupus_decoupled_form\Controller;

use Drupal\Core\Controller\HtmlFormController;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a custom-elements enabled form controller.
 *
 * @see \Drupal\Core\Routing\Enhancer\FormRouteEnhancer
 */
class CustomElementsFormController extends HtmlFormController {

  use CustomElementsFormControllerTrait;

  /**
   * {@inheritdoc}
   */
  public function getContentResult(Request $request, RouteMatchInterface $route_match) {
    $form = parent::getContentResult($request, $route_match);
    return $this->getCustomElementsContentResult($form);
  }

}

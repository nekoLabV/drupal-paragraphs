<?php

namespace Drupal\lupus_decoupled_form\Controller;

use Drupal\Core\Entity\HtmlEntityFormController;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a custom-elements enabled form controller for entity forms.
 *
 * @see \Drupal\Core\Entity\Enhancer\EntityRouteEnhancer::enhanceEntityForm
 */
class CustomElementsEntityFormController extends HtmlEntityFormController {

  use CustomElementsFormControllerTrait;

  /**
   * {@inheritdoc}
   */
  public function getContentResult(Request $request, RouteMatchInterface $route_match) {
    $form = parent::getContentResult($request, $route_match);
    return $this->getCustomElementsContentResult($form);
  }

}

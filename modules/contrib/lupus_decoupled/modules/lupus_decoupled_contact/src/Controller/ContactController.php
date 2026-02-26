<?php

namespace Drupal\lupus_decoupled_contact\Controller;

use Drupal\contact\ContactFormInterface;
use Drupal\contact\Controller\ContactController as CoreContactController;
use Drupal\lupus_decoupled_form\Controller\CustomElementsFormControllerTrait;

/**
 * Controller routines for contact routes.
 *
 * @internal
 */
class ContactController extends CoreContactController {

  use CustomElementsFormControllerTrait;

  /**
   * {@inheritdoc}
   */
  public function contactSitePage(?ContactFormInterface $contact_form = NULL) {
    $form = parent::contactSitePage($contact_form);
    return $this->getCustomElementsContentResult($form);
  }

}

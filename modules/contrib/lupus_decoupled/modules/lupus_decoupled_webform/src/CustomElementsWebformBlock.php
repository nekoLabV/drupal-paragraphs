<?php

namespace Drupal\lupus_decoupled_webform;

use Drupal\webform\Plugin\Block\WebformBlock;
use Drupal\webform\WebformInterface;
use drunomics\ServiceUtils\Symfony\HttpFoundation\RequestStackTrait;

/**
 * Renders the webform block using custom elements.
 */
class CustomElementsWebformBlock extends WebformBlock {

  use RequestStackTrait;
  use CustomElementsWebformTrait;

  /**
   * Determines whether the entity is rendered via custom elements.
   *
   * @return bool
   *   Is request format custom elements.
   */
  public function isCustomElementsEnabled() {
    return $this->getCurrentRequest()->getRequestFormat() == 'custom_elements';
  }

  /**
   * Overrides the build function of the webform block.
   *
   * @return array|void
   *   Webform block build array.
   */
  public function build() {
    if ($this->isCustomElementsEnabled()) {
      $webform = $this->getWebform();
      $block_ce = $this->getCustomElementsWebformBlock($webform);
      $block_ce->setAttribute('as-block', 1);
      return $block_ce->toRenderArray();
    }
    return parent::build();
  }

  /**
   * Get custom element for webform block.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   The webform.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Custom element of the block.
   */
  protected function getCustomElementsWebformBlock(WebformInterface $webform) {
    $ce_webform = $this->getCustomElementsWebform($webform);

    // Unset title value when config set to not display it.
    if (!empty($this->pluginId)) {
      $label_display = $this?->getConfiguration()['label_display'];
      if ($label_display !== 'visible') {
        $ce_webform->setAttribute('title', '');
      }
    }
    return $ce_webform;
  }

}

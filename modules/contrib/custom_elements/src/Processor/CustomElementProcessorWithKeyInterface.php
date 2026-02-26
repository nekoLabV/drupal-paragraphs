<?php

namespace Drupal\custom_elements\Processor;

use Drupal\custom_elements\CustomElement;

/**
 * Processes data into custom elements.
 *
 * Processors should implement this interface (not the parent) to honor the
 * "name" value configured for fields in entity_ce_display config entities.
 *
 * For backwards compatibility, addtoElement()'s key parameter is optional.
 */
interface CustomElementProcessorWithKeyInterface extends CustomElementProcessorInterface {

  /**
   * Processes the given data and adds it to the element.
   *
   * @param mixed $data
   *   The data to be added.
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element that is generated.
   * @param string $viewMode
   *   The view mode under which the element is rendered.
   * @param string $key
   *   (Optional) Name to use for adding data into (a slot / attribute in) the
   *   custom element. Processors can use this how they see fit. Most use it
   *   as a literal key value; some may use it as e.g. a key prefix to add
   *   several values.
   */
  public function addtoElement($data, CustomElement $element, $viewMode, $key = '');

}

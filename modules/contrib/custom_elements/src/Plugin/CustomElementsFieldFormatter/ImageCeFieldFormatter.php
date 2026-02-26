<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

/**
 * Implementation of the 'image' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "image",
 *   label = @Translation("Image Style (URL, Alt, Width, Height)"),
 *   field_types = {
 *     "image",
 *   },
 *   weight = -2
 * )
 */
class ImageCeFieldFormatter extends FileCeFieldFormatter {

  // The parent formatter already covers image fields, we just add this
  // formatter for better usability.
  // @todo In a future major version, we could consider moving all image
  // related code here.
}

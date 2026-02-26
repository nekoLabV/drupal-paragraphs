<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\custom_elements\CustomElementsFieldFormatterUtilsTrait;
use Drupal\link\LinkItemInterface;

/**
 * Implementation of the 'link' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "link",
 *   label = @Translation("Link"),
 *   field_types = {
 *     "link"
 *   },
 *   weight = -20
 * )
 */
class LinkCeFieldFormatter extends RawCeFieldFormatter {

  use CustomElementsFieldFormatterUtilsTrait;

  /**
   * {@inheritdoc}
   */
  protected function getFieldItemListValue(FieldItemListInterface $items): array {
    // Unlike the parent, loop over individual field items to get their values.
    $field_item_values = [];
    foreach ($items as $field_item) {
      // This should always be true; see field_types.
      if ($field_item instanceof LinkItemInterface) {
        // Build URL, instead of sending only its components. It's very likely
        // that these components are 'uri' and 'options', and the client
        // doesn't want to receive those too. (It would just create confusion.)
        $field_item_value = $this->getFieldItemProperties($field_item, ['uri', 'options']);
        $field_item_value['href'] = $field_item->getUrl()->toString();
        if ($field_item->isExternal()) {
          $field_item_value['external'] = TRUE;
        }
      }
      else {
        $field_item_value = $this->getFieldItemProperties($field_item);
      }
      $field_item_values[] = $field_item_value;
    }

    return $field_item_values;
  }

}

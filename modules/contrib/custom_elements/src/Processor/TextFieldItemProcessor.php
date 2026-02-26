<?php

namespace Drupal\custom_elements\Processor;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;

/**
 * Default processor for text field items.
 */
class TextFieldItemProcessor implements CustomElementProcessorWithKeyInterface {

  /**
   * {@inheritdoc}
   */
  public function supports($data, $viewMode) {
    return $data instanceof FieldItemInterface && $data instanceof TextItemBase;
  }

  /**
   * {@inheritdoc}
   */
  public function addtoElement($data, CustomElement $element, $viewMode, $key = '') {
    assert($data instanceof FieldItemInterface);
    $field_item = $data;

    // Add summary as <$key_summary> - except if $key is not provided, add
    // regular value as 'default' and summary as 'summary'.
    $element->setSlot($key ?: 'default', $field_item->processed);
    if (!empty($field_item->summary_processed)) {
      $element->setSlot(($key ? "$key-" : '') . 'summary', $field_item->summary_processed);
    }
  }

}

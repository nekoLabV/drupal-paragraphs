<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Button 段落预处理器
 */
class ButtonPreprocessor extends BaseParagraphPreprocessor {

  /**
   * 预处理 Button 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('button_', true);

    // 收集数据
    $button_data = [
      'text' => $this->getFieldValue($paragraph, 'field_button_label'),
      'type' => $this->getFieldValue($paragraph, 'field_click_event'),
      'href' => ''
    ];

    $link = $this->getLinkFieldValue($paragraph, 'field_click_link');

    $mail = $this->getFieldValue($paragraph, 'field_click_mail');
   
    $download = $paragraph->get('field_click_download')->entity ?? null;
    $download_document = $this->getMediaData($download, 'field_media_document');

    $phone = $this->getFieldValue($paragraph, 'field_click_phone');

    if ($button_data['type'] == 'link') {
      $button_data['href'] = $link['url'];
    } else if ($button_data['type'] == 'mail') {
      $button_data['href'] = $mail;
    } else if ($button_data['type'] == 'download') {
      $button_data['href'] = $download_document['url'];
    } else if ($button_data['type'] == 'phone') {
      $button_data['href'] = $phone;
    } else {
      $button_data['href'] = $link;
    }

    $variables['button_id'] = $id;
    $variables['#attached']['drupalSettings']['button'][$id] = $button_data;
  }
}

<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraphs\Entity\Paragraph as ParagraphEntity;

abstract class BaseParagraphPreprocessor {

  // 获取 Text 字段
  protected function getFieldValue(ParagraphEntity $paragraph, string $field) {
    return ($paragraph->hasField($field) && !$paragraph->get($field)->isEmpty())
      ? $paragraph->get($field)->value
      : '';
  }

  // 获取 Link 字段
  protected function getLinkFieldValue(ParagraphEntity $paragraph, string $field) {
    if ($paragraph->hasField($field) && !$paragraph->get($field)->isEmpty()) {
      $field_items = $paragraph->get($field);
      $link_item = $field_items->first();
      
      if ($link_item) {
        return [
          'url' => $link_item->getUrl()->toString(),
          'title' => $link_item->get('title')->getString(),
          'uri' => $link_item->get('uri')->getString(),
        ];
      }
    }
    return '';
  }

  // 获取 Media 字段
  protected function getMediaData($media, $type = 'field_media_image'): ?array {
    if (!$media || !$media->hasField($type)) {
      return null;
    }

    $media_data = $media->get($type);
    $file = $media_data->entity;

    if (!$file) {
      return null;
    }

    return [
      'url' => \Drupal::service('file_url_generator')
        ->generateAbsoluteString($file->getFileUri()),
      'alt' => $media_data->alt
    ];
  }

  /**
   * 获取 Buttons
   */
  protected function getButtons(ParagraphEntity $paragraph): array {
    $buttons = [];
    
    if (!$paragraph->hasField('field_button') || $paragraph->get('field_button')->isEmpty()) {
      return $buttons;
    }
    
    // 遍历引用字段中的每个按钮段落
    foreach ($paragraph->get('field_button')->referencedEntities() as $button_paragraph) {
      if ($button_paragraph instanceof ParagraphEntity) {
        $buttons[] = $this->getButton($button_paragraph);
      }
    }
    
    return array_filter($buttons);
  }

  /**
   * 获取 Button
   */
  protected function getButton(ParagraphEntity $paragraph): array {
    $button_data = [
      'text' => $this->getFieldValue($paragraph, 'field_button_label'),
      'type' => $this->getFieldValue($paragraph, 'field_click_event'),
      'href' => '',
      'btnStyle' => $this->getFieldValue($paragraph, 'field_button_style')
    ];

    $link = $this->getLinkFieldValue($paragraph, 'field_click_link');

    $mail = $this->getFieldValue($paragraph, 'field_click_mail');
   
    $download_document = null;
    if ($paragraph->hasField('field_click_download') && !$paragraph->get('field_click_download')->isEmpty()) {
      $download = $paragraph->get('field_click_download')->entity ?? null;
      $download_document = $this->getMediaData($download, 'field_media_document');
    }

    $phone = $this->getFieldValue($paragraph, 'field_click_phone');

    switch ($button_data['type']) {
      case 'link':
        $button_data['href'] = $link['url'];
        break;
      case 'mail':
        $button_data['href'] = $mail;
        break;
      case 'download':
        $button_data['href'] = $download_document['url'];
        break;
      case 'phone':
        $button_data['href'] = $phone;
        break;
      default:
        $button_data['href'] = $link;
    }

    return $button_data;
  }
}

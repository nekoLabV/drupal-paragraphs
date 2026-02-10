<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

abstract class BaseParagraphPreprocessor {

  // 获取 Text 字段
  protected function getFieldValue($paragraph, string $field) {
    return ($paragraph->hasField($field) && !$paragraph->get($field)->isEmpty())
      ? $paragraph->get($field)->value
      : '';
  }

  // 获取 Link 字段
  protected function getLinkFieldValue($paragraph, string $field) {
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

    // $image = $media->get('field_media_image');
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
}

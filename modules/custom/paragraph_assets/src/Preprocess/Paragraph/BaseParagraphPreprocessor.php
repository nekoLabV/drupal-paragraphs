<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

abstract class BaseParagraphPreprocessor {

  protected function getFieldValue($paragraph, string $field) {
    return ($paragraph->hasField($field) && !$paragraph->get($field)->isEmpty())
      ? $paragraph->get($field)->value
      : null;
  }

  protected function getMediaImageData($media): ?array {
    if (!$media || !$media->hasField('field_media_image')) {
      return null;
    }

    $image = $media->get('field_media_image');
    $file = $image->entity;

    if (!$file) {
      return null;
    }

    return [
      'url' => \Drupal::service('file_url_generator')
        ->generateAbsoluteString($file->getFileUri()),
      'alt' => $image->alt
    ];
  }
}

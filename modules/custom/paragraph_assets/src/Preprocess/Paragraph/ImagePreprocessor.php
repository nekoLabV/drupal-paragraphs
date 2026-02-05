<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Image 段落预处理器
 */
class ImagePreprocessor extends BaseParagraphPreprocessor {

  /**
   * 预处理 Image 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('image_', true);

    $media = $paragraph->get('field_image_src')->entity ?? null;

    // 收集数据
    $image_data = [
      'image' => $this->getMediaImageData($media),
      'imageRatio' => $this->getFieldValue($paragraph, 'field_image_ratio'),
      'imageFit' => $this->getFieldValue($paragraph, 'field_image_fit'),
      'imageSize' => $this->getFieldValue($paragraph, 'field_image_size'),
      'caption' => $this->getFieldValue($paragraph, 'field_image_caption')
    ];

    $variables['image_id'] = $id;
    $variables['#attached']['drupalSettings']['image'][$id] = $image_data;
  }
}

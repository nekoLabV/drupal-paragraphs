<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Images 段落预处理器
 */
class ImagesPreprocessor extends BaseParagraphPreprocessor {
  
  /**
   * 预处理 Images 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('images_', true);

    // 收集数据
    $images_data = [
      // 图像比例
      'imageRatio' => $this->getFieldValue($paragraph, 'field_image_ratio'),
      // 图像列数
      'imageCol' => $this->getFieldValue($paragraph, 'field_image_cols')
    ];

    $variables['images_id'] = $id;
    $variables['#attached']['drupalSettings']['images'][$id] = $images_data;
  }
}

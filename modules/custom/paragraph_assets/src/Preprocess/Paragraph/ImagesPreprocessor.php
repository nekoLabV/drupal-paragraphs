<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

/**
 * Images 段落预处理器
 */
class ImagesPreprocessor {
  
  /**
   * 预处理 Images 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];

    // 初始化变量
    $images_data = [
      'imageRatio' => '',
      'imageCol' => ''
    ];
    
    // 获取图像比例
    if ($paragraph->hasField('field_image_ratio') && !$paragraph->get('field_image_ratio')->isEmpty()) {
      $image_ratio = $paragraph->get('field_image_ratio')->getValue()[0]['value'] ?? '';
      
      $images_data['imageRatio'] = $image_ratio;
    }

    // 获取图像列数
    if ($paragraph->hasField('field_image_cols') && !$paragraph->get('field_image_cols')->isEmpty()) {
      $image_cols = $paragraph->get('field_image_cols')->getValue()[0]['value'] ?? '';
      
      $images_data['imageCol'] = $image_cols;
    }

    $paragraph_id = $paragraph->id() ?? time();
    $variables['images_id'] = $paragraph_id;
    
    $variables['#attached']['drupalSettings']['images'][$paragraph_id] = $images_data;
  }
}

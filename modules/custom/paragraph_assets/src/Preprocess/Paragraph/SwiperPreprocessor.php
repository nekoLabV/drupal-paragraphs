<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

/**
 * Swiper 段落预处理器
 */
class SwiperPreprocessor {
  
  /**
   * 预处理 Swiper 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];

    $swiper_data = [
      'cols' => ''
    ];

    // 获取 Swiper 列数
    if ($paragraph->hasField('field_swiper_cols') && !$paragraph->get('field_swiper_cols')->isEmpty()) {
      $swiper_cols = $paragraph->get('field_swiper_cols')->getValue()[0]['value'] ?? '';
      
      $swiper_data['cols'] = $swiper_cols;
    }

    $paragraph_id = $paragraph->id() ?? time();
    $variables['swiper_id'] = $paragraph_id;
    
    $variables['#attached']['drupalSettings']['swiper'][$paragraph_id] = $swiper_data;
  }
}

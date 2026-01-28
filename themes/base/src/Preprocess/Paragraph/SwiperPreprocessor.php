<?php

namespace Drupal\base\Preprocess\Paragraph;

/**
 * Swiper 段落预处理器
 */
class SwiperPreprocessor {
  
  /**
   * 预处理 Swiper 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];

    $paragraph_id = $paragraph->id() ?? time();
    $variables['swiper_id'] = $paragraph_id;
    
    // 添加库
    $variables['#attached']['library'][] = 'base/themeHooks';
    $variables['#attached']['drupalSettings']['swiper'][$paragraph_id] = $paragraph_id;
  }
}

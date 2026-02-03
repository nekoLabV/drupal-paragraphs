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

    $paragraph_id = $paragraph->id() ?? time();
    $variables['swiper_id'] = $paragraph_id;
    
    $variables['#attached']['drupalSettings']['swiper'][$paragraph_id] = $paragraph_id;
  }
}

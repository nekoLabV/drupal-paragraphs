<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Swiper 段落预处理器
 */
class SwiperPreprocessor extends BaseParagraphPreprocessor {
  
  /**
   * 预处理 Swiper 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('swiper_', true);

    // 收集数据
    $swiper_data = [
      // Swiper 列数
      'cols' => Number_format($this->getFieldValue($paragraph, 'field_swiper_cols')),
      // Swiper 行数
      'rows' => $this->getFieldValue($paragraph, 'field_swiper_rows')
    ];

    $variables['swiper_id'] = $id;
    $variables['swiper_cols'] = $swiper_data['cols'];
    $variables['swiper_rows'] = $swiper_data['rows'];
    $variables['#attached']['drupalSettings']['swiper'][$id] = $swiper_data;
  }
}

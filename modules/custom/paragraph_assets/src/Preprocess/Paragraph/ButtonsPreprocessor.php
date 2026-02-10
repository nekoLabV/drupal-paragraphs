<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Buttons 段落预处理器
 */
class ButtonsPreprocessor extends BaseParagraphPreprocessor {
  
  /**
   * 预处理 Buttons 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('buttons_', true);

    // 收集数据
    $buttons_data = [
      'direction' => $this->getFieldValue($paragraph, 'field_direction'),
      'btnStyle' => $this->getFieldValue($paragraph, 'field_button_style'),
      'btnAlign' => $this->getFieldValue($paragraph, 'field_align'),
      'marginTop' => $this->getFieldValue($paragraph, 'field_margin_top'),
      'marginBottom' => $this->getFieldValue($paragraph, 'field_margin_bottom')
    ];

    $variables['buttons_id'] = $id;
    $variables['#attached']['drupalSettings']['buttons'][$id] = $buttons_data;
  }
}

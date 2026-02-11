<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Button 段落预处理器
 */
class ButtonPreprocessor extends BaseParagraphPreprocessor {

  /**
   * 预处理 Button 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('button_', true);

    // 收集数据
    $button_data = $this->getButton($paragraph);

    $variables['button_id'] = $id;
    $variables['#attached']['drupalSettings']['button'][$id] = $button_data;
  }
}

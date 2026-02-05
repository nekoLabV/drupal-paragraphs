<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * ContentBlock 段落预处理器
 */
class ContentBlockPreprocessor extends BaseParagraphPreprocessor {

  /**
   * 预处理 ContentBlock 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('image_', true);

    // 收集数据
    $content_block_data = [
      'theme' => $this->getFieldValue($paragraph, 'field_theme')
    ];

    $variables['image_id'] = $id;
    $variables['#attached']['drupalSettings']['contentBlock'][$id] = $content_block_data;
  }
}

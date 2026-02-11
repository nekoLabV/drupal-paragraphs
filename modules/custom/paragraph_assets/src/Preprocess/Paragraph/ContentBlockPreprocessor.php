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
    $id = $paragraph->id() ?? uniqid('content_block_', true);

    $background_image = $paragraph->get('field_background_image')->entity ?? null;
    $background_image_mobile = $paragraph->get('field_background_image_mobile')->entity ?? null;

    // 收集数据
    $content_block_data = [
      'theme' => $this->getFieldValue($paragraph, 'field_theme'),
      'blockAlign' => $this->getFieldValue($paragraph, 'field_block_align'),
      'colWidth' => $this->getFieldValue($paragraph, 'field_block_col_width'),
      'paddingTop' => $this->getFieldValue($paragraph, 'field_padding_top'),
      'paddingBottom' => $this->getFieldValue($paragraph, 'field_padding_bottom'),
      'marginTop' => $this->getFieldValue($paragraph, 'field_margin_top'),
      'marginBottom' => $this->getFieldValue($paragraph, 'field_margin_bottom'),
      'backgroundImageSrc' => $this->getMediaData($background_image),
      'backgroundImageMobileSrc' => $this->getMediaData($background_image_mobile)
    ];

    $variables['content_block_id'] = $id;
    $variables['#attached']['drupalSettings']['contentBlock'][$id] = $content_block_data;
  }
}

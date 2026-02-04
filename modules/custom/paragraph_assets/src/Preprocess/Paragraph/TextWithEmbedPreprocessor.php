<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\Component\Utility\Html;
use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * TextWithEmbedding 段落预处理器
 */
class TextWithEmbedPreprocessor extends BaseParagraphPreprocessor {
  
  /**
   * 预处理 TextWithEmbedding 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('textWithEmbed_', true);
    
    // 收集数据
    $embed_data = [
      // 嵌入的HTML
      'embedHtml' => $this->getFieldValue($paragraph, 'field_embed_html'),
      // 加载js
      'loadJS' => $this->getFieldValue($paragraph, 'field_load_js'),
      // 运行js
      'runJS' => $this->getFieldValue($paragraph, 'field_run_js'),
      // 富文本内容
      'richtext' => $this->getFieldValue($paragraph, 'field_richtext')
    ];
    
    // 设置模板变量
    $variables['embed_id'] = $id;
    $variables['#attached']['drupalSettings']['textWithEmbed'][$id] = $embed_data;
  }
}

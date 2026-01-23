<?php

namespace Drupal\base\Preprocess;

use Drupal\base\Preprocess\Paragraph\TimelinePreprocessor;
use Drupal\base\Preprocess\Paragraph\TextWithEmbedPreprocessor;

/**
 * 段落预处理器管理器
 */
class ParagraphPreprocessor {
  
  /**
   * @var array 预处理器映射
   */
  protected $preprocessors = [];
  
  /**
   * 构造函数
   */
  public function __construct() {
    // 注册所有段落类型的预处理器
    $this->preprocessors = [
      'timeline' => new TimelinePreprocessor(),
      'text_with_embedding' => new TextWithEmbedPreprocessor()
    ];
  }

  /**
   * 预处理段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $type = $paragraph->getType();
    
    // 调用对应的预处理器
    if (isset($this->preprocessors[$type])) {
      $this->preprocessors[$type]->preprocess($variables);
    }
  }
}

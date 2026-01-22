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
      'text-with-embedding' => new TextWithEmbedPreprocessor()
    ];
  }

  // 创建一个自定义的调试方法
  private function getParagraphDebugInfo($paragraph) {
    $info = [
      'id' => $paragraph->id(),
      'bundle' => $paragraph->bundle(),
      'type' => $paragraph->getType(),
      'label' => $paragraph->label(),
      'fields' => [],
    ];
    
    // 获取所有字段信息
    $fields = $paragraph->getFields();
    foreach ($fields as $field_name => $field) {
      $info['fields'][$field_name] = [
        'type' => $field->getFieldDefinition()->getType(),
        'value' => $field->getValue(),
        'is_empty' => $field->isEmpty(),
      ];
    }
    
    return $info;
  }
  
  /**
   * 预处理段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $type = $paragraph->getType();

    // // 确保 Devel 模块已启用
    // if (\Drupal::moduleHandler()->moduleExists('devel')) {
    //   // 使用 Devel 服务
    //   $devel_dumper = \Drupal::service('devel.dumper');
    //   $devel_dumper->message($paragraph, 'Paragraph Debug');
      
    //   // 或者使用 dump() 方法（如果可用）
    //   // $devel_dumper->dump($paragraph);
    // }
    
    // 调用对应的预处理器
    if (isset($this->preprocessors[$type])) {
      $this->preprocessors[$type]->preprocess($variables);
    }
  }
}

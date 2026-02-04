<?php
namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Countdown 段落预处理器
 */
class CountdownPreprocessor extends BaseParagraphPreprocessor {
  
  /**
   * 预处理 Countdown 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('countdown_', true);
    
    // 获取目标日期时间
    $target_datetime_iso = '';

    // 获取时间戳值
    $timestamp = $this->getFieldValue($paragraph, 'field_datetime');

    if (!empty($timestamp)) {
      // 创建DrupalDateTime对象并格式化为ISO 8601
      $target_datetime = DrupalDateTime::createFromTimestamp($timestamp);
      $target_datetime_iso = $target_datetime->format('c'); // 'c' 格式就是 ISO 8601
    }

    // 设置模板变量
    $variables['countdown_id'] = $id;
    $variables['#attached']['drupalSettings']['countdown'][$id] = $target_datetime_iso;
  }
}
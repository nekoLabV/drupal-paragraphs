<?php
namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Countdown 段落预处理器
 */
class CountdownPreprocessor {
  
  /**
   * 预处理 Countdown 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    
    // 获取目标日期时间
    $target_datetime_iso = '';
    
    if ($paragraph->hasField('field_datetime') && !$paragraph->get('field_datetime')->isEmpty()) {
      // 获取时间戳值
      $timestamp = $paragraph->get('field_datetime')->value;
      
      if (!empty($timestamp)) {
        
        // 创建DrupalDateTime对象并格式化为ISO 8601
        $target_datetime = DrupalDateTime::createFromTimestamp($timestamp);
        $target_datetime_iso = $target_datetime->format('c'); // 'c' 格式就是 ISO 8601
      }
    }

    $paragraph_id = $paragraph->id() ?? microtime();
    
    // 设置模板变量
    $variables['countdown_id'] = $paragraph_id;
    $variables['#attached']['drupalSettings']['countdown'][$paragraph_id] = $target_datetime_iso;
  }
}
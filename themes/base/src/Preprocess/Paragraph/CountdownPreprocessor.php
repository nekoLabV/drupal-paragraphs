<?php
namespace Drupal\base\Preprocess\Paragraph;

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
    $target_timestamp = null;
    
    if ($paragraph->hasField('field_datetime') && !$paragraph->get('field_datetime')->isEmpty()) {
      // 获取时间戳值
      $timestamp = $paragraph->get('field_datetime')->value;
      
      if (!empty($timestamp)) {
        $target_timestamp = $timestamp;
        
        // 创建DrupalDateTime对象并格式化为ISO 8601
        $target_datetime = DrupalDateTime::createFromTimestamp($timestamp);
        $target_datetime_iso = $target_datetime->format('c'); // 'c' 格式就是 ISO 8601
        
        // 计算剩余时间（秒数）
        $now = new DrupalDateTime();
        $remaining_seconds = max(0, $target_datetime->getTimestamp() - $now->getTimestamp());
      }
    }

    $paragraph_id = $paragraph->id() ?? microtime();
    
    // 设置模板变量
    $variables['countdown_timestamp_iso'] = $target_datetime_iso;
    $variables['countdown_timestamp'] = $target_timestamp;
    $variables['countdown_remaining_seconds'] = $remaining_seconds ?? 0;
    $variables['countdown_id'] = $paragraph_id;
    
    // 生成JSON数据
    $json_options = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT;
    $variables['countdown_data_json'] = json_encode([
      'iso' => $target_datetime_iso,
      'timestamp' => $target_timestamp,
      'remaining' => $remaining_seconds ?? 0,
    ], $json_options);
    
    // 添加库
    $variables['#attached']['library'][] = 'base/theme-components';
    $variables['#attached']['drupalSettings']['countdown'][$paragraph_id] = $target_datetime_iso;
  }
}
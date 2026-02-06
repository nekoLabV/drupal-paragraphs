<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Timeline 段落预处理器
 */
class TimelinePreprocessor extends BaseParagraphPreprocessor {
  
  /**
   * 预处理 Timeline 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('timeline_', true);
    
    // 收集数据
    $timeline_items = [];
    
    if ($paragraph->hasField('field_timeline_items')) {
      $item_paragraphs = $paragraph->get('field_timeline_items')->referencedEntities();
      
      foreach ($item_paragraphs as $item) {
        $item_data = [
          'date' => '',
          'startTime' => '',
          'endTime' => '',
          'title' => '',
          'subtitle' => '',
          'description' => '',
          'tags' => []
        ];
        
        // 获取日期 - 使用更安全的方法
        if ($item->hasField('field_date') && !$item->get('field_date')->isEmpty()) {
          $date_value = $item->get('field_date')->getString();
          if (!empty($date_value)) {
            // 尝试多种可能的格式
            $date = $this->parseDateString($date_value);
            if ($date) {
              $item_data['date'] = $date->format('Y-m-d');
            }
          }
        }

        // 获取时间范围
        if ($item->hasField('field_time_range') && !$item->get('field_time_range')->isEmpty()) {
          $time_range = $item->get('field_time_range')->getValue()[0];
          
          if (!empty($time_range['value'])) {
            $start_time = $this->parseDateString($time_range['value']);
            if ($start_time) {
              $item_data['startTime'] = $start_time->format('H:i');
            }
          }
          
          if (!empty($time_range['end_value'])) {
            $end_time = $this->parseDateString($time_range['end_value']);
            if ($end_time) {
              $item_data['endTime'] = $end_time->format('H:i');
            }
          }
        }
        
        // 获取标题
        $item_data['title'] = $this->getFieldValue($item, 'field_title');
        
        // 获取副标题
        $item_data['subtitle'] = $this->getFieldValue($item, 'field_subtitle');
        
        // 获取描述
        $item_data['description'] = $this->getFieldValue($item, 'field_description');
        
        // 获取标签
        if ($item->hasField('field_timeline_item_tags') && !$item->get('field_timeline_item_tags')->isEmpty()) {
          $tags = [];
          $tag_entities = $item->get('field_timeline_item_tags')->referencedEntities();
          foreach ($tag_entities as $tag) {
            $tags[] = $tag->label();
          }
          $item_data['tags'] = $tags;
        }
        
        $timeline_items[] = $item_data;
      }
    }
    
    $variables['timeline_id'] = $id;
    $variables['#attached']['drupalSettings']['timeline'][$id] = $timeline_items;
  }
  
  /**
   * 解析日期字符串
   */
  protected function parseDateString($date_string) {
    if (empty($date_string)) {
      return null;
    }
    
    // 尝试常见的Drupal日期格式
    $formats = [
      'Y-m-d\TH:i:s',        // 2026-02-06T12:41:31
      'Y-m-d\TH:i:s\Z',      // 2026-02-06T12:41:31Z
      'Y-m-d H:i:s',         // 2026-02-06 12:41:31
      'Y-m-d',               // 2026-02-06
      \DateTime::ISO8601,    // ISO8601格式
      \DateTime::ATOM,       // ATOM格式
    ];
    
    foreach ($formats as $format) {
      try {
        $date = \Drupal\Core\Datetime\DrupalDateTime::createFromFormat($format, $date_string, 'UTC');
        if ($date && $date->getTimestamp() > 0) {
          // 转换为站点时区
          $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
          return $date;
        }
      } catch (\Exception $e) {
        // 继续尝试下一个格式
        continue;
      }
    }
    
    // 如果以上格式都不匹配，使用strtotime作为备选
    $timestamp = strtotime($date_string);
    if ($timestamp !== false) {
      $date = \Drupal\Core\Datetime\DrupalDateTime::createFromTimestamp($timestamp, 'UTC');
      $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
      return $date;
    }
    
    return null;
  }
}
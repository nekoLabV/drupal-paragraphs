<?php

namespace Drupal\base\Preprocess\Paragraph;

/**
 * Timeline 段落预处理器
 */
class TimelinePreprocessor {
  
  /**
   * 预处理 Timeline 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    
    // 收集时间线数据
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
          'tags' => [],
        ];
        
        // 获取日期
        if ($item->hasField('field_date') && !$item->get('field_date')->isEmpty()) {
          $date_value = $item->get('field_date')->value;
          $item_data['date'] = \Drupal::service('date.formatter')->format(strtotime($date_value), 'custom', 'Y-m-d');
        }
        
        // 获取时间范围
        if ($item->hasField('field_time_range') && !$item->get('field_time_range')->isEmpty()) {
          $time_range = $item->get('field_time_range')->getValue()[0];
          if (!empty($time_range['value'])) {
            $item_data['startTime'] = \Drupal::service('date.formatter')->format(strtotime($time_range['value']), 'custom', 'H:i');
          }
          if (!empty($time_range['end_value'])) {
            $item_data['endTime'] = \Drupal::service('date.formatter')->format(strtotime($time_range['end_value']), 'custom', 'H:i');
          }
        }
        
        // 获取标题
        if ($item->hasField('field_title') && !$item->get('field_title')->isEmpty()) {
          $item_data['title'] = $item->get('field_title')->value;
        }
        
        // 获取副标题
        if ($item->hasField('field_subtitle') && !$item->get('field_subtitle')->isEmpty()) {
          $item_data['subtitle'] = $item->get('field_subtitle')->value;
        }
        
        // 获取描述
        if ($item->hasField('field_description') && !$item->get('field_description')->isEmpty()) {
          $description = $item->get('field_description')->getValue()[0];
          // 处理文本格式
          $item_data['description'] = check_markup($description['value'] ?? '', $description['format'] ?? 'basic_html');
        }
        
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
    
    // 转换为 JSON
    $json_options = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT;
    $timeline_json = json_encode($timeline_items, $json_options);

    $paragraph_id = $paragraph->id() ?? microtime();
    
    $variables['timeline_data'] = $timeline_items;
    $variables['timeline_data_json'] = $timeline_json;
    $variables['timeline_id'] = $paragraph_id;
    $variables['#attached']['library'][] = 'base/themeHooks';
    $variables['#attached']['drupalSettings']['timeline'][$paragraph_id] = $timeline_items;
  }
}

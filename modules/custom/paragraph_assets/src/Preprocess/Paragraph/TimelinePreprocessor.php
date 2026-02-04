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
          'tags' => [],
        ];
        
        // 获取日期
        $date_value = $this->getFieldValue($item, 'field_date');
        $item_data['date'] = \Drupal::service('date.formatter')->format(strtotime($date_value), 'custom', 'Y-m-d');
        
        // 获取时间范围
        $time_range = $this->getFieldValue($item, 'field_time_range');
        if (!empty($time_range['value'])) {
          $item_data['startTime'] = \Drupal::service('date.formatter')->format(strtotime($time_range['value']), 'custom', 'H:i');
        }
        if (!empty($time_range['end_value'])) {
          $item_data['endTime'] = \Drupal::service('date.formatter')->format(strtotime($time_range['end_value']), 'custom', 'H:i');
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
}

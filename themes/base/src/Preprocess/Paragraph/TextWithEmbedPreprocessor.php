<?php

namespace Drupal\base\Preprocess\Paragraph;

use Drupal\Component\Utility\Html;

/**
 * TextWithEmbedding 段落预处理器
 */
class TextWithEmbedPreprocessor {
  
  /**
   * 预处理 TextWithEmbedding 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    
    // 初始化变量
    $embed_data = [
      'embedHtml' => '',
      'loadJS' => '',
      'runJS' => '',
      'richtext' => ''
    ];
    
    // 获取嵌入的HTML
    if ($paragraph->hasField('field_embed_html') && !$paragraph->get('field_embed_html')->isEmpty()) {
      $embed_html = $paragraph->get('field_embed_html')->getValue()[0]['value'] ?? '';
      
      $embed_data['embedHtml'] = $embed_html;
    }
    
    // 获取需要加载的JS文件
    if ($paragraph->hasField('field_load_js') && !$paragraph->get('field_load_js')->isEmpty()) {
      $js_urls = '';
      $field_values = $paragraph->get('field_load_js')->getValue();
      
      foreach ($field_values as $value) {
        $js_url = trim($value['value'] ?? '');
        if (!empty($js_url)) {
          $js_urls = $js_url;
        }
      }
      
      $embed_data['loadJS'] = $js_urls;
      
      // 添加JS库到页面附加
      if (!empty($js_urls)) {
        foreach ($js_urls as $index => $js_url) {
          $variables['#attached']['html_head'][] = [
            [
              '#tag' => 'script',
              '#attributes' => [
                'src' => $js_url,
                'defer' => TRUE,
              ],
            ],
            'text_with_embed_js_' . $paragraph->id() . '_' . $index,
          ];
        }
      }
    }
    
    // 获取运行的JS代码
    if ($paragraph->hasField('field_run_js') && !$paragraph->get('field_run_js')->isEmpty()) {
      $run_js = $paragraph->get('field_run_js')->getValue()[0]['value'] ?? '';
      
      $embed_data['runJS'] = $run_js;
    }
    
    // 获取富文本内容
    if ($paragraph->hasField('field_richtext') && !$paragraph->get('field_richtext')->isEmpty()) {
      $richtext_value = $paragraph->get('field_richtext')->getValue()[0];
      $embed_data['richtext'] = check_markup(
        $richtext_value['value'] ?? '',
        $richtext_value['format'] ?? 'basic_html'
      );
    }
    
    // 生成JSON数据
    $json_options = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT;
    $embed_json = json_encode($embed_data, $json_options);

    $paragraph_id = $paragraph->id() ?? microtime();
    
    // 设置模板变量
    $variables['embed_data'] = $embed_data;
    $variables['embed_data_json'] = $embed_json;
    $variables['embed_id'] = $paragraph_id;
    
    // 添加库
    $variables['#attached']['library'][] = 'base/themeHooks';
    $variables['#attached']['drupalSettings']['textWithEmbed'][$paragraph_id] = $embed_data;
  }
}

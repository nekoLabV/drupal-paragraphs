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
      'richtext' => '',
      'uniqueId' => '',
    ];
    
    // 获取嵌入的HTML
    if ($paragraph->hasField('field_embed_html') && !$paragraph->get('field_embed_html')->isEmpty()) {
      $embed_html = $paragraph->get('field_embed_html')->getValue()[0]['value'] ?? '';
      
      // 解析HTML，提取容器ID
      $dom = Html::load($embed_html);
      $xpath = new \DOMXPath($dom);
      $div_elements = $xpath->query('//div[@id]');
      
      if ($div_elements->length > 0) {
        $container_id = $div_elements->item(0)->getAttribute('id');
        $embed_data['uniqueId'] = $container_id;
        
        // 替换容器ID为唯一ID
        $unique_id = 'embed-container-' . $paragraph->id();
        $embed_html = str_replace($container_id, $unique_id, $embed_html);
        $embed_data['uniqueId'] = $unique_id;
      }
      
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
      
      // 如果存在容器ID，替换为唯一ID
      if (!empty($embed_data['uniqueId']) && !empty($run_js)) {
        // 查找并替换所有可能的容器ID引用
        $run_js = preg_replace('/[\'"](mapContainer-\d+)[\'"]/', "'" . $embed_data['uniqueId'] . "'", $run_js);
      }
      
      $embed_data['runJS'] = $run_js;
      
      // 添加内联JS到页面附加
      if (!empty($run_js)) {
        $js_settings = [
          'text_with_embed' => [
            $embed_data['uniqueId'] => [
              'runJS' => $run_js,
            ],
          ],
        ];
        
        $variables['#attached']['drupalSettings'] = $js_settings;
        $variables['#attached']['library'][] = 'base/embed-scripts';
      }
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

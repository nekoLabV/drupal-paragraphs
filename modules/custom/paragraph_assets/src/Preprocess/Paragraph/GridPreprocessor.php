<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Grid 段落预处理器
 */
class GridPreprocessor extends BaseParagraphPreprocessor {

  /**
   * 预处理 Grid 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('grid_', true);

    // 收集数据
    $grid_data = [
      'col' => $this->getFieldValue($paragraph, 'field_grid_col')
    ];

    $variables['grid_id'] = $id;
    $variables['grid_col'] = $grid_data['col'];
    $variables['#attached']['drupalSettings']['grid'][$id] = $grid_data;
  }
}

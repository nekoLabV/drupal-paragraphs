<?php

namespace Drupal\paragraph_assets\Preprocess\Paragraph;

use Drupal\paragraph_assets\Preprocess\Paragraph\BaseParagraphPreprocessor;

/**
 * Video 段落预处理器
 */
class VideoPreprocessor extends BaseParagraphPreprocessor {

  /**
   * 预处理 Video 段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $id = $paragraph->id() ?? uniqid('video_', true);

    $media = $paragraph->get('field_video_src')->entity ?? null;

    // 收集数据
    $video_data = [
      'src' => $this->getMediaData($media, 'field_media_video_file'),
      'caption' => $this->getFieldValue($paragraph, 'field_video_caption')
    ];

    $variables['video_id'] = $id;
    $variables['#attached']['drupalSettings']['video'][$id] = $video_data;
  }
}

<?php

namespace Drupal\paragraph_assets;

use Drupal\paragraph_assets\Preprocess\Paragraph\CountdownPreprocessor;
use Drupal\paragraph_assets\Preprocess\Paragraph\ImagePreprocessor;
use Drupal\paragraph_assets\Preprocess\Paragraph\ImagesPreprocessor;
use Drupal\paragraph_assets\Preprocess\Paragraph\TimelinePreprocessor;
use Drupal\paragraph_assets\Preprocess\Paragraph\TextWithEmbedPreprocessor;
use Drupal\paragraph_assets\Preprocess\Paragraph\SwiperPreprocessor;

/**
 * 段落预处理器管理器
 */
class ParagraphManager {
  
  /**
   * @var array 预处理器映射
   */
  protected array $preprocessors = [];
  
  /**
   * 构造函数
   */
  public function __construct(
    CountdownPreprocessor $countdown,
    ImagePreprocessor $image,
    ImagesPreprocessor $images,
    SwiperPreprocessor $swiper,
    TextWithEmbedPreprocessor $textWithEmbed,
    TimelinePreprocessor $timeline
  ) {
    // 注册所有段落类型的预处理器
    $this->preprocessors = [
      'count_down' => $countdown,
      'image' => $image,
      'images' => $images,
      'timeline' => $timeline,
      'text_with_embedding' => $textWithEmbed,
      'swiper' => $swiper
    ];
  }

  /**
   * 预处理段落
   */
  public function preprocess(array &$variables): void {
    $paragraph = $variables['paragraph'];
    $type = $paragraph->getType();

    if (!isset($this->preprocessors[$type])) {
      return;
    }

    $handler = $this->preprocessors[$type];

    if (method_exists($handler, 'preprocess')) {
      $handler->preprocess($variables);
    }
  }
}

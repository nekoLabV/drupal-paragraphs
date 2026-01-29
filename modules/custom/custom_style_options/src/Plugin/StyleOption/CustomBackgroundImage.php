<?php

declare(strict_types=1);

namespace Drupal\custom_style_options\Plugin\StyleOption;

use Drupal\file\Entity\File;
use Drupal\style_options\Plugin\StyleOption\BackgroundImage;

/**
 * Custom background image plugin using CSS custom properties.
 *
 * @StyleOption(
 *   id = "custom_background_image",
 *   label = @Translation("Custom Background Image"),
 *   category = @Translation("Custom"),
 *   description = @Translation("Background image using CSS custom properties (--background-image)")
 * )
 */
class CustomBackgroundImage extends BackgroundImage {

  /**
   * {@inheritdoc}
   */
  public function build(array $build, $value = '') {
    // $this->logDebug('CustomBackgroundImage build started');

    $fid = $this->getValue('fid');

    // $this->logDebug('FID value: @fid', [
    //   '@fid' => print_r($fid, TRUE),
    // ]);

    if (empty($fid) || empty($fid[0])) {
      return $build;
    }

    /** @var \Drupal\file\Entity\File|null $file */
    $file = File::load($fid[0]);
    if (!$file) {
      return $build;
    }

    $file_url = $this->fileUrlGenerator
      ->generate($file->getFileUri())
      ->toString();

    if ($this->getConfiguration('method') === 'css') {
      // 👉 核心：输出 CSS 自定义属性
      if (!isset($build['#attributes']['style'])) {
        $build['#attributes']['style'] = [];
      }

      if (is_string($build['#attributes']['style'])) {
        $build['#attributes']['style'] = [$build['#attributes']['style']];
      }

      $build['#attributes']['style'][] =
        '--background-image: url(' . $file_url . ');';

      // $this->logDebug('CSS variable added: @url', [
      //   '@url' => $file_url,
      // ]);
    }
    else {
      // 非 css method 时，保持父类行为
      $build['#attributes']['style'][] =
        'background-image: url(' . $file_url . ');';
    }

    return $build;
  }

  /**
   * Debug logger helper.
   */
  protected function logDebug(string $message, array $context = []): void {
    \Drupal::logger('custom_style_options')->debug($message, $context);
  }

}

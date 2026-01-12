<?php

declare(strict_types=1);

namespace Drupal\custom_style_options\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\style_options\Plugin\StyleOption\BackgroundImage as BaseBackgroundImage;

/**
 * Custom background image plugin using CSS custom properties.
 *
 * Extends the base background image plugin to output CSS variables
 * instead of standard CSS properties.
 *
 * @StyleOption(
 *   id = "custom_background_image",
 *   label = @Translation("Custom Background Image"),
 *   category = @Translation("Custom"),
 *   description = @Translation("Background image using CSS custom properties (--background-image)"),
 * )
 */
class CustomBackgroundImage extends BaseBackgroundImage {

  /**
   * {@inheritdoc}
   */
  public function build(array $build, $value = ''): array {
    $this->logDebug('Build method started');
    
    $fid = $this->getValue('fid');
    $this->logDebug('FID retrieved: @fid', ['@fid' => print_r($fid, TRUE)]);
    
    // 获取文件 URL，如果无法获取则返回原样
    $fileUrl = $this->getFileUrlFromFid($fid);
    
    if (empty($fileUrl)) {
      $this->logDebug('No valid image file found or could not load file');
      return $build;
    }
    
    $this->logDebug('Processing file: @url', ['@url' => $fileUrl]);
    $this->logDebug('Configuration method: @method', 
      ['@method' => $this->getConfiguration('method') ?? 'not set']);
    
    if ($this->isCssMethod()) {
      $build = $this->buildWithCssMethod($build, $fileUrl);
    }
    else {
      $build = $this->buildWithAttributeMethod($build, $value, $fileUrl);
    }
    
    $this->logFinalResult($build);
    return $build;
  }

  /**
   * Gets the file URL from file ID.
   *
   * @param mixed $fid
   *   The file ID or array of file IDs.
   *
   * @return string|null
   *   The file URL or null if not found.
   */
  protected function getFileUrlFromFid($fid): ?string {
    // 检查 FID 是否有效
    if (empty($fid)) {
      return NULL;
    }
    
    // 处理 FID 可能是数组或单个值的情况
    if (is_array($fid)) {
      $fid = $fid[0] ?? NULL;
    }
    
    if (empty($fid) || !is_numeric($fid)) {
      return NULL;
    }
    
    // 尝试加载文件
    $file = File::load((int) $fid);
    
    if (!$file instanceof File) {
      $this->logDebug('File with FID @fid could not be loaded', ['@fid' => $fid]);
      return NULL;
    }
    
    // 检查文件是否存在
    $fileUri = $file->getFileUri();
    if (empty($fileUri)) {
      $this->logDebug('File with FID @fid has no URI', ['@fid' => $fid]);
      return NULL;
    }
    
    try {
      return $this->fileUrlGenerator->generate($fileUri)->toString();
    }
    catch (\Exception $e) {
      $this->logDebug('Error generating URL for file @fid: @error', [
        '@fid' => $fid,
        '@error' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Determines if the CSS method is configured.
   *
   * @return bool
   *   TRUE if using CSS method, FALSE otherwise.
   */
  protected function isCssMethod(): bool {
    return $this->getConfiguration('method') === 'css';
  }

  /**
   * Builds output using CSS method (custom properties).
   *
   * @param array $build
   *   The render array.
   * @param string $fileUrl
   *   The file URL.
   *
   * @return array
   *   The modified render array.
   */
  protected function buildWithCssMethod(array $build, string $fileUrl): array {
    $this->logDebug('Using CSS method - adding custom property');
    
    // 确保 style 属性存在
    if (!isset($build['#attributes']['style'])) {
      $build['#attributes']['style'] = [];
    }
    
    // 如果是数组，添加新的样式规则
    if (is_array($build['#attributes']['style'])) {
      $build['#attributes']['style'][] = sprintf('--background-image: url("%s");', $fileUrl);
    }
    // 如果是字符串，转换为数组
    elseif (is_string($build['#attributes']['style'])) {
      $existingStyle = $build['#attributes']['style'];
      $build['#attributes']['style'] = [
        $existingStyle,
        sprintf('--background-image: url("%s");', $fileUrl),
      ];
    }
    
    return $build;
  }

  /**
   * Builds output using attribute method (modifies parent output).
   *
   * @param array $build
   *   The render array.
   * @param mixed $value
   *   The plugin value.
   * @param string $fileUrl
   *   The file URL.
   *
   * @return array
   *   The modified render array.
   */
  protected function buildWithAttributeMethod(array $build, $value, string $fileUrl): array {
    $this->logDebug('Using attribute method - modifying parent output');
    
    // 获取父类的构建结果
    $build = parent::build($build, $value);
    
    // 将标准属性转换为自定义属性
    $this->convertToCustomProperties($build);
    
    return $build;
  }

  /**
   * Converts standard CSS properties to CSS custom properties.
   *
   * @param array $build
   *   The render array.
   */
  protected function convertToCustomProperties(array &$build): void {
    if (!isset($build['#attributes']['style'])) {
      return;
    }
    
    $style = &$build['#attributes']['style'];
    
    if (is_array($style)) {
      foreach ($style as &$styleRule) {
        $this->convertStyleRule($styleRule);
      }
    }
    elseif (is_string($style)) {
      $this->convertStyleRule($style);
    }
  }

  /**
   * Converts a single style rule to use custom properties.
   *
   * @param string $styleRule
   *   The style rule to convert.
   */
  protected function convertStyleRule(string &$styleRule): void {
    // 将 background-image 转换为 --background-image
    if (strpos($styleRule, 'background-image:') !== FALSE) {
      $styleRule = str_replace('background-image:', '--background-image:', $styleRule);
      $this->logDebug('Converted style rule: @rule', ['@rule' => $styleRule]);
    }
  }

  /**
   * Logs the final build result for debugging.
   *
   * @param array $build
   *   The final render array.
   */
  protected function logFinalResult(array $build): void {
    if (isset($build['#attributes']['style'])) {
      $this->logDebug('Final style attributes: @style', 
        ['@style' => print_r($build['#attributes']['style'], TRUE)]);
    }
    
    $this->logDebug('Build method completed');
  }

  /**
   * Helper method for consistent debug logging.
   *
   * @param string $message
   *   The log message.
   * @param array $context
   *   The message context.
   */
  protected function logDebug(string $message, array $context = []): void {
    \Drupal::logger('custom_style_options')->debug($message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    
    $this->logDebug('Configuration form submitted with values: @values', 
      ['@values' => print_r($values, TRUE)]);
    
    parent::submitConfigurationForm($form, $form_state);
  }

}
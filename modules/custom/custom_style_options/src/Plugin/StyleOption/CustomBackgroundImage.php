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
    
    if (!$this->hasValidImageFile($fid)) {
      $this->logDebug('No valid image file found');
      return $build;
    }
    
    $file = File::load($fid[0]);
    $fileUrl = $this->getFileUrl($file);
    
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
   * Checks if a valid image file ID exists.
   *
   * @param mixed $fid
   *   The file ID or array of file IDs.
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  protected function hasValidImageFile($fid): bool {
    return !empty($fid) 
      && is_array($fid) 
      && isset($fid[0]) 
      && is_numeric($fid[0]);
  }

  /**
   * Gets the public URL for a file entity.
   *
   * @param \Drupal\file\Entity\File $file
   *   The file entity.
   *
   * @return string
   *   The public URL.
   */
  protected function getFileUrl(File $file): string {
    $fileUri = $file->getFileUri();
    return $this->fileUrlGenerator->generate($fileUri)->toString();
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
    
    // Ensure style attribute exists.
    if (!isset($build['#attributes']['style'])) {
      $build['#attributes']['style'] = [];
    }
    
    // Add CSS custom property.
    $build['#attributes']['style'][] = sprintf('--background-image: url(%s);', $fileUrl);
    
    // Optional: Add standard property for backward compatibility.
    // $build['#attributes']['style'][] = sprintf('background-image: url(%s);', $fileUrl);
    
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
    
    // Get parent build result.
    $build = parent::build($build, $value);
    
    // Convert standard properties to custom properties.
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
    // Convert background-image to --background-image.
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
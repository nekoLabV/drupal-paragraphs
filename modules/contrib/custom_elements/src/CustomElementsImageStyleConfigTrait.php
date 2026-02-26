<?php

namespace Drupal\custom_elements;

use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\ImageStyleInterface;

/**
 * Trait to get base structure for image style config generation.
 */
trait CustomElementsImageStyleConfigTrait {

  use StringTranslationTrait;

  /**
   * The current active user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The FileUrlGenerator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Gets the current active user.
   *
   * @return \Drupal\Core\Session\AccountProxyInterface
   *   AccountProxy object of the current user.
   */
  protected function getCurrentUser() {
    if (!$this->currentUser) {
      $this->currentUser = \Drupal::currentUser();
    }

    return $this->currentUser;
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function getEntityTypeManager() {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }

    return $this->entityTypeManager;
  }

  /**
   * Gets the file url generator.
   *
   * @return \Drupal\Core\File\FileUrlGeneratorInterface
   *   The file url generator.
   */
  protected function getFileUrlGenerator() {
    if (!$this->fileUrlGenerator) {
      $this->fileUrlGenerator = \Drupal::service('file_url_generator');
    }

    return $this->fileUrlGenerator;
  }

  /**
   * Gets a base form for image_style configuration.
   *
   * @param string $image_style_name
   *   The machine name of the image style.
   */
  protected function getImageStyleConfigForm($image_style_name) {
    $image_styles = $this->loadImageStyleOptions();
    if (!isset($image_styles[$image_style_name])) {
      $image_styles[$image_style_name] = $this->t('Unknown style @style', ['@style' => $image_style_name]);
    }

    $description_link = Link::fromTextAndUrl(
      $this->t('Configure Image Styles'),
      Url::fromRoute('entity.image_style.collection')
    );

    return [
      'image_style' => [
        '#title' => $this->t('Image style'),
        '#type' => 'select',
        '#default_value' => $image_style_name,
        '#empty_option' => $this->t('None (original image)'),
        '#options' => $image_styles,
        '#description' => $description_link->toRenderable() + [
          '#attributes' => [
            'target' => ['_blank'],
          ],
          '#access' => $this->getCurrentUser()->hasPermission('administer image styles'),
        ],
      ],
    ];
  }

  /**
   * Gets a general setting summary form image_style.
   *
   * @param string $image_style_name
   *   The machine name of the image style.
   */
  protected function getSettingsSummary($image_style_name) {
    $summary = [];

    $image_styles = $this->loadImageStyleOptions();
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    // Styles could be lost because of enabled/disabled modules that defines
    // their styles in code.
    if (isset($image_styles[$image_style_name])) {
      $summary[] = $this->t('Image style: @style.', ['@style' => $image_styles[$image_style_name]]);
    }
    elseif ($image_style_name) {
      $summary[] = $this->t('WARNING: Unknown image style @style.', ['@style' => $image_style_name]);
    }
    else {
      $summary[] = $this->t('Original image.');
    }

    return $summary;
  }

  /**
   * Gets the URL for a given image style and URI.
   *
   * Helper that correctly gets the URL and respects Drupal public file config,
   * $settings['file_public_base_url']
   *
   * @param \Drupal\image\ImageStyleInterface $imageStyle
   *   The image style.
   * @param string $uri
   *   The Drupal file URI.
   *
   * @return string
   *   The generated URL, relative or absolute.
   */
  protected function getImageStyleUrl(ImageStyleInterface $imageStyle, string $uri) : string {
    // We follow
    // \Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter::viewElements
    // to correctly generate the URL.
    // _responsive_image_image_style_url() seems to do it wrong by always
    // calling file_url_transform_relative() which generates relative URLs.
    $url = Url::fromUri($imageStyle->buildUrl($uri))->toString();
    // When no base-url is configured, generate them relative as usual.
    if (!Settings::get('file_public_base_url')) {
      return $this->getFileUrlGenerator()->transformRelative($url);
    }
    return $url;
  }

  /**
   * Loads an image style.
   *
   * @param string $id
   *   The entity id.
   *
   * @return \Drupal\image\Entity\ImageStyle|null
   *   The loaded entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadImageStyle(string $id) : ?ImageStyle {
    /** @var \Drupal\image\Entity\ImageStyle $image_style */
    $image_style = $this->getEntityTypeManager()
      ->getStorage('image_style')
      ->load($id);
    return $image_style;
  }

  /**
   * Loads available image style options.
   *
   * @return array
   *   Available style options as an array.
   */
  private function loadImageStyleOptions() {
    $styles = $this->getEntityTypeManager()->getStorage('image_style')->loadMultiple();
    $options = [];

    foreach ($styles as $name => $style) {
      $options[$name] = $style->label();
    }

    if (empty($options)) {
      $options[''] = t('No defined styles');
    }

    return $options;
  }

}

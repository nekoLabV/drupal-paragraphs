<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_ce_api\File;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\lupus_decoupled_ce_api\LupusDecoupledCeApiSettingsTrait;
use Drupal\lupus_decoupled_ce_api\ApiResponseTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Generates absolute file URLs.
 *
 * @see https://www.drupal.org/node/2669074
 */
class FileUrlGenerator implements FileUrlGeneratorInterface {

  use ApiResponseTrait;
  use LupusDecoupledCeApiSettingsTrait;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Original file url generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * An array of paths to redirect to the frontend.
   *
   * Contains '/ce-api' by default and can be configured
   * via service parameters.
   *
   * @var string
   */
  protected $apiPrefix;

  /**
   * Constructs a new Lupus decoupled ce-api file URL generator object.
   *
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The decorated file URL generator.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param string $apiPrefix
   *   The api path prefix.
   */
  public function __construct(FileUrlGeneratorInterface $fileUrlGenerator, ConfigFactoryInterface $configFactory, RequestStack $request_stack, string $apiPrefix) {
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->configFactory = $configFactory;
    $this->requestStack = $request_stack;
    $this->apiPrefix = $apiPrefix;
  }

  /**
   * {@inheritdoc}
   */
  public function generateString(string $uri): string {
    if ($this->getLupusDecoupledCeApiSettings()->get('absolute_file_urls') && $this->isApiResponse($this->requestStack->getCurrentRequest())) {
      return $this->fileUrlGenerator->generateAbsoluteString($uri);
    }
    return $this->fileUrlGenerator->generateString($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function generateAbsoluteString(string $uri): string {
    return $this->fileUrlGenerator->generateAbsoluteString($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function generate(string $uri): Url {
    if ($this->getLupusDecoupledCeApiSettings()->get('absolute_file_urls') && $this->isApiResponse($this->requestStack->getCurrentRequest())) {
      $result = $this->fileUrlGenerator->generateAbsoluteString($uri);
      return Url::fromUri($result);
    }
    return $this->fileUrlGenerator->generate($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function transformRelative(string $file_url, bool $root_relative = TRUE): string {
    // Prevent transform relative here when absolute file URLS are configured.
    // This is required for things like responsive-images to work with absolute
    // URLs.
    // @see _responsive_image_image_style_url()
    if (!($this->getLupusDecoupledCeApiSettings()->get('absolute_file_urls') && $this->isApiResponse($this->requestStack->getCurrentRequest()))) {
      return $this->fileUrlGenerator->transformRelative($file_url, $root_relative);
    }
    return $file_url;
  }

}

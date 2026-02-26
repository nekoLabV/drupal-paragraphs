<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_site_info\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents Lupus Decoupled Site Info as resources.
 */
#[RestResource(
  id: "lupus_decoupled_site_info",
  label: new TranslatableMarkup("Lupus Decoupled Site Info"),
  uri_paths: [
    "canonical" => "/api/site-info",
  ]
)]
final class LupusDecoupledSiteInfoResource extends ResourceBase {

  use StringTranslationTrait;

  /**
   * The config factory service.
   */
  private readonly ConfigFactoryInterface $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   */
  public function get(): ResourceResponse {
    $site_info_settings = $this->configFactory->get('lupus_decoupled_site_info.settings');
    $caches = [CacheableMetadata::createFromObject($site_info_settings)];
    $resource = [];
    $previous_config_name = '';
    foreach ($site_info_settings->get('expose') as $config_name_and_key) {
      [$config_name, $config_key] = explode(':', $config_name_and_key);
      if (empty($config_key) || empty($config_name)) {
        continue;
      }
      if ($config_name !== $previous_config_name || !isset($config)) {
        $config = $this->configFactory->get($config_name);
        $caches[] = CacheableMetadata::createFromObject($config);
      }
      $value = $config->get($config_key);
      if (!isset($value)) {
        $this->logger->error('Miss-configured Lupus Decoupled Site Info: key @key from @name has no value. Check the configuration <pre>lupus_decoupled_site_info.settings</pre>.', [
          '@key' => $config_key,
          '@name' => $config_name,
        ]);
      }
      $resource[$config_name][$config_key] = $value;
      unset($value);
    }
    $response = new ResourceResponse($resource);
    foreach ($caches as $cache) {
      $response->addCacheableDependency($cache);
    }

    return $response;
  }

}

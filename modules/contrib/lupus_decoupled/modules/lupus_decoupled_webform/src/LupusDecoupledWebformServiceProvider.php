<?php

namespace Drupal\lupus_decoupled_webform;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Alters event subscriber parameters of lupus_decoupled_ce_api.
 */
class LupusDecoupledWebformServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Altering parameters passed to
    // Drupal\lupus_decoupled_ce_api\EventSubscriber\FrontendRedirectSubscriber.
    if ($container->hasParameter('lupus_decoupled_ce_api.frontend_routes')
      && $frontend_routes = $container->getParameter('lupus_decoupled_ce_api.frontend_routes')) {
      $frontend_routes[] = 'entity.webform.canonical';
      $frontend_routes[] = 'entity.webform.confirmation';
      $container->setParameter('lupus_decoupled_ce_api.frontend_routes', $frontend_routes);
    }
    if ($container->hasParameter('lupus_decoupled_ce_api.frontend_paths')
      && $frontend_paths = $container->getParameter('lupus_decoupled_ce_api.frontend_paths')) {
      $frontend_paths[] = '/webform/{webform}';
      $frontend_paths[] = '/webform/{webform}/confirmation';
      $container->setParameter('lupus_decoupled_ce_api.frontend_paths', $frontend_paths);
    }
    if ($container->hasParameter('lupus_decoupled_ce_api.frontend.keep_frontend_paths')
      && $frontend_paths = $container->getParameter('lupus_decoupled_ce_api.frontend.keep_frontend_paths')) {
      $frontend_paths[] = '/webform/{webform}';
      $frontend_paths[] = '/webform/{webform}/confirmation';
      $container->setParameter('lupus_decoupled_ce_api.frontend.keep_frontend_paths', $frontend_paths);
    }
  }

}

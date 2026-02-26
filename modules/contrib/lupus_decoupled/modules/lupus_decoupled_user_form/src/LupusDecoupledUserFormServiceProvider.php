<?php

namespace Drupal\lupus_decoupled_user_form;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies the session manager service.
 */
class LupusDecoupledUserFormServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('session_manager')) {
      $definition = $container->getDefinition('session_manager');
      $arguments = $definition->getArguments();

      // Replace the session_configuration argument with our custom service.
      $arguments[3] = new Reference('lupus_decoupled_user_form.session_configuration');
      $definition->setArguments($arguments);
    }
  }

}

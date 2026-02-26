<?php

namespace Drupal\trusted_redirect;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Trait providing helpers for checking trusted hosts.
 */
trait TrustedRedirectHelpersTrait {

  /**
   * List of trusted hosts.
   *
   * @var array
   */
  protected $trustedHosts;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Sets the config factory.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   *
   * @return $this
   */
  public function setConfigFactory(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    return $this;
  }

  /**
   * Gets the config factory.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   */
  public function getConfigFactory() {
    if (empty($this->configFactory)) {
      $this->configFactory = \Drupal::service('config.factory');
    }
    return $this->configFactory;
  }

  /**
   * Sets the module handler.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   *
   * @return $this
   */
  public function setModuleHandler(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
    return $this;
  }

  /**
   * Gets the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  public function getModuleHandler() {
    if (empty($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::service('module_handler');
    }
    return $this->moduleHandler;
  }

  /**
   * Obtain list of trusted hosts.
   *
   * @return string[]
   *   List of trusted hosts.
   */
  protected function getTrustedHosts() {
    if (!isset($this->trustedHosts)) {
      $this->trustedHosts = $this->getConfigFactory()
        ->get('trusted_redirect.settings')
        ->get('trusted_hosts');
      $this->getModuleHandler()->alter('trusted_redirect_hosts', $this->trustedHosts);
    }
    return $this->trustedHosts;
  }

  /**
   * Evaluates whether destination url is trusted or not.
   *
   * @param string $url
   *   The url, e.g. from the destination parameter.
   *
   * @return bool
   *   Whether destination url is trusted or not.
   */
  protected function isTrustedUrl($url) {
    $trusted_hosts = $this->getTrustedHosts();
    $url_info = parse_url($url);
    if (!isset($url_info['host'])) {
      return FALSE;
    }
    return in_array($url_info['host'], $trusted_hosts);
  }

}

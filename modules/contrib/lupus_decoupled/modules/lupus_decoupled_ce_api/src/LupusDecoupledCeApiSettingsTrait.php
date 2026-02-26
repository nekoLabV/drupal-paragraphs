<?php

namespace Drupal\lupus_decoupled_ce_api;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides access to the Lupus Decoupled Ce Api Settings.
 *
 * Requires configFactory property on the class where it is used.
 */
trait LupusDecoupledCeApiSettingsTrait {

  /**
   * The lupus_decoupled_ce_api.settings configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $lupusDecoupledCeApiSettings;

  /**
   * Getter method for lupus_decoupled_ce_api.settings configuration.
   *
   * Improves performance by retrieving the configuration only once per
   * request.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The lupus_decoupled_ce_api.settings config.
   */
  protected function getLupusDecoupledCeApiSettings() {
    if (!empty($this->lupusDecoupledCeApiSettings)) {
      return $this->lupusDecoupledCeApiSettings;
    }
    if (!isset($this->configFactory) || !($this->configFactory instanceof ConfigFactoryInterface)) {
      throw new \LogicException('No config factory available for LupusDecoupledCeApiSettingsTrait');
    }
    $this->lupusDecoupledCeApiSettings = $this->configFactory->get('lupus_decoupled_ce_api.settings');
    return $this->lupusDecoupledCeApiSettings;
  }

}

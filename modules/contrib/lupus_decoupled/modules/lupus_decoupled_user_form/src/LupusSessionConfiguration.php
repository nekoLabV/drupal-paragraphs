<?php

namespace Drupal\lupus_decoupled_user_form;

use Drupal\Core\Session\SessionConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides custom session configuration for Lupus Decoupled User Form.
 *
 * This class extends Drupal's core SessionConfiguration to modify the behavior
 * of getCookieDomain(). When no cookie_domain is explicitly set in the
 * configuration, it returns NULL instead of defaulting to the current host.
 * This allows the browser to automatically use the current domain as the
 * cookie domain, which is more suitable for decoupled architectures.
 */
class LupusSessionConfiguration extends SessionConfiguration {

  /**
   * {@inheritdoc}
   */
  protected function getCookieDomain(Request $request) {
    // If a cookie_domain is explicitly set in the configuration, use it.
    if (!empty($this->options['cookie_domain'])) {
      return parent::getCookieDomain($request);
    }

    // Otherwise, return NULL to let the browser set the cookie domain.
    return NULL;
  }

}

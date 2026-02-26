<?php

namespace Drupal\trusted_redirect\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\Component\HttpFoundation\SecuredRedirectResponse;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\trusted_redirect\TrustedRedirectHelpersTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Trusted redirect subscriber to redirect to trusted hosts.
 */
class TrustedRedirectSubscriber implements EventSubscriberInterface {

  use TrustedRedirectHelpersTrait;

  /**
   * Redirect to trusted host.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespondRedirectToTrustedHost(ResponseEvent $event) {
    $response = $event->getResponse();
    if ($response instanceof RedirectResponse) {
      $request = $event->getRequest();

      // Backwards-compatibility for 'trusted_destination' parameter:
      $trusted_destination = $request->get('trusted_destination');
      if ($trusted_destination && $this->isTrustedUrl($trusted_destination)) {
        // Redirect to trusted destination.
        $response->setTargetUrl($trusted_destination);
        $event->stopPropagation();
      }

      // Before RedirectResponseSubscriber is checking for secure redirects,
      // we do it first and mark redirects to trusted hosts as trusted.
      // First handle the destination parameter, then regular redirects.
      $destination = $this->getDestinationParameter($request);
      if ($destination && UrlHelper::isExternal($destination) && $this->isTrustedUrl($destination)) {
        $safe_response = TrustedRedirectResponse::createFromRedirectResponse($response);
        $safe_response->setTargetUrl($destination);
        $event->setResponse($safe_response);
      }
      elseif (!($response instanceof SecuredRedirectResponse) && $this->isTrustedUrl($response->getTargetUrl())) {
        $safe_response = TrustedRedirectResponse::createFromRedirectResponse($response);
        $event->setResponse($safe_response);
      }
    }
  }

  /**
   * Work-a-round to get the destination parameter.
   *
   * When saving an entity form, somehow
   * \Drupal\Core\Path\PathValidator::getPathAttributes triggers the
   * destination parameter to be lost when it's an absolute URL. So we need to
   * parse it out from the given request URI.
   *
   * @return string|null
   *   The destination parameter if any.
   */
  private function getDestinationParameter(Request $request) {
    $new_request = Request::create($request->getRequestUri());
    return $new_request->query->get('destination');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Priority of 1 to run before RedirectResponseSubscriber to act before
    // destination parameter is processed and evaluated with exception.
    $events[KernelEvents::RESPONSE][] = ['onRespondRedirectToTrustedHost', 1];
    return $events;
  }

}

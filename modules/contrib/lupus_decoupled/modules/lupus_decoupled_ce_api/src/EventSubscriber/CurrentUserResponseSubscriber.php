<?php

namespace Drupal\lupus_decoupled_ce_api\EventSubscriber;

use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Adds the current user information to ce-api responses.
 */
class CurrentUserResponseSubscriber implements EventSubscriberInterface {
  /**
   * The current user session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs the subscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Adds current user data to the API response.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event.
   */
  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();

    // Ensure we are modifying a CustomElementsJsonResponse and not a redirect.
    if (
      $response instanceof CustomElementsJsonResponse &&
      !$response->isRedirect()
    ) {
      $user = $this->currentUser->getAccount();

      $currentUserData = [
        'current_user' => [
          'id' => $user->id(),
          'name' => $user->getDisplayName(),
          'roles' => $user->getRoles(),
        ],
      ];

      // Merge the existing response data with the new user data.
      $response_data = $response->getResponseData();
      $response->setData($response_data + $currentUserData);

      // Usually the data added to authenticated responses would not be cached
      // since we add this data AFTER dynamic page cache has run. Still we
      // update cacheability metadata to ensure it gets cached correctly per
      // user in case some other caching layer kicks in.
      $user_metadata = (new CacheableMetadata())
        ->setCacheContexts(['user'])
        ->setCacheTags(['user:' . $user->id()]);
      $response->addCacheableDependency($user_metadata);
    }
  }

  /**
   * {@inheritdoc}
   *
   * This subscriber listens for the RESPONSE event with a priority of 6.
   * The priority ensures that it runs AFTER the Dynamic Page Cache (7) has
   * processed the response. This is important because we want to modify the
   * response ONLY AFTER it has been cached, ensuring that user-specific data
   * is correctly added without being overridden by caching mechanisms.
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onResponse', 6],
    ];
  }

}

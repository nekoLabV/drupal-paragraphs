<?php

namespace Drupal\Tests\lupus_decoupled_ce_api\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests event subscriber priority ordering.
 *
 * @group lupus_decoupled_ce_api
 */
class EventSubscriberOrderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'path_alias',
    'lupus_decoupled_ce_api',
    'dynamic_page_cache',
  ];

  /**
   * Tests that our subscriber runs after dynamic page cache subscriber.
   */
  public function testEventSubscriberPriority() {
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher */
    $event_dispatcher = $this->container->get('event_dispatcher');

    // Get all listeners for the response event.
    $listeners = $event_dispatcher->getListeners('kernel.response');

    $dynamic_cache_position = NULL;
    $current_user_response_subscriber = NULL;

    foreach ($listeners as $index => $listener) {
      $listener_class = get_class($listener[0]);

      if ($listener_class === 'Drupal\dynamic_page_cache\EventSubscriber\DynamicPageCacheSubscriber') {
        $dynamic_cache_position = $index;
      }

      if ($listener_class === 'Drupal\lupus_decoupled_ce_api\EventSubscriber\CurrentUserResponseSubscriber') {
        $current_user_response_subscriber = $index;
      }
    }

    $this->assertNotNull($dynamic_cache_position);
    $this->assertNotNull($current_user_response_subscriber);
    $this->assertGreaterThan($dynamic_cache_position, $current_user_response_subscriber,
      'The current user response subscriber should run after dynamic page cache subscriber');
  }

}

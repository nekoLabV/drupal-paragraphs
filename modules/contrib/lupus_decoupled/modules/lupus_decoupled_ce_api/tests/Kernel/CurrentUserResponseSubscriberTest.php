<?php

namespace Drupal\Tests\lupus_decoupled_ce_api\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\lupus_decoupled_ce_api\EventSubscriber\CurrentUserResponseSubscriber;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse;

/**
 * Tests the CurrentUserResponseSubscriber.
 *
 * @group lupus_decoupled_ce_api
 */
class CurrentUserResponseSubscriberTest extends KernelTestBase {

  /**
   * Modules required for this test.
   *
   * @var array<string>
   */
  protected static $modules = ['system', 'user'];

  /**
   * The mock user session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * The event subscriber instance.
   *
   * @var \Drupal\lupus_decoupled_ce_api\EventSubscriber\CurrentUserResponseSubscriber
   */
  protected $subscriber;

  /**
   * Setup the test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a mock user.
    $this->currentUser = $this->createMock(AccountProxyInterface::class);
    $mock_user = $this->createMock(AccountInterface::class);

    // Set expectations for user data.
    $mock_user->method('id')->willReturn(1);
    $mock_user->method('getDisplayName')->willReturn('Test User');
    $mock_user->method('getRoles')->willReturn(['authenticated']);

    $this->currentUser->method('getAccount')->willReturn($mock_user);

    // Initialize the subscriber.
    $this->subscriber = new CurrentUserResponseSubscriber($this->currentUser);
  }

  /**
   * Tests that user session data is added to the response.
   */
  public function testOnResponse(): void {
    // Create a mock response.
    $response = new CustomElementsJsonResponse(['existing_data' => 'test']);

    // Simulate the event trigger.
    $event = new ResponseEvent(
      $this->container->get('http_kernel'),
      $this->container->get('request_stack')->getCurrentRequest(),
      1,
      $response
    );
    $this->subscriber->onResponse($event);

    // Decode the response to check the output.
    $response_data = json_decode($response->getContent(), TRUE);

    // Assertions: Ensure user data is added.
    $this->assertArrayHasKey('current_user', $response_data);
    $this->assertEquals(1, $response_data['current_user']['id']);
    $this->assertEquals('Test User', $response_data['current_user']['name']);
    $this->assertEquals(['authenticated'], $response_data['current_user']['roles']);
  }

}

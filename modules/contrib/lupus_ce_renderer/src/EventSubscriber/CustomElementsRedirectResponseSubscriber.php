<?php

namespace Drupal\lupus_ce_renderer\EventSubscriber;

use Drupal\Core\Messenger\MessengerTrait;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Site\Settings;
use Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles redirects and provides them as explicit API responses.
 */
class CustomElementsRedirectResponseSubscriber implements EventSubscriberInterface {

  use MessengerTrait;

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
   * Constructs the object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, ConfigFactoryInterface $configFactory) {
    $this->moduleHandler = $moduleHandler;
    $this->configFactory = $configFactory;
  }

  /**
   * Handles the response.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    $result = $event->getResponse();
    $request = $event->getRequest();

    // When custom elements rendering is requested, handle the redirect and
    // provide them as explicit API responses. So the client may forward the
    // request to the browser.
    if (
      $result instanceof RedirectResponse &&
      ($request->getRequestFormat() == 'custom_elements' || $request->attributes->get('lupus_ce_renderer') || Settings::get('lupus_ce_renderer_enable', FALSE))
    ) {
      $data = [];
      $url = $result->getTargetUrl();

      $data['redirect'] = [
        'external' => UrlHelper::isExternal($url),
        'url' => $url,
        'statusCode' => $result->getStatusCode(),
      ];

      // Conditionally add drupal messages to the response. See README for
      // details on this setting.
      $add_messages = $this->configFactory->get('lupus_ce_renderer.settings')->get('redirect_response.add_drupal_messages');
      $data['messages'] = $add_messages ? $this->getMessages() : [];
      $bubbleable_metadata = new BubbleableMetadata();
      if ($result instanceof CacheableResponseInterface) {
        $bubbleable_metadata->addCacheableDependency($result->getCacheableMetadata());
      }
      $this->moduleHandler->alter('lupus_ce_renderer_response', $data, $bubbleable_metadata, $request);
      $response = new CustomElementsJsonResponse();
      $response->setData($data);
      if (!empty($result->headers->get('cache-control'))) {
        $result->headers->remove('location');
        $result->headers->remove('content-type');
        $response->headers->add($result->headers->all());
      }
      $response->addCacheableDependency($bubbleable_metadata);
      $event->setResponse($response);
    }
  }

  /**
   * Get drupal messages.
   *
   * @return array
   *   Array of messages.
   */
  private function getMessages() : array {
    $messages = $this->messenger()->all();
    $this->messenger()->deleteAll();
    if (!empty($messages['status'])) {
      $messages['success'] = $messages['status'];
      unset($messages['status']);
    }
    return $messages;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // Priority -11 to run after event subscriber from http_cache_control,
    // or the ones from core.
    $events[KernelEvents::RESPONSE][] = ['onResponse', -11];
    return $events;
  }

}

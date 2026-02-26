<?php

namespace Drupal\lupus_decoupled_views\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\views\Element\View;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use drunomics\ServiceUtils\Core\Render\RendererTrait;

/**
 * Controller for decoupled Views.
 */
class ViewsController extends ControllerBase {

  use RendererTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a ViewsController object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Renders Views pages into custom elements.
   *
   * @param string $view_id
   *   The ID of the view.
   * @param string $display_id
   *   The ID of the display.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Return Custom element object.
   *
   * @throws \Exception
   */
  public function viewsView(string $view_id, string $display_id, RouteMatchInterface $route_match) {
    $args = [];
    $route = $route_match->getRouteObject();
    $map = $route->hasOption('_view_argument_map') ? $route->getOption('_view_argument_map') : [];

    foreach ($map as $attribute => $parameter_name) {
      // Allow parameters be pulled from the request.
      // The map stores the actual name of the parameter in the request. Views
      // which override existing controller, use for example 'node' instead of
      // arg_nid as name.
      if (isset($map[$attribute])) {
        $attribute = $map[$attribute];
      }
      if ($arg = $route_match->getRawParameter($attribute)) {
      }
      else {
        $arg = $route_match->getParameter($attribute);
      }

      if (isset($arg)) {
        $args[] = $arg;
      }
    }

    // Start the rendering process via the render element
    // \Drupal\views\Element\View - as done by
    // \Drupal\views\Routing\ViewPageController::handle.
    $render = DisplayPluginBase::buildBasicRenderable($view_id, $display_id, $args);
    $render = View::preRenderViewElement($render);
    if (!empty($render['view_build']['#custom_element'])) {
      return $render['view_build']['#custom_element'];
    }
    throw new \Exception(sprintf("Views display plugin '%s' is not recognized as being able to output custom elements format.", $route->getOption('_view_display_plugin_id')));
  }

}

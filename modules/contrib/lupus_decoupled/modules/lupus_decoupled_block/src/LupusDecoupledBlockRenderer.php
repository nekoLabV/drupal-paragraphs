<?php

namespace Drupal\lupus_decoupled_block;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\block\BlockRepositoryInterface;
use Drupal\custom_elements\CustomElementsBlockRenderHelperTrait;
use Drupal\lupus_ce_renderer\CustomElementsRenderer;
use drunomics\ServiceUtils\Core\Render\RendererTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides support functionality for the lupus_decoupled_block module.
 */
class LupusDecoupledBlockRenderer {

  use RendererTrait;
  use CustomElementsBlockRenderHelperTrait;

  /**
   * The block.repository service.
   *
   * @var \Drupal\block\BlockRepositoryInterface
   */
  protected $blockRepository;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The custom elements renderer.
   *
   * @var \Drupal\lupus_ce_renderer\CustomElementsRenderer
   */
  protected $customElementsRenderer;

  /**
   * Constructs a LupusDecoupledBlockRenderer object.
   *
   * @param \Drupal\block\BlockRepositoryInterface $block_repository
   *   The block.repository service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\lupus_ce_renderer\CustomElementsRenderer $custom_elements_renderer
   *   The custom elements renderer service.
   */
  public function __construct(BlockRepositoryInterface $block_repository, RequestStack $request_stack, CustomElementsRenderer $custom_elements_renderer) {
    $this->blockRepository = $block_repository;
    $this->requestStack = $request_stack;
    $this->customElementsRenderer = $custom_elements_renderer;
  }

  /**
   * Gets blocks data.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface|null $cacheableDependency
   *   The cacheable dependency object.
   *
   * @return array
   *   Array of blocks data, by region. Format depends on content format:
   *   - Markup: Rendered HTML strings
   *   - JSON: Normalized custom element data arrays
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getBlocks(?RefinableCacheableDependencyInterface $cacheableDependency = NULL) {
    $output = [];
    $request = $this->requestStack->getCurrentRequest();
    $content_format = $request ? $this->customElementsRenderer->getContentFormatFromRequest($request) : CustomElementsRenderer::CONTENT_FORMAT_MARKUP;

    $blocksPerRegion = $this->blockRepository->getVisibleBlocksPerRegion();
    foreach ($blocksPerRegion as $region => $blocks) {
      foreach ($blocks as $block) {
        if ($render = $block->getPlugin()->build()) {
          // Check if there's already a custom element at root level.
          if (isset($render['#custom_element'])) {
            $customElement = $render['#custom_element'];
          }
          else {
            // Convert block render array to custom element, using the helper
            // trait which properly detects existing custom elements in content.
            $customElement = $this->convertBlockRenderArray($render);
          }

          if ($content_format === CustomElementsRenderer::CONTENT_FORMAT_MARKUP) {
            $build = $customElement->toRenderArray();
            $block_output = $this->getrenderer()
              ->renderRoot($build);
          }
          else {
            $block_output = $this->customElementsRenderer
              ->getCustomElementNormalizer()
              ->normalize($customElement);
          }
          $output[$region][$block->id()] = $block_output;
        }

        if ($cacheableDependency) {
          $cacheableDependency->addCacheableDependency($block->getPlugin());
          $cacheableDependency->addCacheableDependency($block);
        }
      }
      if (!empty($output[$region])) {
        $output[$region] = array_filter($output[$region]);
      }
    }
    if (!empty($output)) {
      $output = array_filter($output);
    }
    return $output;
  }

}

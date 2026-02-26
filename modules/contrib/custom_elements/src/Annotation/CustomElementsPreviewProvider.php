<?php

namespace Drupal\custom_elements\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Custom Elements Preview Provider plugin annotation object.
 *
 * @see \Drupal\custom_elements\Plugin\CustomElementsPreviewProviderInterface
 * @see \Drupal\custom_elements\CustomElementsPreviewProviderManager
 * @see plugin_api
 *
 * @Annotation
 */
class CustomElementsPreviewProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the preview provider.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A description of the preview provider.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}

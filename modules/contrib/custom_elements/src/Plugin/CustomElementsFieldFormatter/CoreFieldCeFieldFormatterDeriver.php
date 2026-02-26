<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives plugin instances for using core field formatters as CE formatters.
 */
class CoreFieldCeFieldFormatterDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The core field formatter manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_manager
   *   The formatter manager.
   */
  public function __construct(FormatterPluginManager $formatter_manager) {
    $this->formatterManager = $formatter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.field.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if (empty($this->derivatives)) {
      foreach ($this->formatterManager->getDefinitions() as $id => $formatter_definition) {
        $this->derivatives[$id] = [
          'label' => $this->t('Formatted: @label', ['@label' => $formatter_definition['label']]),
          'field_types' => $formatter_definition['field_types'],
          'weight' => $formatter_definition['weight'] ?? 0,
        ] + $base_plugin_definition;
      }
    }
    return $this->derivatives;
  }

}

<?php

namespace Drupal\custom_elements\Entity;

use Drupal\Core\Entity\Display\EntityDisplayInterface;

/**
 * Provides a common interface for entity view displays.
 */
interface EntityCeDisplayInterface extends EntityDisplayInterface {

  /**
   * Gets the flag to force building using layout builder.
   *
   * When enabled, and when Layout Builder is enabled on the curresponding
   * Core entity view display. the component options of individual fields are
   * ignored. Instead, custom elements are built using the layout.
   *
   * @return bool
   *   Whether auto-processing is forced.
   */
  public function getUseLayoutBuilder(): bool;

  /**
   * Sets the flag to force building using layout builder.
   *
   * @param bool $status
   *   The status.
   *
   * @return $this
   */
  public function setUseLayoutBuilder(bool $status): self;

  /**
   * Gets the flag to force-enable auto processing for the entity.
   *
   * When enabled, the component options of individual fields are ignored.
   * Instead, automatic processing using CE-processors is applied on entity
   * level, such that processors can take complete control on how to process
   * an entity. This is the same behaviour as it was default in 2.x and may
   * be used for backwards-compatibility.
   *
   * @return bool
   *   Whether auto-processing is forced.
   */
  public function getForceAutoProcessing(): bool;

  /**
   * Sets the flag to force-enable auto processing for the entity.
   *
   * @param bool $status
   *   The status.
   *
   * @return $this
   */
  public function setForceAutoProcessing(bool $status): self;

  /**
   * Gets the configured custom element name.
   *
   * @return string
   *   The entity type id.
   */
  public function getCustomElementName(): string;

  /**
   * Sets the custom element name to be displayed.
   *
   * @param string $name
   *   The custom element name to be displayed.
   *
   * @return $this
   */
  public function setCustomElementName(string $name): self;

  /**
   * {@inheritDoc}
   *
   * @return \Drupal\custom_elements\CustomElementsFieldFormatterInterface|null
   *   The plugin, or NULL.
   */
  public function getRenderer($field_name);

}

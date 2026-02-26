<?php

namespace Drupal\custom_elements\Plugin\CustomElementsFieldFormatter;

use drunomics\ServiceUtils\Core\Language\LanguageManagerTrait;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\custom_elements\CustomElementsFieldFormatterBase;
use Drupal\path\Plugin\Field\FieldType\PathItem;

/**
 * Implementation of the 'link' custom element formatter plugin.
 *
 * @CustomElementsFieldFormatter(
 *   id = "path",
 *   label = @Translation("URL"),
 *   field_types = {
 *     "path"
 *   },
 *   weight = -20
 * )
 */
class PathFieldFormatter extends CustomElementsFieldFormatterBase {

  use LanguageManagerTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldItemListInterface $items, CustomElement $custom_element, $langcode = NULL) {
    $field_item = $items->first();
    assert($field_item instanceof PathItem);
    // The path-item is acutally hard-coded to use $entity->toUrl() +
    // the language for the alias. Since for URL generation it's better to
    // is the internal URL than the alias, let's just use that.
    $url = $field_item->getEntity()->toUrl();

    // Apply path alias language, if available.
    if ($field_item->langcode) {
      $url->setOption('language', $this->getLanguageManager()->getLanguage($field_item->langcode));
    }
    if ($this->configuration['absolute']) {
      $url->setAbsolute();
    }

    if ($this->isSlot()) {
      $custom_element->addSlot($this->getName(), $url->toString());
    }
    else {
      $custom_element->setAttribute($this->getName(), $url->toString());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'absolute' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['absolute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Absolute URL'),
      '#default_value' => FALSE,
      '#description' => $this->t('If checked, the URL will be absolute, otherwise it will be relative.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['absolute'] = $form_state->getValue('absolute');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->configuration['absolute'] ? $this->t('Absolute URL') : $this->t('Relative URL');
    return $summary;
  }

}

<?php

namespace Drupal\custom_elements;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Formats a custom element structure into an array.
 */
class CustomElementNormalizer implements NormalizerInterface {

  /**
   * Constructs a CustomElementNormalizer object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * Supported context parameters:
   * - 'cache_metadata' (BubbleableMetadata): Object to collect cache metadata
   *   from the custom element and its nested elements. If not provided, a new
   *   BubbleableMetadata instance will be created internally.
   * - 'key_casing' (string): Controls the casing of array keys in the output.
   *   Set to 'ignore' to preserve original key casing (with underscores).
   *   By default, keys are converted to camelCase for nicer JavaScript usage.
   * - 'json_format' (string): Override the configured JSON format.
   *   Values: 'explicit' or 'legacy'.
   *   - 'explicit': Separates props and slots:
   *     {element, props: {...}, slots: {...}}
   *   - 'legacy': Mixes props and slots at root:
   *     {element, prop1, prop2, slot1}
   *   If not provided, uses the 'json_format' setting from module
   *   configuration (admin/config/system/custom-elements).
   */
  public function normalize(mixed $object, ?string $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|null {
    $cache_metadata = $context['cache_metadata'] ?? new BubbleableMetadata();

    $result = $this->normalizeCustomElement($object, $cache_metadata, $context);

    // By default, convert keys in the outer result array to be valid JS
    // identifiers. (Actually,
    // https://vuejs.org/guide/components/registration.html indicates that
    // PascalCase names, not camelCase, are valid identifiers - but camelCase
    // was used since the noram was introduced in v2.) 'key_casing' context
    // parameter can override this.
    if (!isset($context['key_casing']) || $context['key_casing'] !== 'ignore') {
      $result = $this->convertKeysToCamelCase($result);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization(mixed $data, ?string $format = NULL, array $context = []): bool {
    return $data instanceof CustomElement;
  }

  /**
   * Normalize custom element in explicit format with separated props and slots.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   * @param array $context
   *   Normalization context.
   *
   * @return array
   *   Normalized custom element with explicit props/slots structure.
   */
  protected function normalizeInExplicitFormat(CustomElement $element, BubbleableMetadata $cache_metadata, array $context) {
    $result = ['element' => $element->getPrefixedTag()];

    // Collect cache metadata.
    $cache_metadata->addCacheableDependency($element);

    // Normalize attributes into props object.
    $props = $this->normalizeAttributes($element->getAttributes(), $cache_metadata);

    // Normalize slots into slots object.
    $slots = $this->normalizeSlots($element, $cache_metadata, $context);

    // Special handling for "renderless-container" elements.
    if ($element->getTag() == 'renderless-container') {
      // Merge all slots into a single array.
      return call_user_func_array('array_merge', array_values($slots));
    }

    // Add props and slots to result only if they're not empty.
    if (!empty($props)) {
      $result['props'] = $props;
    }
    if (!empty($slots)) {
      $result['slots'] = $slots;
    }

    return $result;
  }

  /**
   * Normalize custom element, respecting the configured format.
   *
   * This method routes to the appropriate format-specific normalization method
   * based on the context. For backward compatibility, defaults to legacy format
   * when called directly without context.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   * @param array $context
   *   Normalization context.
   *
   * @return array
   *   Normalized custom element.
   */
  protected function normalizeCustomElement(CustomElement $element, BubbleableMetadata $cache_metadata, array $context = []) {
    // Determine explicit flag from context or config.
    if (isset($context['explicit'])) {
      $explicit = $context['explicit'];
    }
    elseif (isset($context['json_format'])) {
      // BC: Support legacy json_format context parameter.
      $explicit = ($context['json_format'] === 'explicit');
    }
    else {
      // Get from config setting.
      $config = $this->configFactory->get('custom_elements.settings');
      $json_format = $config->get('json_format') ?? 'explicit';
      $explicit = ($json_format === 'explicit');
      // Add config as cacheable dependency so responses are invalidated when
      // settings change.
      $cache_metadata->addCacheableDependency($config);
    }

    // Store explicit flag in context for nested normalizations.
    $context['explicit'] = $explicit;

    // Route to format-specific method.
    if ($explicit) {
      return $this->normalizeInExplicitFormat($element, $cache_metadata, $context);
    }
    else {
      return $this->normalizeInImplicitFormat($element, $cache_metadata, $context);
    }
  }

  /**
   * Normalize custom element in implicit format.
   *
   * In implicit format, attributes and slots are mixed at the root level.
   * This is the backwards-compatible format.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The custom element.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   * @param array $context
   *   Normalization context.
   *
   * @return array
   *   Normalized custom element in implicit format.
   */
  protected function normalizeInImplicitFormat(CustomElement $element, BubbleableMetadata $cache_metadata, array $context = []) {
    $result = ['element' => $element->getPrefixedTag()];
    $result = array_merge($result, $this->normalizeAttributes($element->getAttributes(), $cache_metadata));

    // Collect cache metadata. Since the cache metadata object is passed down
    // to slots, custom elements of slots will add their metadata as well.
    $cache_metadata->addCacheableDependency($element);

    $normalized_slots = $this->normalizeSlots($element, $cache_metadata, $context);

    // Special handling for "renderless-container" elements. Only slots are
    // output, the element itself is not rendered.
    if ($element->getTag() == 'renderless-container') {
      // Merge all slots into a single array.
      return call_user_func_array('array_merge', array_values($normalized_slots));
    }
    else {
      $result = array_merge($result, $normalized_slots);
    }

    // Special handling for "div" and "span" elements in JSON output.
    // Those elements are used to wrap slot-content and should not be output as
    // separate elements in the JSON structure.
    // @todo Consider using renderless-container instead of div/span in
    // in slot-helper-methods of CustomElement class.
    if ($result['element'] == 'div' || $result['element'] == 'span') {
      unset($result['element']);
    }
    return $result;
  }

  /**
   * Normalize custom element attributes.
   *
   * @param array $attributes
   *   The attributes.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   *
   * @return array
   *   Normalized custom element attributes.
   */
  protected function normalizeAttributes(array $attributes, BubbleableMetadata $cache_metadata) {
    $result = [];
    foreach ($attributes as $key => $value) {
      if ($key == 'slot') {
        continue;
      }
      $result_key = strpos($key, ':') === 0 ? substr($key, 1) : $key;
      $result[$result_key] = $value;
    }
    return $result;
  }

  /**
   * Normalize slots.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The element for which to normalize slots.
   * @param \Drupal\Core\Render\BubbleableMetadata $cache_metadata
   *   The cache metadata.
   * @param array $context
   *   Normalization context.
   *
   * @return array
   *   Normalized slots.
   */
  protected function normalizeSlots(CustomElement $element, BubbleableMetadata $cache_metadata, array $context = []) {
    $data = [];

    // Determine if using explicit format.
    $explicit = $context['explicit'] ?? TRUE;

    foreach ($element->getSortedSlotsByName() as $slot_key => $slot_entries) {
      $slot_data = [];
      foreach ($slot_entries as $index => $slot) {
        $slot_key = $slot['key'];

        // Handle slots set via nested custom element and markup.
        if (!empty($slot['content']) && $slot['content'] instanceof CustomElement) {
          // Recursively normalize nested elements - normalizeCustomElement()
          // will respect the format from context.
          $slot_data[$index] = $this->normalizeCustomElement($slot['content'], $cache_metadata, $context);
          // Remove possible doubled slot attributes.
          unset($slot_data[$index]['slot']);
        }
        elseif ($slot['content'] instanceof MarkupInterface) {
          $slot_data[$index] = (string) $slot['content'];
        }
      }

      if ($element->hasSlotNormalizationStyle($slot_key, CustomElement::NORMALIZE_AS_SINGLE_VALUE)) {
        $slot_data = reset($slot_data);
      }

      // In explicit format, skip slots with empty div/span elements.
      if ($explicit && is_array($slot_data) && isset($slot_data['element']) &&
        empty($slot_data['props']) && empty($slot_data['slots']) &&
        ($slot_data['element'] === 'div' || $slot_data['element'] === 'span')) {

        continue;
      }

      // In explicit format, keep 'default' as 'default'.
      // In implicit format, rename 'default' to 'content' for BC.
      $data_key = (!$explicit && $slot_key == 'default') ? 'content' : $slot_key;
      $data[$data_key] = $slot_data;
    }
    return $data;
  }

  /**
   * Converts keys to camel case.
   *
   * @param array $array
   *   Array of keys to convert.
   *
   * @return array
   *   Converted keys.
   */
  protected function convertKeysToCamelCase(array $array) {
    $keys = array_map(function ($key) use (&$array) {
      if (is_array($array[$key])) {
        $array[$key] = $this->convertKeysToCamelCase($array[$key]);
      }
      return preg_replace_callback('/[_-]([a-z])/', function ($matches) {
        return strtoupper($matches[1]);
      }, $key);
    }, array_keys($array));

    return array_combine($keys, $array);
  }

  /**
   * {@inheritDoc}
   */
  public function getSupportedTypes(?string $format): array {
    return [
      CustomElement::class => TRUE,
    ];
  }

}

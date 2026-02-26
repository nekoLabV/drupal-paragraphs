<?php

namespace Drupal\custom_elements;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\DeprecationHelper;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;

/**
 * Custom element data model.
 */
class CustomElement implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * Whether Drupal's "field-" prefixes should be removed.
   *
   * This is a global settings and can be set early in the bootstrap, e.g. from
   * settings.php.
   *
   * @var bool
   */
  public static $removeFieldPrefix = TRUE;

  /**
   * List of no-end tags.
   *
   * @var array
   */
  protected static $noEndTags = [
    'area',
    'base',
    'br',
    'col',
    'embed',
    'hr',
    'img',
    'input',
    'link',
    'meta',
    'param',
    'source',
    'track',
    'wbr',
  ];

  /**
   * Custom element tag prefix.
   *
   * Used for prefixing a bunch of custom elements the same way.
   *
   * @var string
   */
  protected $tagPrefix = '';

  /**
   * Custom element tag name.
   *
   * @var string
   */
  protected $tag = 'div';

  /**
   * List of other attributes.
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * Array of slots, represented as array of nested custom elements.
   *
   * @var array[][]
   *   Array of slots, keyed slot name and entry index. Each entry is an array
   *   with the following keys:
   *   - key: Slot name
   *   - weight: Slot weight
   *   - content: Slot markup (MarkupInterface) or element (CustomElement)
   *     object.
   */
  protected $slots = [];

  /**
   * Array of normalization styles.
   *
   * @var array[][]
   *   Array keyed by slot name and normalization style constant, with the value
   *   indicating whether it's enabled.
   *
   * @see self::NORMALIZE_AS_SINGLE_VALUE
   */
  protected $slotNormalizationStyles = [];

  /**
   * Normalize assuming the slot is single-valued.
   */
  const NORMALIZE_AS_SINGLE_VALUE = 'single';

  /**
   * Creates a new custom element.
   *
   * @param string $tag
   *   The element tag name.
   *
   * @return static
   *   The created custom element.
   */
  public static function create($tag = 'div') {
    // @phpstan-ignore-next-line
    $element = new static();
    $element->setTag($tag);
    return $element;
  }

  /**
   * Creates a custom element from the given render array.
   *
   * If no custom-element is pre-existing in the render array, the render
   * array will be rendered and wrapped in a <drupal-markup> element.
   *
   * @param array $render
   *   The render array.
   *
   * @return \Drupal\custom_elements\CustomElement|mixed
   *   Returns the custom element data model.
   */
  public static function createFromRenderArray(array $render) {
    if (isset($render['#custom_element'])) {
      $element = $render['#custom_element'];
      if (isset($render['#cache'])) {
        $element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($render));
      }
    }
    // If no custom-element is pre-existing, wrap the result in a drupal-markup
    // tag.
    else {
      $element = static::create('drupal-markup');
      $element->setSlotFromRenderArray('default', $render);
    }
    return $element;
  }

  /**
   * Sets the slots of the element.
   *
   * @param array[] $slots
   *   The slots as returned by getSlots().
   */
  public function setSlots(array $slots) {
    $this->slots = $slots;
  }

  /**
   * Gets the element slots, keyed by slot name and entry index.
   *
   * @return array[][]
   *   Array of slots, keyed slot name and entry index. Each entry is an array
   *   with the following keys:
   *   - key: Slot name
   *   - weight: Slot weight
   *   - content: Slot markup (MarkupInterface) or element (CustomElement)
   *     object.
   */
  public function getSlots() {
    return $this->slots;
  }

  /**
   * Gets a sorted and flattened list of slots.
   *
   * @return \Drupal\custom_elements\CustomElement|\Drupal\Core\Render\Markup[]
   *   A numerically indexed and sorted array of slot arrays as defined
   *   by ::getSlot().
   */
  public function getSortedSlots() {
    $slots = $this->getSortedSlotsByName();
    // Flatten the array.
    $slot_entries = [];
    foreach ($slots as $entries) {
      foreach ($entries as $slot_entry) {
        $slot_entries[] = $slot_entry;
      }
    }
    return $slot_entries;
  }

  /**
   * Gets a sorted list of slots, keyed by slot name.
   *
   * @return array[]
   *   A sorted array of slot entries as defined by ::getSlot(), keyed by slot
   *   name.
   */
  public function getSortedSlotsByName() {
    $slots = $this->getSlots();
    foreach ($slots as &$entries) {
      $i = 0;
      $count = count($entries);
      foreach ($entries as &$entry) {
        $entry['weight'] = floor($entry['weight'] * 1000) + $i / $count;
        $i++;
      }
      usort($entries, 'Drupal\Component\Utility\SortArray::sortByWeightElement');
    }
    return $slots;
  }

  /**
   * Gets a slot entry from the given slot key and index.
   *
   * NOTE: This method only gets a single entry for the given slot key, not the
   * complete slot! Use ::getSortedSlotsByName() to get the complete slot.
   *
   * @param string $key
   *   Name of the slot to get.
   * @param int $index
   *   (optional) The index of the slot entry to retrieve. Defaults to 0.
   *
   * @return array|null
   *   A single slot entry array with the following keys, or NULL if not found:
   *   - key: Slot name
   *   - weight: Slot weight
   *   - content: Slot markup (MarkupInterface) or element (CustomElement)
   *     object.
   *
   * @see static::getSortedSlotsByName())
   */
  public function getSlot($key, $index = 0) {
    $key = $this->fixSlotKey($key);
    return $this->slots[$key][$index] ?? NULL;
  }

  /**
   * Sets a slot from a render array.
   *
   * The render-array is rendered into markup and the slot is set with this
   * markup. Falling-back to adding render arrays should be avoided as much as
   * possible, instead content should be rendered into nested CustomElement
   * objects.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param array $build
   *   The render array.
   * @param string $tag
   *   (optional) The element tag to use for the slot.
   * @param array $attributes
   *   (optional) Attributes to add to the slot tag.
   * @param int|null $index
   *   (optional) The index of the slot entry to set. By default, if no value is
   *   given it's assumed that the slot is single-valued and the index 0 gets
   *   set.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   */
  public function setSlotFromRenderArray($key, array $build, string $tag = 'div', array $attributes = [], ?int $index = NULL, int $weight = 0) {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    // @phpstan-ignore-next-line
    $renderer = \Drupal::service('renderer');
    $markup = DeprecationHelper::backwardsCompatibleCall(
      currentVersion: \Drupal::VERSION,
      deprecatedVersion: '10.3',
      currentCallable: fn() => $renderer->renderInIsolation($build),
      deprecatedCallable: fn() => $renderer->renderPlain($build),
    );
    // Add cache metadata as needed from the cache metadata attached to the
    // render array.
    $this->addCacheableDependency(BubbleableMetadata::createFromRenderArray($build));
    return $this->setSlot($key, $markup, $tag, $attributes, $index, $weight);
  }

  /**
   * Sets a value for the given slot.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\Component\Render\MarkupInterface|string $value
   *   Slot value or markup.
   * @param string $tag
   *   (optional) The element tag to use for the slot.
   * @param array $attributes
   *   (optional) Attributes to add to the slot tag.
   * @param int|null $index
   *   (optional) The index of the slot entry to set. By default, if no value is
   *   given it's assumed that the slot is single-valued and the index 0 gets
   *   set. For adding a slot, use ::addSlot().
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::addSlot()
   */
  public function setSlot(string $key, $value, string $tag = 'div', array $attributes = [], ?int $index = NULL, int $weight = 0) {
    if (in_array($tag, static::$noEndTags) && !empty($value)) {
      throw new \LogicException(sprintf('Tag %s is no-end tag and should not have a content.', $tag));
    }

    // We do not support passing render arrays directly anymore.
    if (is_array($value)) {
      @trigger_error('Setting slot value to an array is deprecated in custom_elements:8.x-2.0 (alpha13) and is removed from custom_elements:8.x-3.0. Provide a CustomElement, MarkupInterface or a markup string. Treating content as a render array. See https://www.drupal.org/project/custom_elements/issues/3144191', E_USER_DEPRECATED);
      return $this->setSlotFromRenderArray($key, $value, $tag, $attributes, $index, $weight);
    }

    if (!isset($index)) {
      $index = 0;
      $this->setSlotNormalizationStyle($key, self::NORMALIZE_AS_SINGLE_VALUE);
    }

    $key = $this->fixSlotKey($key);

    if ($value instanceof CustomElement) {
      $this->setSlotFromCustomElement($key, $value, $index, $weight);
      return $this;
    }

    if ($value && !$value instanceof MarkupInterface) {
      $value = Markup::create((string) $value);
    }

    // If markup and attributes are given, we need to wrap the content in
    // another custom element.
    if ($value && ($attributes || ($tag != 'div' && $tag != 'span'))) {
      $slot = CustomElement::create($tag)
        ->setAttributes($attributes)
        ->setSlot('default', $value);
    }
    // If just markup is given, we can use it directly as slot.
    elseif ($value) {
      $slot = $value;
    }
    // If no value is given, simply create a nested custom element.
    else {
      $slot = CustomElement::create($tag)
        ->setAttributes($attributes);
    }

    $this->slots[$key][$index] = [
      'weight' => $weight,
      'key' => $key,
      'content' => $slot,
    ];

    return $this;
  }

  /**
   * Removes all entries for the given slot.
   *
   * @param string $key
   *   Name of the slot to remove.
   *
   * @return $this
   */
  public function removeSlot($key) {
    $key = $this->fixSlotKey($key);
    unset($this->slots[$key]);
    return $this;
  }

  /**
   * Adds a value for the given slot by appending to any pre-existing ones.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\Core\Render\Markup|string $value
   *   Slot markup.
   * @param string $tag
   *   (optional) The tag to use for the slot.
   * @param array $attributes
   *   (optional) Attributes to add to the slot tag.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::setSlot()
   */
  public function addSlot($key, $value, $tag = 'div', array $attributes = [], $weight = 0) {
    return $this->setSlot($key, $value, $tag, $attributes, $this->getIndexForNewSlotEntry($key), $weight);
  }

  /**
   * Gets the index to use for a new slot entry.
   *
   * @param string $key
   *   The slot key.
   *
   * @return int
   *   Return index for new slot entry.
   */
  private function getIndexForNewSlotEntry($key) {
    // Fix the slot key before using it for indexing.
    $key = $this->fixSlotKey($key);
    // Determine last index.
    if (isset($this->slots[$key])) {
      // Determine array index by appending a new one. ::setSlot() will
      // overwrite the array with the right slot array afterwards.
      $this->slots[$key][] = [];
      end($this->slots[$key]);
      return key($this->slots[$key]);
    }
    else {
      return 0;
    }
  }

  /**
   * Sets the slot with a single custom element on a certain index.
   *
   * Note: This is overwriting possibly already existing slots.
   *
   * This method avoids a wrapper div as necessary by the helper for multiple
   * elements.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement $nestedElement
   *   The nested custom element.
   * @param int|null $index
   *   (optional) The index of the slot entry to set. By default, if no value is
   *   given it's assumed that the slot is single-valued and the index 0 gets
   *   set.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::addSlotFromCustomElement()
   */
  public function setSlotFromCustomElement(string $key, CustomElement $nestedElement, $index = NULL, $weight = 0) {
    if (!isset($index)) {
      $index = 0;
      $this->setSlotNormalizationStyle($key, self::NORMALIZE_AS_SINGLE_VALUE);
    }

    $key = $this->fixSlotKey($key);
    $this->slots[$key][$index] = [
      'weight' => $weight,
      'key' => $key,
      'content' => $nestedElement,
    ];
    return $this;
  }

  /**
   * Sets a slot normalization style.
   *
   * @param string $key
   *   The slot name.
   * @param string $style
   *   Normalization style. A single value is currently supported:
   *   Drupal\custom_elements\CustomElement::NORMALIZE_AS_SIMPLE_VALUE.
   * @param bool $enable
   *   Whether to enable the style (default) or disable it.
   *
   * @return $this
   */
  public function setSlotNormalizationStyle($key, $style, $enable = TRUE) {
    $key = $this->fixSlotKey($key);
    $this->slotNormalizationStyles[$key][$style] = $enable;
    return $this;
  }

  /**
   * Returns whether a slot normalization style is set.
   *
   * @param string $key
   *   The slot name.
   * @param string $style
   *   Normalization style. A single value is currently supported:
   *   Drupal\custom_elements\CustomElement::NORMALIZE_AS_SIMPLE_VALUE.
   *
   * @return bool
   *   Whether the style is enabled.
   */
  public function hasSlotNormalizationStyle($key, $style) {
    return !empty($this->slotNormalizationStyles[$key][$style]);
  }

  /**
   * Sets the slot with a single custom element.
   *
   * This method avoids a wrapper div as necessary by the helper for multiple
   * elements.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement $nestedElement
   *   The nested custom element.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   */
  public function addSlotFromCustomElement($key, CustomElement $nestedElement, $weight = 0) {
    return $this->setSlotFromCustomElement($key, $nestedElement, $this->getIndexForNewSlotEntry($key), $weight);
  }

  /**
   * Sets the slot content from multiple nested custom elements.
   *
   * Note: This is overwriting possibly already existing slots.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement[] $nestedElements
   *   The (ordered) array of nested custom elements.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::addSlotFromNestedElements()
   */
  public function setSlotFromNestedElements($key, array $nestedElements, $weight = 0) {
    $key = $this->fixSlotKey($key);
    $this->slots[$key] = [];
    if (empty($nestedElements)) {
      return $this;
    }
    $this->addSlotFromNestedElements($key, $nestedElements, $weight);
    return $this;
  }

  /**
   * Adds multiple nested custom elements to the given slot.
   *
   * @param string $key
   *   Name of the slot to set value for.
   * @param \Drupal\custom_elements\CustomElement[] $nestedElements
   *   The (ordered) array of nested custom elements to append.
   * @param int $weight
   *   (optional) A weight for ordering output slots. Defaults to 0.
   *
   * @return $this
   *
   * @see ::setSlotFromNestedElements()
   */
  public function addSlotFromNestedElements($key, array $nestedElements, $weight = 0) {
    $key = $this->fixSlotKey($key);
    $offset = $this->getIndexForNewSlotEntry($key);
    foreach (array_values($nestedElements) as $delta => $nestedElement) {
      $this->slots[$key][$offset + $delta] = [
        'weight' => $weight,
        'key' => $key,
        'content' => $nestedElement,
      ];
    }
    return $this;
  }

  /**
   * Fix slot key and remove prefix if set via static::$removeFieldPrefix.
   *
   * @param string $key
   *   The slot key.
   *
   * @return string
   *   The fixed slot key.
   */
  public static function fixSlotKey($key) {
    $key = str_replace('_', '-', $key);
    if (static::$removeFieldPrefix && strpos($key, 'field-') === 0) {
      $key = substr($key, strlen('field-'));
    }
    return $key;
  }

  /**
   * Gets html tags which don't have an end-tag.
   *
   * @return string[]
   *   List of no-end tags.
   */
  public static function getNoEndTags() {
    return static::$noEndTags;
  }

  /**
   * Sets the custom element from the given element.
   *
   * This overwrites the current custom element with content from the given
   * element.
   *
   * @param \Drupal\custom_elements\CustomElement $element
   *   The element to set values from.
   */
  public function setFromCustomElement(CustomElement $element) {
    $this->setTag($element->getTag());
    $this->setTagPrefix($element->getTagPrefix());
    $this->setAttributes($element->getAttributes());
    $this->setSlots($element->getSlots());
    $this->slotNormalizationStyles = $element->slotNormalizationStyles;
    $this->addCacheableDependency($element);
  }

  /**
   * Gets the custom element tag name.
   *
   * @return string
   *   Custom element tag.
   */
  public function getTag() {
    return $this->tag;
  }

  /**
   * Sets the custom element tag name.
   *
   * @param string $tag
   *   The element tag name.
   *
   * @return $this
   */
  public function setTag($tag) {
    $tag = str_replace('_', '-', $tag);
    $this->tag = $tag;
    return $this;
  }

  /**
   * Gets value for given attribute.
   *
   * @param string $key
   *   Name of the attribute to get value for.
   *
   * @return string|null
   *   Value for a given attribute or NULL if unset.
   */
  public function getAttribute($key) {
    $key = str_replace('_', '-', $key);
    if (static::$removeFieldPrefix && strpos($key, 'field-') === 0) {
      $key = substr($key, strlen('field-'));
    }
    return $this->attributes[$key] ?? NULL;
  }

  /**
   * Sets value for the given attribute.
   *
   * @param string $key
   *   Name of the attribute to set value for.
   * @param string|null $value
   *   Attribute value or NULL to unset the attribute.
   *
   * @return $this
   */
  public function setAttribute($key, $value = NULL) {
    $key = str_replace('_', '-', $key);
    if (static::$removeFieldPrefix && strpos($key, 'field-') === 0) {
      $key = substr($key, strlen('field-'));
    }
    if (isset($value)) {
      $this->attributes[$key] = $value;
    }
    else {
      unset($this->attributes[$key]);
    }
    return $this;
  }

  /**
   * Removes an attribute.
   *
   * @param string $key
   *   Attribute key.
   *
   * @return $this
   */
  public function removeAttribute($key) {
    $key = str_replace('_', '-', $key);
    if (static::$removeFieldPrefix && strpos($key, 'field-') === 0) {
      $key = substr($key, strlen('field-'));
    }
    unset($this->attributes[$key]);
    return $this;
  }

  /**
   * Gets all attributes.
   *
   * @return array
   *   All attributes.
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * Sets all attributes.
   *
   * @param array $attributes
   *   The attributes.
   *
   * @return $this
   */
  public function setAttributes(array $attributes) {
    $this->attributes = $attributes;
    return $this;
  }

  /**
   * Gets the tag prefix.
   *
   * @return string
   *   Tag prefix.
   */
  public function getTagPrefix() {
    return $this->tagPrefix;
  }

  /**
   * Sets the tag prefix.
   *
   * Note that the tag prefix is separated by a dash from the tag.
   *
   * @param string $tagPrefix
   *   The tag prefix.
   *
   * @return $this
   */
  public function setTagPrefix($tagPrefix) {
    $tagPrefix = str_replace('_', '-', $tagPrefix);
    $this->tagPrefix = $tagPrefix;
    return $this;
  }

  /**
   * Returns the tag including the prefix, separated by a dash.
   *
   * @return string
   *   The tag with prefix.
   */
  public function getPrefixedTag() {
    return $this->tagPrefix ? $this->tagPrefix . '-' . $this->tag : $this->tag;
  }

  /**
   * Gets a render array for rendering the custom element into markup.
   *
   * The returned render array always includes a '#custom_element' reference
   * to this CustomElement instance, enabling other code to detect and
   * intelligently handle the custom element. This applies to all render
   * variants.
   *
   * @param string|null $render_variant
   *   (optional) The rendering variant to use. One of:
   *   - NULL: Use configured default (respects default_render_variant setting)
   *   - 'markup': Custom Elements Markup rendering (traditional)
   *   - 'preview': Use preview provider (automatic selection)
   *   - 'preview:<provider_id>': Use specific preview provider
   *     (e.g., 'preview:markup', 'preview:nuxt')
   *
   * @return array
   *   Render array for the custom element. Always contains '#custom_element'
   *   property with reference to this CustomElement instance.
   */
  public function toRenderArray(?string $render_variant = NULL) {
    if ($uses_default = $render_variant === NULL) {
      // @phpstan-ignore-next-line
      $render_helper = \Drupal::service('custom_elements.render_helper');
      $render_variant = $render_helper->getDefaultRenderVariant();
    }

    if ($render_variant === 'markup') {
      // Traditional markup rendering via theme system.
      $build = [
        '#theme' => 'custom_element',
        '#custom_element' => $this,
      ];
    }
    elseif ($render_variant === 'preview') {
      // Auto-select preview provider.
      $build = $this->preview();
    }
    elseif (strpos($render_variant, 'preview:') === 0) {
      // Use specific preview provider.
      $provider_id = substr($render_variant, strlen('preview:'));
      $build = $this->preview($provider_id);
    }
    else {
      throw new \InvalidArgumentException(sprintf('Invalid render variant: %s', $render_variant));
    }

    // When variant is using the default, add cache metadata.
    // Note on caching: Cache metadata is handled as follows:
    // - For API requests (when lupus_ce_renderer is installed): The
    //   lupus_ce_renderer_content_format cache context is added at the
    //   response level, ensuring proper cache variation.
    // - For non-API requests: The render variant is determined by config,
    //   so we add config cache tags here to ensure proper cache invalidation.
    if ($uses_default) {
      $build['#cache']['tags'][] = 'config:custom_elements.settings';
    }
    return $build;
  }

  /**
   * Generates a preview render array for the custom element.
   *
   * Previews are rendering the custom element in Drupal-UI context, e.g. in the
   * administrative interface. This could be a simple markup preview, or a full
   * client-side rendered preview using a JavaScript framework.
   *
   * @param string|null $provider_id
   *   (optional) The preview provider service ID. If not provided, the provider
   *   will be automatically selected based on the current request.
   *
   * @return array
   *   A render array for the preview. Always returns a render array since
   *   the markup provider is available as a fallback.
   *
   * @throws \InvalidArgumentException
   *   If the specified provider service does not exist.
   */
  public function preview(?string $provider_id = NULL): array {
    // @phpstan-ignore-next-line
    $resolver = \Drupal::service('custom_elements.preview_resolver');

    if ($provider_id !== NULL) {
      // Use specific preview provider service.
      $provider = $resolver->getProviderById($provider_id);

      if (!$provider) {
        throw new \InvalidArgumentException(sprintf('Preview provider "%s" does not exist.', $provider_id));
      }
    }
    else {
      // Auto-select provider based on request.
      // @phpstan-ignore-next-line
      $provider = $resolver->getProvider();
    }

    $preview = $provider->preview($this);
    // Attach the custom element to the preview render array to enable other
    // code like \Drupal\custom_elements\CustomElementsBlockRenderHelperTrait
    // to detect the custom element and intelligently handle it.
    $preview['#custom_element'] = $this;
    return $preview;
  }

  /**
   * Converts the custom element to an array representation.
   *
   * @param bool $preserve_keys
   *   (optional) Whether to preserve original key casing with underscores.
   *   Defaults to FALSE, which converts keys to camelCase for nicer JavaScript
   *   usage.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $cache_metadata
   *   (optional) Object to collect cache metadata from the element and its
   *   nested elements. If not provided, cache metadata will not be collected.
   * @param bool $explicit
   *   (optional) Whether to use explicit structure with separate 'props' and
   *   'slots' keys. Defaults to FALSE. When TRUE, attributes are in 'props'
   *   and slots in 'slots'. When FALSE (implicit format), attributes are
   *   mixed at the root level with slots.
   *
   * @return array
   *   Array representation of the custom element.
   *
   * @see \Drupal\custom_elements\CustomElementNormalizer
   */
  public function toArray(bool $preserve_keys = FALSE, ?BubbleableMetadata $cache_metadata = NULL, bool $explicit = FALSE): array {
    /** @var \Drupal\custom_elements\CustomElementNormalizer $normalizer */
    // @phpstan-ignore-next-line
    $normalizer = \Drupal::service('custom_elements.normalizer');
    // Build context for the normalizer.
    $context = $cache_metadata ? ['cache_metadata' => $cache_metadata] : [];
    $context['key_casing'] = $preserve_keys ? 'ignore' : NULL;
    $context['explicit'] = $explicit;
    return $normalizer->normalize($this, NULL, $context);
  }

  /**
   * Converts the custom element to a JSON-ready array.
   *
   * This method uses the normalizer's configured json_format setting
   * (explicit vs implicit/legacy) rather than forcing a specific format.
   *
   * @param bool $preserve_keys
   *   (optional) Whether to preserve original key casing with underscores.
   *   Defaults to FALSE, which converts keys to camelCase.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $cache_metadata
   *   (optional) Object to collect cache metadata.
   *
   * @return array
   *   Array representation suitable for JSON encoding.
   */
  public function toJson(bool $preserve_keys = FALSE, ?BubbleableMetadata $cache_metadata = NULL): array {
    /** @var \Drupal\custom_elements\CustomElementNormalizer $normalizer */
    // @phpstan-ignore-next-line
    $normalizer = \Drupal::service('custom_elements.normalizer');
    // Build context for the normalizer.
    $context = $cache_metadata ? ['cache_metadata' => $cache_metadata] : [];
    $context['key_casing'] = $preserve_keys ? 'ignore' : NULL;
    // Don't set explicit - let normalizer use its configured default.
    return $normalizer->normalize($this, NULL, $context);
  }

  /**
   * Converts the custom element to HTML markup.
   *
   * This is a shortcut for rendering the element's render array to markup-style
   * output of the custom element. The configured markup style is applied.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   HTML markup of the custom element as a MarkupInterface object.
   *
   * @see template_preprocess_custom_element()
   */
  public function toMarkup(): MarkupInterface {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    // @phpstan-ignore-next-line
    $renderer = \Drupal::service('renderer');
    // Always use 'markup' variant - this method's contract is to return markup.
    $render_array = $this->toRenderArray('markup');
    return $renderer->renderInIsolation($render_array);
  }

}

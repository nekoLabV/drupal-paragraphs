<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_elements\Traits;

use Drupal\custom_elements\CustomElement;

/**
 * Provides debug helper methods for test classes.
 */
trait TestDebugHelperTrait {

  /**
   * Output debug information to a file.
   *
   * @param string $filename
   *   The filename to write to (typically in /tmp/).
   * @param mixed $data
   *   The data to output.
   * @param string $test_name
   *   (optional) The test name to include in the output.
   *   If not provided, will try to determine from backtrace.
   */
  protected function debug(string $filename, $data, string $test_name = ''): void {
    // Check if DEBUG_ENABLED constant is defined in the using class.
    // Default to FALSE if not defined.
    $debug_enabled = defined('self::DEBUG_ENABLED') ? self::DEBUG_ENABLED : FALSE;
    if (!$debug_enabled) {
      return;
    }

    // Use custom elements normalizer directly to map it to a JSON structure.
    $normalizer = $this->container->get('custom_elements.normalizer');
    $serialize = function ($data) use ($normalizer) {
      if ($data instanceof CustomElement) {
        return $normalizer->normalize($data);
      }
      elseif (is_array($data) || is_object($data)) {
        // Recursively process arrays and objects.
        $result = [];
        foreach ((array) $data as $key => $value) {
          if ($value instanceof CustomElement) {
            $result[$key] = $normalizer->normalize($value);
          }
          else {
            $result[$key] = $value;
          }
        }
        return $result;
      }
      return $data;
    };

    $debug_data = [
      'test' => $test_name ?: debug_backtrace()[1]['function'] ?? 'unknown',
      'timestamp' => date('Y-m-d H:i:s'),
      'data' => $serialize($data),
    ];

    file_put_contents($filename, json_encode($debug_data, JSON_PRETTY_PRINT));
  }

}

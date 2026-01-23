<?php

namespace Drupal\base\Utils\Debug;

use Drupal\Component\Serialization\Json;
use Drupal\Core\File\FileSystemInterface;

/**
 * 通用调试工具类
 */
class DebugTool {
  
  /**
   * @var string 调试文件目录
   */
  protected static $debugDir = 'public://debug_logs';
  
  /**
   * @var bool 是否启用调试
   */
  protected static $enabled = TRUE;
  
  /**
   * @var array 调试配置
   */
  protected static $config = [
    'method' => 'file', // file, log, console, devel
    'max_depth' => 5,
    'max_length' => 1000,
    'truncate' => TRUE,
    'include_backtrace' => FALSE,
    'timestamp_format' => 'Y-m-d H:i:s.u',
  ];
  
  /**
   * 初始化配置
   */
  public static function init(array $config = []): void {
    static::$config = array_merge(static::$config, $config);
    
    // 确保目录存在
    if (static::$config['method'] === 'file') {
      $file_system = \Drupal::service('file_system');
      $file_system->prepareDirectory(static::$debugDir, FileSystemInterface::CREATE_DIRECTORY);
    }
  }
  
  /**
   * 启用/禁用调试
   */
  public static function setEnabled(bool $enabled): void {
    static::$enabled = $enabled;
  }
  
  /**
   * 设置调试目录
   */
  public static function setDebugDir(string $dir): void {
    static::$debugDir = $dir;
  }
  
  /**
   * 通用调试方法
   *
   * @param mixed $data 要调试的数据
   * @param string $label 调试标签
   * @param array $options 调试选项
   */
  public static function dump($data, string $label = '', array $options = []): void {
    if (!static::$enabled) {
      return;
    }
    
    $options = array_merge(static::$config, $options);
    
    // 准备调试信息
    $debug_info = static::prepareDebugInfo($data, $label, $options);
    
    // 根据方法输出
    switch ($options['method']) {
      case 'file':
        static::writeToFile($debug_info, $options);
        break;
        
      case 'log':
        static::writeToLog($debug_info, $options);
        break;
        
      case 'console':
        static::writeToConsole($debug_info, $options);
        break;
        
      case 'devel':
        static::writeWithDevel($debug_info, $options);
        break;
        
      case 'browser':
        static::writeToBrowser($debug_info, $options);
        break;
        
      default:
        static::writeToFile($debug_info, $options);
    }
  }
  
  /**
   * 准备调试信息
   */
  private static function prepareDebugInfo($data, string $label, array $options): array {
    $timestamp = microtime(true);
    $debug_id = uniqid('debug_', true);
    
    // 获取调用位置
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    $caller = $backtrace[1] ?? [];
    $caller_info = sprintf(
      '%s:%d',
      basename($caller['file'] ?? 'unknown'),
      $caller['line'] ?? 0
    );
    
    // 格式化数据
    $formatted_data = static::formatData($data, $options);
    
    return [
      'id' => $debug_id,
      'timestamp' => $timestamp,
      'datetime' => date($options['timestamp_format'], $timestamp),
      'label' => $label,
      'caller' => $caller_info,
      'full_backtrace' => $options['include_backtrace'] ? $backtrace : null,
      'data_type' => gettype($data),
      'data' => $formatted_data,
      'raw_data' => $data,
      'memory_usage' => memory_get_usage(true),
      'peak_memory' => memory_get_peak_usage(true),
      'request_uri' => \Drupal::request()->getRequestUri(),
      'user' => \Drupal::currentUser()->getAccountName(),
    ];
  }
  
  /**
   * 格式化数据
   */
  private static function formatData($data, array $options, int $depth = 0) {
    if ($depth > $options['max_depth']) {
      return '[Max Depth Reached]';
    }
    
    $type = gettype($data);
    
    switch ($type) {
      case 'array':
        $result = [];
        $count = 0;
        foreach ($data as $key => $value) {
          if ($count++ > 20) { // 限制数组项数量
            $result['...'] = '[' . (count($data) - 20) . ' more items]';
            break;
          }
          $result[$key] = static::formatData($value, $options, $depth + 1);
        }
        return $result;
        
      case 'object':
        if ($data instanceof \Drupal\Core\Entity\EntityInterface) {
          return [
            '__type__' => 'Entity',
            'class' => get_class($data),
            'id' => method_exists($data, 'id') ? $data->id() : null,
            'bundle' => method_exists($data, 'bundle') ? $data->bundle() : null,
            'label' => method_exists($data, 'label') ? $data->label() : null,
            'fields' => static::formatEntityFields($data, $options),
          ];
        }
        
        if ($data instanceof \stdClass) {
          return (array) $data;
        }
        
        return [
          '__type__' => 'Object',
          'class' => get_class($data),
          'properties' => get_object_vars($data),
          'methods' => get_class_methods($data),
        ];
        
      case 'string':
        if ($options['truncate'] && strlen($data) > $options['max_length']) {
          return substr($data, 0, $options['max_length']) . '... [truncated]';
        }
        return $data;
        
      case 'boolean':
        return $data ? 'TRUE' : 'FALSE';
        
      case 'NULL':
        return 'NULL';
        
      case 'resource':
        return '[Resource: ' . get_resource_type($data) . ']';
        
      default:
        return $data;
    }
  }
  
  /**
   * 格式化实体字段
   */
  private static function formatEntityFields($entity, array $options): array {
    if (!method_exists($entity, 'getFields')) {
      return [];
    }
    
    $fields = [];
    foreach ($entity->getFields() as $field_name => $field) {
      $fields[$field_name] = [
        'type' => $field->getFieldDefinition()->getType(),
        'label' => $field->getFieldDefinition()->getLabel(),
        'is_empty' => $field->isEmpty(),
        'value' => $field->isEmpty() ? null : static::formatData($field->getValue(), $options, 1),
      ];
    }
    return $fields;
  }
  
  /**
   * 写入文件
   */
  private static function writeToFile(array $debug_info, array $options): void {
    try {
      $file_system = \Drupal::service('file_system');
      $real_path = $file_system->realpath(static::$debugDir);
      
      if (!$real_path) {
        return;
      }
      
      // 生成文件名
      $filename = sprintf(
        'debug_%s_%s.json',
        date('Ymd_His'),
        $debug_info['id']
      );
      
      $filepath = $real_path . '/' . $filename;
      
      // 写入 JSON 文件
      $json_data = Json::encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      file_put_contents($filepath, $json_data);
      
      // 同时写入日志文件（文本格式）
      $text_filepath = str_replace('.json', '.txt', $filepath);
      $text_content = static::formatAsText($debug_info);
      file_put_contents($text_filepath, $text_content);
      
    } catch (\Exception $e) {
      \Drupal::logger('debug_tool')->error('Error writing debug file: @error', [
        '@error' => $e->getMessage()
      ]);
    }
  }
  
  /**
   * 写入日志
   */
  private static function writeToLog(array $debug_info, array $options): void {
    $message = sprintf(
      "[DEBUG] %s - %s at %s",
      $debug_info['label'] ?: 'No label',
      $debug_info['caller'],
      $debug_info['datetime']
    );
    
    \Drupal::logger('debug_tool')->debug($message, [
      'data' => $debug_info['data'],
    ]);
  }
  
  /**
   * 写入控制台（Drush）
   */
  private static function writeToConsole(array $debug_info, array $options): void {
    if (PHP_SAPI !== 'cli') {
      return;
    }
    
    $output = static::formatAsText($debug_info);
    echo "\n" . str_repeat('=', 80) . "\n";
    echo $output;
    echo "\n" . str_repeat('=', 80) . "\n";
  }
  
  /**
   * 使用 Devel 输出
   */
  private static function writeWithDevel(array $debug_info, array $options): void {
    if (!\Drupal::moduleHandler()->moduleExists('devel')) {
      static::writeToFile($debug_info, $options);
      return;
    }
    
    try {
      $devel_dumper = \Drupal::service('devel.dumper');
      
      ob_start();
      $devel_dumper->export($debug_info['raw_data'], $debug_info['label']);
      $output = ob_get_clean();
      
      // 写入 HTML 文件
      $html_file = static::$debugDir . '/devel_debug_' . $debug_info['id'] . '.html';
      $real_path = \Drupal::service('file_system')->realpath($html_file);
      
      if ($real_path) {
        $html_content = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Debug Output</title>';
        $html_content .= '<style>body { font-family: monospace; margin: 20px; background: #f5f5f5; }</style></head><body>';
        $html_content .= '<h2>Debug: ' . htmlspecialchars($debug_info['label']) . '</h2>';
        $html_content .= '<p><strong>Time:</strong> ' . $debug_info['datetime'] . '</p>';
        $html_content .= '<p><strong>Location:</strong> ' . $debug_info['caller'] . '</p>';
        $html_content .= '<hr>';
        $html_content .= $output;
        $html_content .= '</body></html>';
        
        file_put_contents($real_path, $html_content);
      }
      
    } catch (\Exception $e) {
      static::writeToFile($debug_info, $options);
    }
  }
  
  /**
   * 写入浏览器（仅开发环境）
   */
  private static function writeToBrowser(array $debug_info, array $options): void {
    if (PHP_SAPI === 'cli') {
      return;
    }
    
    $output = static::formatAsText($debug_info);
    
    echo '<div style="background: #f0f0f0; border: 1px solid #ccc; margin: 10px; padding: 10px; font-family: monospace; font-size: 12px;">';
    echo '<strong>DEBUG: ' . htmlspecialchars($debug_info['label']) . '</strong><br>';
    echo '<pre style="background: white; padding: 10px; overflow: auto;">';
    echo htmlspecialchars($output);
    echo '</pre>';
    echo '</div>';
  }
  
  /**
   * 格式化为文本
   */
  private static function formatAsText(array $debug_info): string {
    $output = [];
    
    $output[] = str_repeat('=', 80);
    $output[] = "DEBUG INFORMATION";
    $output[] = str_repeat('=', 80);
    $output[] = sprintf("ID:          %s", $debug_info['id']);
    $output[] = sprintf("Time:        %s", $debug_info['datetime']);
    $output[] = sprintf("Label:       %s", $debug_info['label'] ?: '(no label)');
    $output[] = sprintf("Location:    %s", $debug_info['caller']);
    $output[] = sprintf("Data Type:   %s", $debug_info['data_type']);
    $output[] = sprintf("Memory:      %s / %s", 
      static::formatBytes($debug_info['memory_usage']),
      static::formatBytes($debug_info['peak_memory'])
    );
    $output[] = sprintf("URI:         %s", $debug_info['request_uri']);
    $output[] = sprintf("User:        %s", $debug_info['user']);
    $output[] = str_repeat('-', 80);
    $output[] = "DATA:";
    $output[] = str_repeat('-', 80);
    
    $data_output = print_r($debug_info['data'], true);
    $output[] = $data_output;
    
    if ($debug_info['full_backtrace']) {
      $output[] = str_repeat('-', 80);
      $output[] = "BACKTRACE:";
      $output[] = str_repeat('-', 80);
      $output[] = print_r($debug_info['full_backtrace'], true);
    }
    
    $output[] = str_repeat('=', 80);
    
    return implode("\n", $output);
  }
  
  /**
   * 格式化字节大小
   */
  private static function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
  }
  
  /**
   * 清空调试文件
   */
  public static function clearDebugFiles(int $max_age_hours = 24): void {
    try {
      $file_system = \Drupal::service('file_system');
      $real_path = $file_system->realpath(static::$debugDir);
      
      if (!$real_path || !is_dir($real_path)) {
        return;
      }
      
      $files = scandir($real_path);
      $cutoff_time = time() - ($max_age_hours * 3600);
      $deleted_count = 0;
      
      foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
          continue;
        }
        
        $filepath = $real_path . '/' . $file;
        $filetime = filemtime($filepath);
        
        if ($filetime && $filetime < $cutoff_time) {
          unlink($filepath);
          $deleted_count++;
        }
      }
      
      \Drupal::logger('debug_tool')->info('Cleared @count debug files older than @hours hours.', [
        '@count' => $deleted_count,
        '@hours' => $max_age_hours,
      ]);
      
    } catch (\Exception $e) {
      \Drupal::logger('debug_tool')->error('Error clearing debug files: @error', [
        '@error' => $e->getMessage()
      ]);
    }
  }
  
  /**
   * 获取最近的调试文件
   */
  public static function getRecentDebugFiles(int $limit = 10): array {
    $files = [];
    
    try {
      $file_system = \Drupal::service('file_system');
      $real_path = $file_system->realpath(static::$debugDir);
      
      if (!$real_path || !is_dir($real_path)) {
        return $files;
      }
      
      $all_files = scandir($real_path);
      
      foreach ($all_files as $file) {
        if ($file === '.' || $file === '..' || !str_ends_with($file, '.txt')) {
          continue;
        }
        
        $filepath = $real_path . '/' . $file;
        $files[$file] = [
          'path' => $filepath,
          'size' => filesize($filepath),
          'modified' => filemtime($filepath),
        ];
      }
      
      // 按修改时间排序
      uasort($files, function($a, $b) {
        return $b['modified'] <=> $a['modified'];
      });
      
      // 限制数量
      $files = array_slice($files, 0, $limit, true);
      
    } catch (\Exception $e) {
      // 静默失败
    }
    
    return $files;
  }
}

<?php

  class stats {

    private static $_data;
    private static $_capture_parse_start;

    public static function init() {

      self::$_data = [
        'page_parse_time' => 0, // s
        'page_capture_time' => 0, // s
        'memory_peak_usage' => 0, // percent
        'database_queries' => 0, // qty
        'database_execution_time' => 0, // s
        'http_requests' => 0, // qty
        'http_duration' => 0, // s
        'output_optimization' => 0, // s
      ];

      event::register('before_capture', [__CLASS__, 'before_capture']);
      event::register('after_capture', [__CLASS__, 'after_capture']);
      event::register('before_output', [__CLASS__, 'before_output']);
    }

    ######################################################################

    public static function before_capture() {
      self::$_capture_parse_start = microtime(true);
    }

    public static function after_capture() {

      self::set('page_capture_time', microtime(true) - self::$_capture_parse_start);

      $page_parse_time = microtime(true) - SCRIPT_TIMESTAMP_START;

      if ($page_parse_time > 5) {

        $log_message = '['. date('d-M-Y H:i:s e').'] Long page execution time '. number_format($page_parse_time, 3, ',', ' ') .' s requesting '. document::link() . PHP_EOL . PHP_EOL;
        file_put_contents(FS_DIR_STORAGE . 'logs/performance.log', $log_message, FILE_APPEND);

        if ($page_parse_time > 10) {
          notices::add('warnings', sprintf(language::translate('text_long_execution_time', 'We apologize for the inconvenience that the server seems temporary overloaded right now.'), number_format($page_parse_time, 1, ',', ' ')));
        }
      }
    }

    public static function before_output() {

    // Memory peak usage
      self::set('memory_peak_usage', memory_get_peak_usage(true) / 1e6);

    // Page parse time
      $page_parse_time = microtime(true) - SCRIPT_TIMESTAMP_START;
      self::set('page_parse_time', $page_parse_time);

      if (empty(cache::$enabled)) {
        $cache = false;
      } else if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
        $cache = preg_match('#no-cache|max-age=0#i', $_SERVER['HTTP_CACHE_CONTROL']) ? false : true;
      } else {
        $cache = true;
      }

    // Output stats
      $stats = '<!--' . PHP_EOL
             . '  Application Statistics:' . PHP_EOL
             . '  - Using Cache: ' . ($cache ? 'Yes' : 'No') . PHP_EOL
             . '  - Page Parse Time: ' . number_format(self::get('page_parse_time')*1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Page Capture Time: ' . number_format(self::get('page_capture_time')*1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Included Files: ' . count(get_included_files()) . PHP_EOL
             . '  - Memory Peak: ' . number_format(self::get('memory_peak_usage'), 2, '.', ' ') . ' MB / '. ini_get('memory_limit') . PHP_EOL
             . '  - Database Queries: ' . number_format(self::get('database_queries'), 0, '.', ' ') . PHP_EOL
             . '  - Database Parse Time: ' . number_format(self::get('database_execution_time')*1000, 0, '.', ' ') . ' ms (' . number_format(self::get('database_execution_time')/self::get('page_parse_time')*100, 0, '.', ' ') . ' %)' . PHP_EOL
             . '  - Network Requests: ' . self::get('http_requests') . PHP_EOL
             . '  - Network Requests Duration: ' . number_format(self::get('http_duration')*1000, 0, '.', ' ') . ' ms (' . number_format(self::get('http_duration')/self::get('page_parse_time')*100, 0, '.', ' ') . ' %)' . PHP_EOL
             . '  - Output Optimization: ' . number_format(self::get('output_optimization')*1000, 0, '.', ' ') . ' ms (' . number_format(self::get('output_optimization')/self::get('page_parse_time')*100, 0, '.', ' ') . ' %)' . PHP_EOL
             . '  - vMod: ' . number_format(vmod::$time_elapsed*1000, 0, '.', ' ') . ' ms (' . number_format(vmod::$time_elapsed/self::get('page_parse_time')*100, 0, '.', ' ') . ' %)' . PHP_EOL
             . '-->';

      $GLOBALS['output'] = preg_replace('#</html>$#', '</html>' . PHP_EOL . $stats, $GLOBALS['output']);
    }

    public static function set($key, $value) {
      self::$_data[$key] = $value;
    }

    public static function get($key) {
      if (isset(self::$_data[$key])) return self::$_data[$key];
    }
  }

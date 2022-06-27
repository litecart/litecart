<?php

  class stats {

    private static $_data;
    private static $_counters;
    private static $_watches;
    private static $_elapsed;

    public static function init() {
      event::register('before_capture', [__CLASS__, 'before_capture']);
      event::register('after_capture', [__CLASS__, 'after_capture']);
      event::register('before_output', [__CLASS__, 'before_output']);
    }

    ######################################################################

    public static function before_capture() {
      self::start_watch('content_capture');
    }

    public static function after_capture() {

      self::stop_watch('content_capture');

      if (($page_parse_time = microtime(true) - SCRIPT_TIMESTAMP_START) > 5) {
        notices::add('warnings', sprintf(language::translate('text_long_execution_time', 'We apologize for the inconvenience that the server seems temporary overloaded right now.'), number_format($page_parse_time, 1, ',', ' ')));
        error_log('Warning: Long page execution time '. number_format($page_parse_time, 3, ',', ' ') .' s - '. $_SERVER['REQUEST_URI']);
      }
    }

    public static function before_output() {

    // Page parse time
      $page_parse_time = microtime(true) - SCRIPT_TIMESTAMP_START;

    // Output stats
      $stats = '<!--' . PHP_EOL
             . '  Timings:' . PHP_EOL
             . '  - Memory Peak: ' . number_format(memory_get_peak_usage(true) / 1e6, 2, '.', ' ') . ' MB / '. ini_get('memory_limit') . PHP_EOL
             . '  - Included Files: ' . count(get_included_files()) . PHP_EOL
             . '  - Page Parse Time: ' . number_format($page_parse_time * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Content Capture Time: ' . number_format(self::get_watch('content_capture') * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Database Queries: ' . number_format(self::get_counter('database_queries'), 0, '.', ' ') . PHP_EOL
             . '  - Database Duration: ' . number_format(self::get_watch('database_execution') * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Network Requests: ' . self::get_counter('http_requests') . PHP_EOL
             . '  - Network Duration: ' . number_format(self::get_watch('http_requests') * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Output Optimization: ' . number_format(self::get_watch('output_optimization') * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - vMod: ' . number_format(vmod::$time_elapsed * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '-->';

      $GLOBALS['output'] = preg_replace('#</html>#', '</html>' . PHP_EOL . $stats, $GLOBALS['output']);
    }

    public static function get($key) {
      if (isset(self::$_data[$key])) return self::$_data[$key];
    }

    public static function set($key, $value) {
      self::$_data[$key] = $value;
    }

    public static function start_watch($id) {
      self::$_watches[$id] = microtime(true);
    }

    public static function get_watch($id) {

      if (isset(self::$_watches[$id])) {
        return microtime(true) - self::$_watches[$id];
      } else if (isset(self::$_elapsed[$id])) {
        return self::$_elapsed[$id];
      }

      return 0;
    }

    public static function stop_watch($id) {

      $elapsed = microtime(true) - self::$_watches[$id];

      if (!empty(self::$_elapsed[$id])) {
        self::$_elapsed[$id] += $elapsed;
      } else {
        self::$_elapsed[$id] = $elapsed;
      }

      unset(self::$_watches[$id]);
    }

    public static function get_counter($id) {

      if (!empty(self::$_counters[$id])) {
        return self::$_counters[$id];
      }

      return 0;
    }

    public static function increase_count($id, $amount=1) {

      if (!empty(self::$_counters[$id])) {
        self::$_counters[$id] += $amount;
      } else {
        self::$_counters[$id] = $amount;
      }
    }
  }

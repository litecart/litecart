<?php

  class stats {

    private static $capture_timestamp;
    private static $capture_duration;

    public static function init() {
      event::register('before_capture', [__CLASS__, 'before_capture']);
      event::register('after_capture', [__CLASS__, 'after_capture']);
      event::register('before_output', [__CLASS__, 'before_output']);
    }

    ######################################################################

    public static function before_capture() {
      self::$capture_timestamp = microtime(true);
    }

    public static function after_capture() {

      self::$capture_duration = microtime(true) - self::$capture_timestamp;

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
             . '  - Content Capture Time: ' . number_format(self::$capture_duration * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Database Queries: ' . number_format(database::$stats['queries'], 0, '.', ' ') . PHP_EOL
             . '  - Database Duration: ' . number_format(database::$stats['duration'] * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - Network Requests: ' . number_format(http_client::$stats['requests'], 0, '.', ' ') . PHP_EOL
             . '  - Network Duration: ' . number_format(http_client::$stats['duration'] * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '  - vMod: ' . number_format(vmod::$time_elapsed * 1000, 0, '.', ' ') . ' ms' . PHP_EOL
             . '-->';

      $GLOBALS['output'] = preg_replace('#</html>#', '</html>' . PHP_EOL . $stats, $GLOBALS['output']);
    }
  }

<?php

  class stats {

    private static $_data;
    private static $_page_parse_start;
    private static $_capture_parse_start;


    public static function construct() {

    // Set time stamp for execution
      self::$_page_parse_start = microtime(true);
    }

    //public static function load_dependencies() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    public static function capture() {
      self::$_capture_parse_start = microtime(true);
    }

    public static function after_capture() {
      if (self::get('page_parse_time') > 5) {
        notices::add('warnings', sprintf(language::translate('text_long_execution_time', 'We apologize for the inconvenience that the server seems temporary overloaded right now.'), number_format($page_parse_time, 1, ',', ' ')));
        error_log('Warning: Long page execution time '. number_format($page_parse_time, 3, ',', ' ') .' s - '. $_SERVER['REQUEST_URI']);
      }
    }

    public static function prepare_output() {

    // Capture parse time
      $page_parse_time = microtime(true) - self::$_page_parse_start;
      self::set('page_capture_time', $page_parse_time);

    // Memory peak usage
      self::set('memory_peak_usage', memory_get_peak_usage() / 1024 / 1024);

    // Page parse time
      $page_parse_time = microtime(true) - self::$_page_parse_start;
      self::set('page_parse_time', $page_parse_time);

    // Add stats to snippet
      document::$snippets['stats'] = '<p><strong>System Statistics:</strong></p>' . PHP_EOL
                                   . '<ul>' . PHP_EOL
                                   . '  <li>'. language::translate('title_page_parse_time', 'Page Parse Time') .': ' . number_format(self::get('page_parse_time'), 3, '.', ' ') . ' s</li>' . PHP_EOL
                                   . '  <li>'. language::translate('title_page_capture_time', 'Page Capture Time') .': ' . number_format(self::get('page_capture_time'), 3, '.', ' ') . ' s</li>' . PHP_EOL
                                   . '  <li>'. language::translate('title_included_files', 'Included Files') .': ' . count(get_included_files()) . '</li>' . PHP_EOL
                                   . '  <li>'. language::translate('title_memory_peak', 'Memory Peak') .': ' . number_format(self::get('memory_peak_usage'), 2, '.', ' ') . ' MB</li>' . PHP_EOL
                                   . '  <li>'. language::translate('title_memory_limit', 'Memory Limit') .': ' . ini_get('memory_limit') . '</li>' . PHP_EOL
                                   . '  <li>'. language::translate('title_database_queries', 'Database Queries') .': ' . number_format(self::get('database_queries'), 0, '.', ' ') . '</li>' . PHP_EOL
                                   . '  <li>'. language::translate('title_database_parse_time', 'Database Parse Time') .': ' . number_format(self::get('database_execution_time'), 3, '.', ' ') . ' s (' . number_format(self::get('database_execution_time')/self::get('page_parse_time')*100, 0, '.', ' ') . ' %)</li>' . PHP_EOL
                                   . '</ul>';
    }

    //public static function before_output() {
    //}

    //public static function shutdown() {
    //}

    ######################################################################

    public static function set($key, $value) {
      self::$_data[$key] = $value;
    }

    public static function get($key) {
      if (isset(self::$_data[$key])) return self::$_data[$key];
    }
  }

<?php

  class database {
    private static $_links = [];

    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset=DB_CONNECTION_CHARSET) {

      if (!isset(self::$_links[$link])) {

        if (class_exists('stats', false)) {
          stats::start_watch('database_execution');
        }

        self::$_links[$link] = mysqli_init();

        self::set_option(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1, $link);

        if (!mysqli_real_connect(self::$_links[$link], $server, $username, $password, $database)) {
          trigger_error('Could not connect to database: '. mysqli_connect_errno() .' - '. mysqli_connect_error(), E_USER_ERROR);
        }

        if (($duration = stats::get_watch('database_execution')) > 1) {
          error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL connection established in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL, 3, FS_DIR_APP . 'logs/performance.log');
        }

        if (class_exists('stats', false)) {
          stats::stop_watch('database_execution');
        }
      }

      if (!is_object(self::$_links[$link])) {
        trigger_error('Invalid database link', E_USER_ERROR);
      }

      if (!empty($charset)) {
        self::set_charset($charset);
      }

      $sql_mode_query = self::query("select @@SESSION.sql_mode;", $link);
      $sql_mode = self::fetch($sql_mode_query, '@@SESSION.sql_mode');
      $sql_mode = preg_split('#\s*,\s*#', $sql_mode, -1, PREG_SPLIT_NO_EMPTY);

      $undesired_modes = [
        'TRADITIONAL',         // Shortcut flag for a bunch of other flags like below
        'STRICT_ALL_TABLES',   // Strict mode [MySQL 5.7+, MariaDB 10.2.4+]
        'STRICT_TRANS_TABLES', // Strict mode [MySQL 5.7+, MariaDB 10.2.4+]
        'ONLY_FULL_GROUP_BY',  // Requiring an undesired amount of columns in group by clause [MySQL 5.7+]
        'NO_ZERO_DATE',        // Prevents us from sending in empty dates [MySQL 5.7+]
        'NO_ZERO_IN_DATE',     // Prevents us from sending in a zero date 0000-00-00 [MySQL 5.7+]
      ];

      foreach ($undesired_modes as $mode) {
        if (($key = array_search($mode, $sql_mode)) !== false) {
          unset($sql_mode[$key]);
        }
      }

      self::query("SET SESSION sql_mode = '". database::input(implode(',', $sql_mode)) ."';", $link);
      self::query("SET names '". database::input($charset) ."';", $link);

      event::register('shutdown', [__CLASS__, 'disconnect']);

      return self::$_links[$link];
    }

    public static function set_charset($charset, $link='default') {

      if (!$result = mysqli_set_charset(self::$_links[$link], $charset)) {
        trigger_error('Could not set database connection charset: '. mysqli_connect_errno() .' - '. mysqli_connect_error(), E_USER_ERROR);
      }

      return true;
    }

    public static function set_option($option, $value, $link='default') {

      if (!$result = mysqli_options(self::$_links[$link], $option, $value)) {
        trigger_error('Could not set database connection charset: '. mysqli_connect_errno() .' - '. mysqli_connect_error(), E_USER_ERROR);
      }

      return true;
    }

    public static function disconnect($link=null) {

      if (!empty($link)) {
        $links = [self::$_links[$link]];
      } else {
        $links = self::$_links;
      }

      $errors = false;
      foreach (array_keys($links) as $link) {
        if (!is_object($link)) {
          $errors = true;
        } else {
          mysqli_close(self::$_links[$link]);
          unset(self::$_links[$link]);
        }
      }

      return $errors ? true : false;
    }

    public static function query($query, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      if (class_exists('stats', false)) {
        stats::start_watch('database_execution');
      }

      if (($result = mysqli_query(self::$_links[$link], $query)) === false) {
        trigger_error(mysqli_errno(self::$_links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$_links[$link])) . PHP_EOL . preg_replace('#^\s+#m', '', $query) . PHP_EOL, E_USER_ERROR);
      }

      if (($duration = stats::get_watch('database_execution')) > 3) {
        error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $query) . PHP_EOL, 3, FS_DIR_STORAGE . 'logs/performance.log');
      }

      if (class_exists('stats', false)) {
        stats::increase_count('database_queries');
        stats::stop_watch('database_execution');
      }

      return $result;
    }

    public static function multi_query($query, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      if (class_exists('stats', false)) {
        stats::start_watch('database_execution');
      }

      if (($result = mysqli_multi_query(self::$_links[$link], $query)) === false) {
        trigger_error(mysqli_errno(self::$_links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$_links[$link])) . PHP_EOL . preg_replace('#^\s+#m', '', $query) . PHP_EOL, E_USER_ERROR);
      }

      $i = 1;
      while (mysqli_more_results(self::$_links[$link])) {
        if (mysqli_next_result(self::$_links[$link]) === false) {
          die('Fatal: Query '. $i .' failed');
        }
        $i++;
      }

      if (class_exists('stats', false)) {
        stats::increase_count('database_queries', $i);
        stats::stop_watch('database_execution');
      }
    }

    public static function fetch($result, $column='') {
      if (class_exists('stats', false)) {
        stats::start_watch('database_execution');
      }

      $row = mysqli_fetch_assoc($result);

      if (class_exists('stats', false)) {
        stats::stop_watch('database_execution');
      }

      if ($column) {
        if (isset($row[$column])) {
          return $row[$column];
        } else {
          return false;
        }
      }

      return $row;
    }

    public static function seek($result, $offset) {
      return mysqli_data_seek($result, $offset);
    }

    public static function num_rows($result) {
      return mysqli_num_rows($result);
    }

    public static function free($result) {
      return mysqli_free_result($result);
    }

    public static function insert_id($link='default') {
      return mysqli_insert_id(self::$_links[$link]);
    }

    public static function affected_rows($link='default') {
      return mysqli_affected_rows(self::$_links[$link]);
    }

    public static function info($link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      return mysqli_info(self::$_links[$link]);
    }

    public static function create_variable($column_type, $value='') {

      switch (true) {
        case (preg_match('#^(bit|int|tinyint|smallint|mediumint|bigint)#i', $column_type)):
          return intval($value);

      case (preg_match('#^(decimal|double|float)#i', $column_type)):
        return floatval($value);

        default:
          return strval($value);
      }
    }

    public static function input($string, $allowable_tags=false, $trim=true, $link='default') {

      if (empty($string) || in_array(gettype($string), ['null', 'boolean', 'double', 'integer', 'float'])) {
        return $string;
      }

      if (is_array($string)) {
        foreach (array_keys($string) as $key) {
          $string[$key] = self::input($string[$key], $allowable_tags, $trim, $link);
        }
        return $string;
      }

      if ($allowable_tags !== true) {
        if ($allowable_tags != '') {
          $string = strip_tags($string, $allowable_tags);
        } else {
          $string = strip_tags($string);
        }
      }

      if ($trim === true) {
        $string = trim($string);
      } else if ($trim != '') {
        $string = trim($string, $trim);
      }

      if (!isset(self::$_links[$link])) self::connect($link);

      return mysqli_real_escape_string(self::$_links[$link], $string);
    }
  }

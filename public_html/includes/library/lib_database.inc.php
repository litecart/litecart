<?php

  class database {
    private static $_links = array();

    public static function init() {
      event::register('shutdown', array(__CLASS__, 'disconnect'));
    }

    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset=DB_CONNECTION_CHARSET) {

      if (!isset(self::$_links[$link])) {

        $measure_start = microtime(true);

        self::$_links[$link] = new mysqli($server, $username, $password, $database);

        if (($duration = microtime(true) - $measure_start) > 1) {
          error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL connection established in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL, 3, FS_DIR_APP . 'logs/performance.log');
        }

        if (class_exists('stats', false)) {
          stats::set('database_execution_time', stats::get('database_execution_time') + $duration);
        }
      }

      if (!is_object(self::$_links[$link])) {
        trigger_error('Error: Invalid database link', E_USER_ERROR);
      }

      if (self::$_links[$link]->connect_error) exit;

      self::set_encoding($charset);

      $sql_mode_query = self::query("select @@SESSION.sql_mode;", $link);
      $sql_mode = self::fetch($sql_mode_query, '@@SESSION.sql_mode');
      $sql_mode = preg_split('# ?, ?#', $sql_mode);

      $undesired_modes = array(
        'TRADITIONAL',         // Shortcut flag for a bunch of other flags like below
        'STRICT_ALL_TABLES',   // Strict mode [MySQL 5.7+, MariaDB 10.2.4+]
        'STRICT_TRANS_TABLES', // Strict mode [MySQL 5.7+, MariaDB 10.2.4+]
        'ONLY_FULL_GROUP_BY',  // Requiring an undesired amount of columns in group by clause [MySQL 5.7+]
        'NO_ZERO_DATE',        // Prevents us from sending in empty dates [MySQL 5.7+]
        'NO_ZERO_IN_DATE',     // Prevents us from sending in a zero date 0000-00-00 [MySQL 5.7+]
      );

      foreach ($undesired_modes as $mode) {
        if (($key = array_search($mode, $sql_mode)) !== false) {
          unset($sql_mode[$key]);
        }
      }

      self::query("SET @@session.sql_mode = '". database::input(implode(',', $sql_mode)) ."';", $link);

      self::query("set names '". database::input($charset) ."';", $link);

      return self::$_links[$link];
    }

    public static function set_charset($charset, $link='default') {
      return self::$_links[$link]->set_charset($charset);
    }

    public static function set_encoding($charset, $collation=null, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      if (empty($charset)) return false;

      $charset = strtolower($charset);

      $charset_to_mysql_character_set = array(
        'euc-kr' => 'euckr',
        'iso-8859-1' => 'latin1',
        'iso-8859-2' => 'latin2',
        'iso-8859-3' => 'latin7',
        'iso-8859-4' => 'cp1257',
        'iso-8859-5' => 'cp1251',
        'iso-8859-6' => 'cp1256',
        'iso-8859-7' => 'greek',
        'iso-8859-8' => 'hebrew',
        'iso-8859-9' => 'latin5',
        'iso-8859-13' => 'latin7',
        'iso-2022-jp' => 'cp932',
        'iso-2022-jp-2' => 'eucjpms',
        'iso-2022-kr' => 'euckr',
        'utf-8' => 'utf8',
        'utf-16' => 'utf16',
        'windows-1250' => 'cp1250',
        'windows-1251' => 'cp1251',
        'windows-1252' => 'latin1',
        'windows-1256' => 'cp1256',
        'windows-1257' => 'cp1257',
      );

      $charset = strtr($charset, $charset_to_mysql_character_set);

      if (!self::set_charset($charset)) {
        trigger_error('Unknown MySQL character set for charset '. $charset, E_USER_WARNING);
        return false;
      }

      if (!empty($collation)) {
        self::query("set collation_connection = ". database::input($collation), $link);
      }

      return true;
    }

    public static function disconnect($link=null) {

      if (!empty($link)) {
        $links = array(self::$_links[$link]);
      } else {
        $links = self::$_links;
      }

      $errors = false;
      foreach (array_keys($links) as $link) {
        if (!is_object($link)) {
          $errors = true;
        } else {
          self::$_links[$link]->close();
          unset(self::$_links[$link]);
        }
      }

      return $errors ? true : false;
    }

    public static function query($query, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      $measure_start = microtime(true);

      if (!$result = self::$_links[$link]->query($query)) {
        self::_error($query, self::$_links[$link]);
      }

      if (($duration = microtime(true) - $measure_start) > 3) {
        error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $query) . PHP_EOL, 3, FS_DIR_APP . 'logs/performance.log');
      }

      if (class_exists('stats', false)) {
        stats::set('database_queries', stats::get('database_queries') + 1);
        stats::set('database_execution_time', stats::get('database_execution_time') + $duration);
      }

      return $result;
    }

    public static function multi_query($query, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      if (self::$_links[$link]->multi_query($query)) {
        do {
          if ($result = self::$_links[$link]->use_result()) {
            while ($row = $result->fetch_row($result)) {
            }
            self::free($result);
          }
        }
        while (@self::$_links[$link]->next_result());
      } else {
        self::_error($query, self::$_links[$link]);
      }
    }

    public static function fetch($result, $column='') {

      $measure_start = microtime(true);

      $array = $result->fetch_assoc();

      $duration = microtime(true) - $measure_start;

      if (class_exists('stats', false)) {
        stats::set('database_execution_time', stats::get('database_execution_time') + $duration);
      }

      if ($column != '') return $array[$column];

      return $array;
    }

    public static function seek($result, $offset) {
      return $result->data_seek($offset);
    }

    public static function num_rows($result) {
      return $result->num_rows;
    }

    public static function free($result) {
      return $result->close();
    }

    public static function insert_id($link='default') {
      return self::$_links[$link]->insert_id;
    }

    public static function affected_rows($link='default') {
      return self::$_links[$link]->affected_rows;
    }

    public static function info($link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      return self::$_links[$link]->info;
    }

    public static function input($string, $allowable_tags=false, $trim=true, $link='default') {

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

      return self::$_links[$link]->escape_string($string);
    }

    private static function _error($query, $object) {

      $query = preg_replace('#^\s+#m', '', $query) . PHP_EOL;

      trigger_error($object->errno .' - '. preg_replace('#\r#', ' ', $object->error) . PHP_EOL . $query, E_USER_ERROR);
    }
  }

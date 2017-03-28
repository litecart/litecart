<?php

  class database {
    private static $_links = array();

    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset=DB_CONNECTION_CHARSET) {

      if (!isset(self::$_links[$link])) {

        $measure_start = microtime(true);

        self::$_links[$link] = new mysqli($server, $username, $password, $database) or exit;
        //self::$_links[$link]->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
        self::set_encoding($charset);

        if (($duration = microtime(true) - $measure_start) > 1) {
          error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL connection established in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL, 3, FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'performance.log');
        }

        if (class_exists('stats', false)) {
          stats::set('database_execution_time', stats::get('database_execution_time') + $duration);
        }
      }

      if (!is_resource(self::$_links[$link]) && !is_object(self::$_links[$link])) {
        trigger_error('Error: Invalid database link', E_USER_ERROR);
      }

      $sql_mode_query = self::query("select @@SESSION.sql_mode;");
      $sql_mode = self::fetch($sql_mode_query);

      if (strpos($sql_mode['@@SESSION.sql_mode'], 'STRICT_TRANS_TABLES') !== false) {
        $sql_mode['@@SESSION.sql_mode'] = str_replace($sql_mode['@@SESSION.sql_mode'], 'STRICT_TRANS_TABLES', '');
      }

      $sql_mode['@@SESSION.sql_mode'] = trim($sql_mode['@@SESSION.sql_mode']);

      self::query("SET @@session.sql_mode = '". database::input($sql_mode['@@SESSION.sql_mode']) ."';");

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
        self::query("set collation_connection = ". database::input($collation));
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
        error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $query) . PHP_EOL, 3, FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'performance.log');
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

    public static function fetch($result) {

      $measure_start = microtime(true);

      $array = $result->fetch_assoc();

      $duration = microtime(true) - $measure_start;

      if (class_exists('stats', false)) {
        stats::set('database_execution_time', stats::get('database_execution_time') + $duration);
      }

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

    public static function input($string, $allowable_tags=false, $link='default') {

      if (is_array($string)) {
        foreach (array_keys($string) as $key) {
          $string[$key] = self::input($string[$key]);
        }
        return $string;
      }

      if (is_bool($allowable_tags) === true && $allowable_tags !== true) {
        $string = strip_tags($string, $allowable_tags);
      }

      if (!isset(self::$_links[$link])) self::connect($link);

      return self::$_links[$link]->escape_string($string);
    }

    private static function _error($query, $object) {

      $query = preg_replace('#^\s+#m', '', $query) . PHP_EOL;

      trigger_error($object->errno .' - '. preg_replace('#\r#', ' ', $object->error) . PHP_EOL . $query, E_USER_ERROR);
    }
  }

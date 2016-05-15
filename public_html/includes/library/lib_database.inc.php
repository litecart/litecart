<?php

  class database {
    private static $_links = array();
    private static $_type = DB_TYPE;

    public static function construct() {
      if (self::$_type == 'mysql' && function_exists('mysqli_connect')) self::$_type = 'mysqli';
    }

    //public static function load_dependencies() {
    //}

    //public static function startup() {
    //}

    //public static function before_capture() {
    //}

    //public static function after_capture() {
    //}

    //public static function prepare_output() {
    //}

    //public static function before_output() {
    //}

    public static function shutdown() {

    // Close a non-persistent database connection
      if (!in_array(strtolower(DB_PERSISTENT_CONNECTIONS), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
        database::disconnect();
      }
    }

    ######################################################################

    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset=DB_CONNECTION_CHARSET) {

      if (!isset(self::$_links[$link]) || (!is_resource(self::$_links[$link]) && !is_object(self::$_links[$link]))) {

        $execution_time_start = microtime(true);

        if (self::$_type == 'mysqli') {

          if (in_array(strtolower(DB_PERSISTENT_CONNECTIONS), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
            self::$_links[$link] = mysqli_connect('p:'.$server, $username, $password, $database) or exit;
          } else {
            self::$_links[$link] = mysqli_connect($server, $username, $password, $database) or exit;
          }

        } else {

          if (in_array(strtolower(DB_PERSISTENT_CONNECTIONS), array('1', 'active', 'enabled', 'on', 'true', 'yes'))) {
            self::$_links[$link] = mysql_pconnect($server, $username, $password, 65536) or exit;
          } else {
            self::$_links[$link] = mysql_connect($server, $username, $password, false, 65536) or exit;
          }

          mysql_select_db($database) or self::_error(false, mysql_errno(), mysql_error());
        }

        $execution_time_stop = microtime(true);
        $execution_time_duration = $execution_time_stop - $execution_time_start;

        if ($execution_time_duration > 1) {
          error_log('Warning: A MySQL connection established in '. number_format($execution_time_duration, 3, '.', ' ') .' s.' . PHP_EOL, 3, FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'performance.log');
        }

        if (class_exists('stats', false)) {
          stats::set('database_execution_time', stats::get('database_execution_time') + $execution_time_duration);
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

    public static function set_encoding($charset, $collation=null, $link='default') {

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

      if (empty($charset_to_mysql_character_set[$charset])) {
        trigger_error('Unknown MySQL character set for charset '. $charset, E_USER_WARNING);
        return false;
      }

      if (!empty($collation)) {
        self::query("set names '". database::input($charset_to_mysql_character_set[$charset]) ."' collate '". database::input($collation) ."';", $link);
      } else {
        self::query("set names '". database::input($charset_to_mysql_character_set[$charset]) ."';", $link);
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
        if (!is_resource($link)) {
          $errors = true;
        } else {
          if (self::$_type == 'mysqli') {
            mysqli_close(self::$_links[$link]);
          } else {
            mysql_close(self::$_links[$link]);
          }
          unset(self::$_links[$link]);
        }
      }

      return $errors ? true : false;
    }

    public static function query($query, $link='default') {

      if (!isset(self::$_links[$link]) || is_resource(self::$_links[$link])) self::connect($link);

      $execution_time_start = microtime(true);

      if (self::$_type == 'mysqli') {
        $result = mysqli_query(self::$_links[$link], $query) or self::_error($query, mysqli_errno(self::$_links[$link]), mysqli_error(self::$_links[$link]));
      } else {
        $result = mysql_query($query, self::$_links[$link]) or exit;
      }

      $execution_time_stop = microtime(true);
      $execution_time_duration = $execution_time_stop - $execution_time_start;

      if ($execution_time_duration > 3) {
        error_log('Warning: A MySQL query executed in '. number_format($execution_time_duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $query) . PHP_EOL, 3, FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'performance.log');
      }

      if (class_exists('stats', false)) {
        stats::set('database_queries', stats::get('database_queries') + 1);
        stats::set('database_execution_time', stats::get('database_execution_time') + $execution_time_duration);
      }

      return $result;
    }

    public static function multi_query($query, $link='default') {

      if (!isset(self::$_links[$link]) || is_resource(self::$_links[$link])) self::connect($link);

      if (self::$_type == 'mysqli') {
        if (mysqli_multi_query(self::$_links[$link], $query) or self::_error($query, mysqli_errno(self::$_links[$link]), mysqli_error(self::$_links[$link]))) {
          do {
            if ($result = mysqli_use_result(self::$_links[$link])) {
              while ($row = mysqli_fetch_row($result)) {
              }
              mysqli_free_result($result);
            }
          }
          while (mysqli_next_result(self::$_links[$link]));
        }
      } else {
        self::query($query, self::$_links[$link]); // don't pick up results - we're not supporting it
      }

      return;
    }

    public static function fetch($result) {

      $execution_time_start = microtime(true);

      if (self::$_type == 'mysqli') {
        $array = mysqli_fetch_assoc($result);
      } else {
        $array = mysql_fetch_assoc($result);
      }

      $execution_time_stop = microtime(true);
      $execution_time_duration = $execution_time_stop - $execution_time_start;

      if (class_exists('stats', false)) {
        stats::set('database_execution_time', stats::get('database_execution_time') + $execution_time_duration);
      }

      return $array;
    }

    public static function seek($result, $offset) {
      if (self::$_type == 'mysqli') {
        return mysqli_data_seek($result, $offset);
      } else {
        return mysql_data_seek($result, $offset);
      }
    }

    public static function num_rows($result) {
      if (self::$_type == 'mysqli') {
        return mysqli_num_rows($result);
      } else {
        return mysql_num_rows($result);
      }
    }

    public static function free($result) {
      if (self::$_type == 'mysqli') {
        return mysqli_free_result($result);
      } else {
        return mysql_free_result($result);
      }
    }

    public static function insert_id($link='default') {
      if (self::$_type == 'mysqli') {
        return mysqli_insert_id(self::$_links[$link]);
      } else {
        return mysql_insert_id(self::$_links[$link]);
      }
    }

    public static function affected_rows($link='default') {
      if (self::$_type == 'mysqli') {
        return mysqli_affected_rows(self::$_links[$link]);
      } else {
        return mysql_affected_rows(self::$_links[$link]);
      }
    }

    public static function info($link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      if (self::$_type == 'mysqli') {
        return mysqli_info(self::$_links[$link]);
      } else {
        return mysql_info(self::$_links[$link]);
      }
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

      if (self::$_type == 'mysqli') {
        return mysqli_real_escape_string(self::$_links[$link], $string);
      } else {
        return mysql_real_escape_string($string, self::$_links[$link]);
      }
    }

    private static function _error($query, $errno, $error) {
      trigger_error($errno .' - '. str_replace("\r\n", ' ', $error) ."\r\n  ". str_replace("\r\n", "\r\n  ", $query), E_USER_ERROR);
    }
  }

?>
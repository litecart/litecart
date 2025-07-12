<?php

  class database {
    private static $_links = [];
    public static $stats = [
      'duration' => 0,
      'queries' => 0,
    ];

    public static function init() {
      event::register('shutdown', [__CLASS__, 'disconnect']);
    }

    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset=DB_CONNECTION_CHARSET) {

      if (!isset(self::$_links[$link])) {

        $measure_start = microtime(true);

        mysqli_report(MYSQLI_REPORT_OFF);

        self::$_links[$link] = mysqli_init();

        if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
          self::set_option(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1, $link);
        } else {
          trigger_error('Undefined constant MYSQLI_OPT_INT_AND_FLOAT_NATIVE. Make sure you enabled the PHP extension mysqlnd which is the recommended driver since PHP 5.4 instead of libmysql.', E_USER_WARNING);
        }

        if (!mysqli_real_connect(self::$_links[$link], $server, $username, $password, $database)) {
          throw new Error('Could not connect to database: '. mysqli_connect_errno() .' - '. mysqli_connect_error());
        }

        if (($duration = microtime(true) - $measure_start) > 1) {
          $log_message = '['. date('Y-m-d H:i:s e').'] A MySQL connection established in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL . PHP_EOL;
          file_put_contents(FS_DIR_STORAGE . 'logs/performance.log', $log_message, FILE_APPEND);
        }

        if (class_exists('stats', false)) {
          self::$stats['duration'] += $duration;
        }
      }

      if (!is_object(self::$_links[$link])) {
        throw new Error('Invalid database link');
      }

      if (!empty($charset)) {
        self::set_charset($charset, $link);
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

      self::query("set names '". database::input($charset) ."';", $link);

    // Set time zone for current session
      if (defined('DB_TABLE_PREFIX')) {
        $setting_query = database::query("SELECT * FROM ". DB_TABLE_PREFIX ."settings WHERE `key` = 'store_timezone' LIMIT 1;", 'default');

        if ($timezone = database::fetch($setting_query, 'value')) {
          $datetime = new \DateTime('now', new \DateTimezone($timezone));
          self::query("set time_zone = '". $datetime->format('P') ."';", $link);
        }
      }

      return self::$_links[$link];
    }

    public static function server_info($link='default') {

      if (!$result = mysqli_get_server_info(self::$_links[$link])) {
        trigger_error('Could not get server info for MySQL connection: '. mysqli_errno(self::$_links[$link]) .' - '. mysqli_error(self::$_links[$link]), E_USER_WARNING);
        return false;
      }

      return $result;
    }

    public static function set_charset($charset, $link='default') {

      if (!$result = mysqli_set_charset(self::$_links[$link], $charset)) {
        trigger_error('Could not set charset for MySQL connection: '. mysqli_errno(self::$_links[$link]) .' - '. mysqli_error(self::$_links[$link]), E_USER_WARNING);
        return false;
      }

      return $result;
    }

    public static function set_encoding($charset, $collation=null, $link='default') {

      if (!$charset) {
        return false;
      }

      if (!isset(self::$_links[$link])) {
        self::connect($link);
      }

      $charset = strtolower($charset);

      $charset_to_mysql_character_set = [
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
      ];

      if (isset($charset_to_mysql_character_set[$charset])) {
        $charset = $charset_to_mysql_character_set[$charset];
      }

      if (!self::set_charset($charset, $link)) {
        trigger_error('Unknown MySQL character set for charset '. $charset, E_USER_WARNING);
        return false;
      }

      if (!empty($collation)) {
        self::query("set collation_connection = ". database::input($collation), $link);
      }

      return true;
    }

    public static function set_option($option, $value, $link='default') {

      if (!$result = mysqli_options(self::$_links[$link], $option, $value)) {
        throw new Error('Could not set MySQL option "'. $option .'" to "'. $value .'"');
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

      if (!isset(self::$_links[$link])) {
        self::connect($link);
      }

      $measure_start = microtime(true);

      if (($result = mysqli_query(self::$_links[$link], $query)) === false) {
        throw new Error(mysqli_errno(self::$_links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$_links[$link])) . PHP_EOL . preg_replace('#^\s+#m', '', $query) . PHP_EOL);
      }

      if (($duration = microtime(true) - $measure_start) > 3) {
        $log_message = '['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL
                     . '  Query: '. str_replace("\r\n", "\r\n    ", $query) . PHP_EOL . PHP_EOL;
        file_put_contents(FS_DIR_STORAGE . 'logs/performance.log', $log_message, FILE_APPEND);
      }

      self::$stats['queries']++;
      self::$stats['duration'] += $duration;

      if ($result instanceof mysqli_result) {
        return new database_result($result);
      }

      return $result;
    }

    public static function multi_query($sql, $link='default') {

      if (!isset(self::$_links[$link])) {
        self::connect($link);
      }

      $timestamp = microtime(true);

      if (mysqli_multi_query(self::$_links[$link], $sql) === false) {
        throw new Error(mysqli_errno(self::$_links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$_links[$link])) . PHP_EOL . preg_replace('#^\s+#m', '', $sql) . PHP_EOL);
      }

      $results = [];

      do {
        if ($result = mysqli_store_result(self::$_links[$link])) {
          $results[] = new database_result($result);
        }
      } while (mysqli_next_result(self::$_links[$link]));

      self::$stats['queries']++;
      self::$stats['duration'] += microtime(true) - $timestamp;

      return $results;
    }

    public static function fetch($result, $column='') {
      return $result->fetch($column);
    }

    public static function fetch_all($result, $column=null, $index_column=null) {
      return $result->fetch_all($column=null, $index_column=null);
    }

    public static function seek($result, $offset) {
      return $result->seek($offset);
    }

    public static function num_rows($result) {
      return $result->num_rows;
    }

    public static function free($result) {
      return $result->free();
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

    public static function create_variable($field, $value=null) {

      if (!$field) {
        return null;
      }

      if (is_string($field)) {
        $field = [
          'Type' => $field,
          'Default' => null,
        ];
      }

      if ($field['Default'] == "''") {
        $field['Default'] = '';
      }

      if ($field['Default']) {

        $search_replace = [
          '#^\'\'$#' => '',
          '#^null$#i' => null,
          '#^(now|current_timestamp)(\(\))?$#i' => date('Y-m-d H:i:s'),
        ];

        $field['Default'] = preg_replace(array_keys($search_replace), array_values($search_replace), $field['Default']);
      }

      switch (true) {
        case (preg_match('#^(bit|int|tinyint|smallint|mediumint|bigint)#i', $field['Type'])):
          return intval(!is_null($value) ? $value : $field['Default']);

        case (preg_match('#^(decimal|double|float)#i', $field['Type'])):
          return floatval(!is_null($value) ? $value : $field['Default']);

        default:
          return strval(!is_null($value) ? $value : $field['Default']);
      }
    }

    public static function input($input, $allowable_tags=false, $trim=true, $link='default') {

      if (is_array($input)) {
        foreach (array_keys($input) as $key) {
          $input[$key] = self::input($input[$key], $allowable_tags, $trim, $link);
        }
        return $input;
      }

      if ($input == '') {
        return '';
      }

      if (in_array(gettype($input), ['null', 'boolean', 'double', 'integer', 'float'])) {
        return $input;
      }

      if ($allowable_tags !== true) {
        if ($allowable_tags != '') {
          $input = strip_tags($input, $allowable_tags);
        } else {
          $input = strip_tags($input);
        }
      }

      if ($trim === true) {
        $input = trim($input);
      } else if ($trim != '') {
        $input = trim($input, $trim);
      }

      if (!isset(self::$_links[$link])) {
        self::connect($link);
      }

      return mysqli_real_escape_string(self::$_links[$link], $input);
    }

    public static function input_fulltext($input, $allowable_tags=false, $trim=true, $link='default') {
      $input = self::input($input, $allowable_tags, $trim, $link);
      $input = preg_replace('#[+\-<>\(\)~*\"@; ]+#', ' ', $input);
      return $input;
    }

    public static function input_like($input, $allowable_tags=false, $trim=true, $link='default') {
      $input = self::input($input, $allowable_tags, $trim, $link);
      $input = addcslashes($input, '%_');
      return $input;
    }
  }

  class database_result {
    private $_result;

    public function __construct(mysqli_result $result) {
      $this->_result = $result;
    }

    public function __call($method, $arguments) {
      return call_user_func_array([$this->_result, $method], $arguments);
    }

    public function __get($name) {
      return $this->_result->$name;
    }

    public function __set($name, $value) {
      // Do nothing
    }

    public function export(&$object) {
      return $object = $this;
    }

    public function fields() {
      $fields = array_column(mysqli_fetch_fields($this->_result), 'name');
      return $fields;
    }

    public function fetch($filter=null) {

      $timestamp = microtime(true);

      $row = mysqli_fetch_assoc($this->_result);

      if ($row !== null && $filter) {
        switch (gettype($filter)) {

          case 'array':
            $result = array_intersect_key($row, array_flip($filter));
            break;

          case 'string':
            if (isset($row[$filter])) {
              $result = $row[$filter];
            } else {
              $result = false;
            }
            break;

          case 'object':
            $result = call_user_func_array($filter, [&$row]);
            if ($result === null) { // Was no result returned?
              $result = $row;
            }
            break;

          default:
            $row = false;
            break;
        }
      } else {
        $result = $row;
      }

      database::$stats['duration'] += microtime(true) - $timestamp;

      return $result;
    }

    public function fetch_all($filter=null, $index_column=null) {

      $timestamp = microtime(true);

      if ($filter || $index_column) {

        $rows = [];

        while ($row = mysqli_fetch_assoc($this->_result)) {

          if ($filter) {

            switch (gettype($filter)) {

              case 'array':
                $result = array_intersect_key($row, array_flip($filter));
                break;

              case 'string':
                if (isset($row[$filter])) {
                  $result = $row[$filter];
                } else {
                  $result = false;
                }
                break;

              case 'object':
                $result = call_user_func_array($filter, [&$row]);
                if ($result === null) { // Was no result returned?
                  $result = $row;
                }
                break;

              default:
                $result = false;
            }

          } else {
            $result = $row;
          }

          if (empty($result) && !is_numeric($result)) {
            continue;
          }

          if ($index_column) {

            if (isset($row[$index_column])) {
              $rows[$row[$index_column]] = $result;
          } else {
              trigger_error('Index column not found in row ('. $index_column .')', E_USER_WARNING);
              $rows[] = false;
            }

          } else {
            $rows[] = $result;
          }
        }

      } else {
        $rows = mysqli_fetch_all($this->_result, MYSQLI_ASSOC);
      }

      database::$stats['duration'] += microtime(true) - $timestamp;

      return $rows;
    }

    public function fetch_page($filter=null, $index_column=null, $page=1, $items_per_page=null, &$num_rows=null, &$num_pages=null) {

      $timestamp = microtime(true);

      if (!is_numeric($page) || $page < 1) {
        $page = 1;
      }

      if (!$items_per_page) {
        $items_per_page = settings::get('data_table_rows_per_page');
      }

      $rows = [];
      $num_rows = mysqli_num_rows($this->_result);
      $num_pages = ceil($num_rows / $items_per_page);
      $pointer = (((int)$_GET['page']) -1) * $items_per_page;

      if ($pointer < $num_rows) {

        mysqli_data_seek($this->_result, $pointer);

        for ($i=0; $i < $items_per_page; $i++) {

          $row = $this->fetch($filter, $index_column);
          $pointer++;

          if (!empty($row) || !is_numeric($row)) {
        $rows[] = $row;
          }

          if ($pointer == $num_rows) {
            break; // We reached the end of the result set
          }
        }
      }

      database::$stats['duration'] += microtime(true) - $timestamp;

      return $rows;
    }

    public function each(callable $function) {
      while ($row = $this->fetch()) {
        call_user_func($function, $row);
      }
    }

    public function seek($offset) {
      mysqli_data_seek($this->_result, $offset);
      return $this;
    }

    public function num_rows() {
      return mysqli_num_rows($this->_result);
    }

    public function free() {
      return mysqli_free_result($this->_result);
    }

    public function __destruct() {
      $this->free();
    }
  }

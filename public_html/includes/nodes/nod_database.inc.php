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

    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset='utf8mb4') {

      if (!isset(self::$_links[$link])) {

        $timestamp = microtime(true);

        mysqli_report(MYSQLI_REPORT_OFF);

        self::$_links[$link] = mysqli_init();

        if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
          self::set_option(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1, $link);
        } else {
          trigger_error('Undefined constant MYSQLI_OPT_INT_AND_FLOAT_NATIVE', E_USER_WARNING);
        }

        if (!mysqli_real_connect(self::$_links[$link], $server, $username, $password, $database)) {
          trigger_error('Could not connect to database: '. mysqli_connect_errno() .' - '. mysqli_connect_error(), E_USER_ERROR);
        }

        if (($duration = microtime(true) - $timestamp) > 1) {
          error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL connection established in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL, 3, 'app://logs/performance.log');
        }

        self::$stats['duration'] += microtime(true) - $timestamp;
      }

      if (!is_object(self::$_links[$link])) {
        trigger_error('Invalid database link', E_USER_ERROR);
      }

      if (!empty($charset)) {
        self::set_charset($charset, $link);
      }

      $sql_mode = self::query("select @@SESSION.sql_mode as sql_mode;", $link)->fetch('sql_mode');
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
      }

      return true;
    }

    public static function set_option($option, $value, $link='default') {

      if (!$result = mysqli_options(self::$_links[$link], $option, $value)) {
        trigger_error('Could not set option '. $option .' to '. $value, E_USER_ERROR);
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

    public static function query($sql, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      $timestamp = microtime(true);

      if (($result = mysqli_query(self::$_links[$link], $sql)) === false) {
        trigger_error(mysqli_errno(self::$_links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$_links[$link])) . PHP_EOL . preg_replace('#^\s+#m', '', $sql) . PHP_EOL, E_USER_ERROR);
      }

      if (($duration = microtime(true) - $timestamp) > 3) {
        error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $sql) . PHP_EOL, 3, 'storage://logs/performance.log');
      }

      self::$stats['queries']++;
      self::$stats['duration'] += $duration;

      if ($result instanceof mysqli_result) {
        return new database_result($sql, $result);
      }

      return $result;
    }

    public static function multi_query($query, $link='default') {

      if (!isset(self::$_links[$link])) self::connect($link);

      $timestamp = microtime(true);

      if (($result = mysqli_multi_query(self::$_links[$link], $sql)) === false) {
        trigger_error(mysqli_errno(self::$_links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$_links[$link])) . PHP_EOL . preg_replace('#^\s+#m', '', $sql) . PHP_EOL, E_USER_ERROR);
      }

      $i = 1;
      while (mysqli_more_results(self::$_links[$link])) {
        if (mysqli_next_result(self::$_links[$link]) === false) {
          die('Fatal: Query '. $i .' failed');
        }
        $i++;
      }

      self::$stats['queries']++;
      self::$stats['duration'] += microtime(true) - $timestamp;
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

      if (empty($field)) return null;

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

      if (empty($input)) return '';

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

      if (!isset(self::$_links[$link])) self::connect($link);

      return mysqli_real_escape_string(self::$_links[$link], $input);
    }
  }

  class database_result {
    private $_query;
    private $_result;

    public function __construct(string $query, mysqli_result $result) {
      $this->_query = $query;
      $this->_result = $result;
    }

    public function __call($method, $arguments) {
      return call_user_func_array([$this->_result, $name], $arguments);
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
      $fields = array_column($this->_result->fetch_fields(), 'name');
      return $fields;
    }

    public function fetch($column='') {

      $timestamp = microtime(true);

      $row = mysqli_fetch_assoc($this->_result);

      if ($column) {
        if (isset($row[$column])) {
          $row = $row[$column];
        } else {
          $row = false;
        }
      }

      database::$stats['duration'] += microtime(true) - $timestamp;

      return $row;
    }

    public function fetch_all($column=null, $index_column=null) {

      $timestamp = microtime(true);

      if ($column || $index_column) {

        $rows = [];
        while ($row = mysqli_fetch_assoc($this->_result)) {
          if ($index_column) {
            $rows[$row[$index_column]] = $column ? $row[$column] : $row;
          } else {
            $rows[] = $column ? $row[$column] : $row;
          }
        }

      } else {
        $rows = mysqli_fetch_all($this->_result, MYSQLI_ASSOC);
      }

      database::$stats['duration'] += microtime(true) - $timestamp;

      return $rows;
    }

    public function fetch_page($page, $items_per_page=null, &$num_rows=null, &$num_pages=null) {

      $timestamp = microtime(true);

      if ($page < 1) $page = 1;
      if (!$items_per_page) $items_per_page = settings::get('data_table_rows_per_page');

      mysqli_data_seek($this->_result, ((int)$page -1) * $items_per_page);

      $num_rows = mysqli_num_rows($this->_result);
      $num_pages = ceil($num_rows / $items_per_page);

      $rows = [];

      $i = 0;
      while ($row = mysqli_fetch_assoc($this->_result)) {
        $rows[] = $row;
        if (++$i == $items_per_page) break;
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

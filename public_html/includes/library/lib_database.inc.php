<?php

  class database {
    
    private static $_links = array();
    private static $_type = 'mysql';
    
    public static function construct() {
      
      if (function_exists('mysqli_connect')) self::$_type = 'mysqli';
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
  
    public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE) {
      
    // Create link
      if (!isset(self::$_links[$link]) || (!is_resource(self::$_links[$link]) && !is_object(self::$_links[$link]))) {
      
      // Set start timestamp for debug
        $execution_time_start = microtime(true);
      
      // Connect
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
        
      // Set stop timestamp for debug
        $execution_time_stop = microtime(true);
      
      // Calculate duration time for debug
        $execution_time_duration = $execution_time_stop - $execution_time_start;
        
      // Check if duration was way too long
        if ($execution_time_duration > 5) {
          error_log('Warning: A MySQL connection established in '. number_format($execution_time_duration, 3, '.', ' ') .' s.');
        }
        
        stats::set('database_execution_time', stats::get('database_execution_time') + $execution_time_duration);
      }
      
      self::query("set character set ". DB_DATABASE_CHARSET);
      self::query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
      
    // Make sure link was established
      if (!is_resource(self::$_links[$link]) && !is_object(self::$_links[$link])) {
        trigger_error('Error: Invalid database link', E_USER_ERROR);
      }
      
    // Return connection link
      return self::$_links[$link];
    }
    
  // Set input/output mysql charset
    public static function set_character($charset) {
    
      $charset = strtolower($charset);
      
      $charset_map = array(
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
      
      if (empty($charset_map[$charset])) {
        trigger_error('Unknown MySQL charset for HTML charset '. $charset, E_USER_WARNING);
        return false;
      }
      
      self::query("set character set ". $charset_map[$charset]);
      
      return true;
    }
    
  // Close database connection
    public static function disconnect($link='') {
      
      if ($link != '') {
        $links = array(self::$_links[$link]);
      } else {
        $links = self::$_links;
      }
      
      $errors = false;
      foreach ($links as $link) {
        if (!is_resource($link)) {
          $errors = true;
        } else {
          if (self::$_type == 'mysqli') {
            mysqli_close($link);
          } else {
            mysql_close($link);
          }
        }
      }
      
      return $errors ? true : false;
    }
    
    public static function query($query, $link='default') {
      
    // Establish a link if not previously made
      if (!isset(self::$_links[$link]) || is_resource(self::$_links[$link])) self::connect($link);
      
    // Set start timestamp for debug
      $execution_time_start = microtime(true);
      
      //if (strtolower(substr($query, 0, 6)) == 'select') $query = '/*qc=on*/' . $query; // mysqlnd query cache plugin
      
    // Perform mysql query
      if (self::$_type == 'mysqli') {
        $result = mysqli_query(self::$_links[$link], $query) or self::_error($query, mysqli_errno(self::$_links[$link]), mysqli_error(self::$_links[$link]));
      } else {
        $result = mysql_query($query, self::$_links[$link]) or exit;
      }
      
    // Set stop timestamp for debug
      $execution_time_stop = microtime(true);
      
    // Calculate duration time for debug
      $execution_time_duration = $execution_time_stop - $execution_time_start;
      
    // Check if duration was way too long
      if ($execution_time_duration > 5) {
        error_log('Warning: A MySQL query executed in '. number_format($execution_time_duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $query));
      }
      
      stats::set('database_queries', stats::get('database_queries') + 1);
      stats::set('database_execution_time', stats::get('database_execution_time') + $execution_time_duration);
      
    // Return query resource
      return $result;
    }
    
    public function multi_query($query, $link='default') {
    
    // Establish a link if not previously made
      if (!isset($this->_links[$link]) || is_resource($this->_links[$link])) $this->connect($link);
      
      if ($this->_type == 'mysqli') {
        if (mysqli_multi_query($this->_links[$link], $query) or $this->_error($query, mysqli_errno($this->_links[$link]), mysqli_error($this->_links[$link]))) {
          do {
            if ($result = mysqli_use_result($link)) {
              while ($row = mysqli_fetch_row($result)) {
              }
              mysqli_free_result($result);
            }
          }
          while (mysqli_next_result($this->_links[$link]));
        }
      } else {
        $this->query($query, $this->_links[$link]); // don't pick up results - we're not supporting it
      }
      return;
    }
    
    public static function fetch($result) {
    
    // Set start timestamp for debug
      $execution_time_start = microtime(true);
      
    // Perform mysql query
      if (self::$_type == 'mysqli') {
        $array = mysqli_fetch_assoc($result);
      } else {
        $array = mysql_fetch_assoc($result);
      }
      
    // Set stop timestamp for debug
      $execution_time_stop = microtime(true);
      
    // Calculate duration time for debug
      $execution_time_duration = $execution_time_stop - $execution_time_start;
      
      stats::set('database_execution_time', stats::get('database_execution_time') + $execution_time_duration);
      
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
      if (self::$_type == 'mysqli') {
        return mysqli_info(self::$_links[$link]);
      } else {
        return mysql_info(self::$_links[$link]);
      }
    }
    
    public static function input($string, $allowable_tags=false, $link='default') {
      
    // Return safe array
      if (is_array($string)) {
        foreach (array_keys($string) as $key) {
          $string[$key] = self::input($string[$key]);
        }
        return $string;
      }
      
    // Unescape input
      $string = stripslashes($string);
      
    // Strip html tags
      if (is_bool($allowable_tags) === true && $allowable_tags !== true) {
        $string = strip_tags($string, $allowable_tags);
      }
      
    // Establish a link if not previously made
      if (!isset(self::$_links[$link])) self::connect();
      
    // Return safe input string
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
<?php

  class database {
    
    private $system;
    private $_links = array();
    private $_type = 'mysql';
    
    public function __construct(&$system) {
      $this->system = &$system;
      
      if (function_exists('mysqli_connect')) $this->_type = 'mysqli';
    }
    
    //public function load_dependencies() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    public function shutdown() {
      
    // Close a non-persistent database connection
      if (DB_PERSISTENT_CONNECTIONS != 'true') {
        $this->system->database->disconnect();
      }
    }
    
    ######################################################################
  
    public function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE) {
      
    // Create link
      if (!isset($this->_links[$link]) || (!is_resource($this->_links[$link]) && !is_object($this->_links[$link]))) {
      
      // Set start timestamp for debug
        $execution_time_start = microtime(true);
      
      // Connect
        if ($this->_type == 'mysqli') {
        
          if (DB_PERSISTENT_CONNECTIONS == 'true') {
            $this->_links[$link] = mysqli_connect('p:'.$server, $username, $password, $database) or exit;
          } else {
            $this->_links[$link] = mysqli_connect($server, $username, $password, $database) or exit;
          }
          
        } else {
        
          if (DB_PERSISTENT_CONNECTIONS == 'true') {
            $this->_links[$link] = mysql_pconnect($server, $username, $password, 65536) or exit;
          } else {
            $this->_links[$link] = mysql_connect($server, $username, $password, false, 65536) or exit;
          }
          
          mysql_select_db($database) or $this->_error(false, mysql_errno(), mysql_error());
        }
        
      // Set stop timestamp for debug
        $execution_time_stop = microtime(true);
      
      // Calculate duration time for debug
        $execution_time_duration = $execution_time_stop - $execution_time_start;
        
      // Check if duration was way too long
        if ($execution_time_duration > 5) {
          error_log('Warning: A MySQL connection established in '. number_format($execution_time_duration, 3, '.', ' ') .' s.');
        }
        
        $this->system->stats->set('database_execution_time', $this->system->stats->get('database_execution_time') + $execution_time_duration);
      }
      
      $this->query("set character set ". DB_DATABASE_CHARSET);
      $this->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
      
    // Make sure link was established
      if (!is_resource($this->_links[$link]) && !is_object($this->_links[$link])) {
        trigger_error('Error: Invalid database link', E_USER_ERROR);
      }
      
    // Return connection link
      return $this->_links[$link];
    }
    
  // Set input/output mysql charset
    public function set_character($charset) {
    
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
        'windows-1251' => 'cp1251',
        'windows-1252' => 'cp1252',
        'windows-1256' => 'cp1256',
      );
      
      if (empty($charset_map[$charset])) {
        trigger_error('Unknown MySQL charset for HTML charset '. $charset, E_USER_WARNING);
        return false;
      }
      
      $this->query("set character set ". $charset_map[$charset]);
      
      return true;
    }
    
  // Close database connection
    public function disconnect($link='') {
      
      if ($link != '') {
        $links = array($this->_links[$link]);
      } else {
        $links = $this->_links;
      }
      
      $errors = false;
      foreach ($links as $link) {
        if (!is_resource($link)) {
          $errors = true;
        } else {
          if ($this->_type == 'mysqli') {
            mysqli_close($link);
          } else {
            mysql_close($link);
          }
        }
      }
      
      return $errors ? true : false;
    }
    
    public function query($query, $link='default') {
      
    // Establish a link if not previously made
      if (!isset($this->_links[$link]) || is_resource($this->_links[$link])) $this->connect($link);
      
    // For debug
      if (!isset($this->history)) $this->history = array();
      $this->history[] = $query;
      
    // Set start timestamp for debug
      $execution_time_start = microtime(true);
      
      //if (strtolower(substr($query, 0, 6)) == 'select') $query = '/*qc=on*/' . $query; // mysqlnd query cache plugin
      
    // Perform mysql query
      if ($this->_type == 'mysqli') {
        $result = mysqli_query($this->_links[$link], $query) or $this->_error($query, mysqli_errno($this->_links[$link]), mysqli_error($this->_links[$link]));
      } else {
        $result = mysql_query($query, $this->_links[$link]) or exit;
      }
      
    // Set stop timestamp for debug
      $execution_time_stop = microtime(true);
      
    // Calculate duration time for debug
      $execution_time_duration = $execution_time_stop - $execution_time_start;
      
    // Check if duration was way too long
      if ($execution_time_duration > 5) {
        error_log('Warning: A MySQL query executed in '. number_format($execution_time_duration, 3, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $query));
      }
      
      $this->system->stats->set('database_queries', $this->system->stats->get('database_queries') + 1);
      $this->system->stats->set('database_execution_time', $this->system->stats->get('database_execution_time') + $execution_time_duration);
      
    // Return query resource
      return $result;
    }
    
    public function multi_query($query, $link='default') {
    
    // Establish a link if not previously made
      if (!isset($this->_links[$link]) || is_resource($this->_links[$link])) $this->connect($link);
      
      if ($this->_type == 'mysqli') {
        return mysqli_multi_query($this->_links[$link], $query) or $this->_error($query, mysqli_errno($this->_links[$link]), mysqli_error($this->_links[$link]));;
      } else {
        return $this->query($query, $link);
      }
    }
    
    public function fetch($result) {
    
    // Set start timestamp for debug
      $execution_time_start = microtime(true);
      
    // Perform mysql query
      if ($this->_type == 'mysqli') {
        $array = mysqli_fetch_assoc($result);
      } else {
        $array = mysql_fetch_assoc($result);
      }
      
    // Set stop timestamp for debug
      $execution_time_stop = microtime(true);
      
    // Calculate duration time for debug
      $execution_time_duration = $execution_time_stop - $execution_time_start;
      
      $this->system->stats->set('database_execution_time', $this->system->stats->get('database_execution_time') + $execution_time_duration);
      
      return $array;
    }
    
    public function seek($result, $offset) {
      if ($this->_type == 'mysqli') {
        return mysqli_data_seek($result, $offset);
      } else {
        return mysql_data_seek($result, $offset);
      }
    }
    
    public function num_rows($result) {
      if ($this->_type == 'mysqli') {
        return mysqli_num_rows($result);
      } else {
        return mysql_num_rows($result);
      }
    }

    public function free($result) {
      if ($this->_type == 'mysqli') {
        return mysqli_free_result($result);
      } else {
        return mysql_free_result($result);
      }
    }
    
    public function insert_id($link='default') {
      if ($this->_type == 'mysqli') {
        return mysqli_insert_id($this->_links[$link]);
      } else {
        return mysql_insert_id($this->_links[$link]);
      }
    }
    
    public function affected_rows($link='default') {
      if ($this->_type == 'mysqli') {
        return mysqli_affected_rows($this->_links[$link]);
      } else {
        return mysql_affected_rows($this->_links[$link]);
      }
    }
    
    public function info($link='default') {
      if ($this->_type == 'mysqli') {
        return mysqli_info($this->_links[$link]);
      } else {
        return mysql_info($this->_links[$link]);
      }
    }
    
    public function input($string, $allowable_tags=false, $link='default') {
      global $system;
      
    // Return safe array
      if (is_array($string)) {
        foreach (array_keys($string) as $key) {
          $string[$key] = $this->input($string[$key]);
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
      if (!isset($this->_links[$link])) $this->connect();
      
    // Return safe input string
      if ($this->_type == 'mysqli') {
        return mysqli_real_escape_string($this->_links[$link], $string);
      } else {
        return mysql_real_escape_string($string, $this->_links[$link]);
      }
    }
    
    private function _error($query, $errno, $error) {
      trigger_error($errno .' - '. str_replace("\r\n", ' ', $error) ."\r\n  ". str_replace("\r\n", "\r\n  ", $query), E_USER_ERROR);
    }
  }
  
?>
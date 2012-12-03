<?php

  class database {
    
    private $system;
    private $links = array();
    private $type = 'mysql';
    
    public function __construct(&$system) {
      $this->system = &$system;
      
      if (function_exists('mysqli_connect')) $this->type = 'mysqli';
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
    
    public function before_output() {
    }
    
    public function shutdown() {
      
    // Close a non-persistent database connection
      if (DB_PERSISTENT_CONNECTIONS != 'true') {
        $this->system->database->disconnect();
      }
    }
    
    ######################################################################
  
    function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE) {
      
    // Create link
      if (!isset($this->links[$link]) || (!is_resource($this->links[$link]) && !is_object($this->links[$link]))) {
      
      // Set start timestamp for debug
        $execution_time_start = microtime(true);
      
      // Connect
        if ($this->type == 'mysqli') {
        
          if (DB_PERSISTENT_CONNECTIONS == 'true') {
            $this->links[$link] = mysqli_pconnect($server, $username, $password, $database) or exit;
          } else {
            $this->links[$link] = mysqli_connect($server, $username, $password, $database) or exit;
          }
          
        } else {
        
          if (DB_PERSISTENT_CONNECTIONS == 'true') {
            $this->links[$link] = mysql_pconnect($server, $username, $password) or exit;
          } else {
            $this->links[$link] = mysql_connect($server, $username, $password) or exit;
          }
          
          mysql_select_db($database) or $this->error(false, mysql_errno(), mysql_error());
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
      
    // Make sure link was established
      if (!is_resource($this->links[$link]) && !is_object($this->links[$link])) {
        trigger_error('Error: Invalid database link', E_USER_ERROR);
      }
      
    // Return connection link
      return $this->links[$link];
    }
    
  // Close database connection
    function disconnect($link='') {
      
      if ($link != '') {
        $links = array($this->links[$link]);
      } else {
        $links = $this->links;
      }
      
      $errors = false;
      foreach ($links as $link) {
        if (!is_resource($link)) {
          $errors = true;
        } else {
          if ($this->type == 'mysqli') {
            mysqli_close($link);
          } else {
            mysql_close($link);
          }
        }
      }
      
      return $errors ? true : false;
    }
    
    function query($query, $link='default') {
      
    // Establish a link if not previously made
      if (!isset($this->links[$link]) || is_resource($this->links[$link])) $this->connect($link);
      
    // For debug
      if (!isset($this->history)) $this->history = array();
      $this->history[] = $query;
      
    // Set start timestamp for debug
      $execution_time_start = microtime(true);
      
    // Perform mysql query
      if ($this->type == 'mysqli') {
        $result = mysqli_query($this->links[$link], '/*qc=on*/'. $query) or $this->error($query, mysqli_errno($this->links[$link]), mysqli_error($this->links[$link]));
      } else {
        $result = mysql_query($query, $this->links[$link]) or exit;
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
    
    function fetch($result) {
    
    // Set start timestamp for debug
      $execution_time_start = microtime(true);
      
    // Perform mysql query
      if ($this->type == 'mysqli') {
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
    
    function seek($result, $offset) {
      if ($this->type == 'mysqli') {
        return mysqli_data_seek($result, $offset);
      } else {
        return mysql_data_seek($result, $offset);
      }
    }
    
    function num_rows($result) {
      if ($this->type == 'mysqli') {
        return mysqli_num_rows($result);
      } else {
        return mysql_num_rows($result);
      }
    }

    function free($result) {
      if ($this->type == 'mysqli') {
        return mysqli_free_result($result);
      } else {
        return mysql_free_result($result);
      }
    }
    
    function insert_id($link='default') {
      if ($this->type == 'mysqli') {
        return mysqli_insert_id($this->links[$link]);
      } else {
        return mysql_insert_id($this->links[$link]);
      }
    }
    
    function affected_rows($link='default') {
      if ($this->type == 'mysqli') {
        return mysqli_affected_rows($this->links[$link]);
      } else {
        return mysql_affected_rows($this->links[$link]);
      }
    }
    
    function input($string, $allowable_tags=false, $link='default') {
    
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
      if (!isset($this->links[$link])) $this->connect();
      
    // Return safe input string
      if ($this->type == 'mysqli') {
        return mysqli_real_escape_string($this->links[$link], $string);
      } else {
        return mysql_real_escape_string($string, $this->links[$link]);
      }
    }
    
    function error($query, $errno, $error) {
    
    // Log
      error_log($errno .' - '. str_replace("\r\n", ' ', $error) ."\r\n  ". str_replace("\r\n", "\r\n  ", $query), E_USER_ERROR);
    
    // Halt script and output error
      //die('MySQL error code '. $errno .' at '. date('Y-m-d H:i:s') .'. Please consult the webmaster.');
      die($errno .' - '. str_replace("\r\n", ' ', $error) ."\r\n  ". str_replace("\r\n", "\r\n  ", $query));
    }
  }
  
?>
<?php

  include('../includes/config.inc.php');

  class database {
    private $server = DB_SERVER;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_DATABASE;
    public $table_prefix = 'whptest_';
    private $sqlLink = false;
    
    public function connect() {
      $this->sqlLink = mysql_connect($this->server, $this->username, $this->password) or $this->error(false, mysql_errno(), mysql_error());
      mysql_select_db($this->database) or $this->error(false, mysql_errno(), mysql_error());
      mysql_query("set character set utf8", $this->sqlLink) or $this->error(false, mysql_errno(), mysql_error());
    }
    
    public function disconnect() {
      mysql_close($this->sqlLink);
    }
    
    public function query($query) {
      if (!isset($this->sqlLink) || !is_resource($this->sqlLink)) $this->connect();
      $result = mysql_query($query, $this->sqlLink) or $this->error($query, mysql_errno(), mysql_error());
      return $result;
    }
    
    public function fetch($result) {
      $row = mysql_fetch_array($result);
      return $row;
    }
    
    public function seek($result, $offset) {
      return mysql_data_seek($result, $offset);
    }
    
    public function num_rows($result) {
      return mysql_num_rows($result);
    }

    public function free($result) {
      return mysql_free_result($result);
    }
    
    public function insert_id() {
      if (!isset($this->sqlLink) || !is_resource($this->sqlLink)) $this->connect();
      return mysql_insert_id($this->sqlLink);
    }
    
    public function input($string, $allowable_tags=false) {
      
      if (is_bool($allowable_tags) === true && $allowable_tags !== true) {
        $string = strip_tags($string, $allowable_tags);
      }
      
      if (function_exists('mysql_real_escape_string')) {
        if (!isset($this->sqlLink)) $this->connect();
        return mysql_real_escape_string($string, $this->sqlLink);
      } elseif (function_exists('mysql_escape_string')) {
        return mysql_escape_string($string);
      }
      
      return addslashes($string);
    }
    
    function get_server_info() {
      if (!isset($this->sqlLink) || !is_resource($this->sqlLink)) $this->connect();
      return mysql_get_server_info($this->sqlLink);
    }
    
    private function error($query, $errno, $error) {
      die('MySQL error code '. $errno .': '. $error .' Query: '. $query);
    }
  }
  
?>
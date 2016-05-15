<?php

  class database {

    private $_links = array();

    public function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE) {

    // Create link
      if (!isset($this->_links[$link]) || (!is_resource($this->_links[$link]) && !is_object($this->_links[$link]))) {

      // Connect
        if (function_exists('mysqli_connect')) {
          $this->_links[$link] = mysqli_connect($server, $username, $password, $database) or die('Could not connect to database: '. mysqli_error($this->_links[$link]));

        } else {
          $this->_links[$link] = mysql_connect($server, $username, $password) or die('Could not connect to database: '. mysql_error($this->_links[$link]));

          mysql_select_db($database) or $this->_error(false, mysql_errno(), mysql_error());
        }
      }

      $this->query("set character set utf8");
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
          if (function_exists('mysqli_close')) {
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

      //if (strtolower(substr($query, 0, 6)) == 'select') $query = '/*qc=on*/' . $query; // mysqlnd query cache plugin

    // Perform mysql query
      if (function_exists('mysqli_query')) {
        $result = mysqli_query($this->_links[$link], $query) or $this->_error($query, mysqli_errno($this->_links[$link]), mysqli_error($this->_links[$link]));
      } else {
        $result = mysql_query($query, $this->_links[$link]) or exit;
      }

    // Return query resource
      return $result;
    }

    public function fetch($result) {

    // Perform mysql query
      if (function_exists('mysqli_fetch_assoc')) {
        $array = mysqli_fetch_assoc($result);
      } else {
        $array = mysql_fetch_assoc($result);
      }

      return $array;
    }

    public function seek($result, $offset) {
      if (function_exists('mysqli_data_seek')) {
        return mysqli_data_seek($result, $offset);
      } else {
        return mysql_data_seek($result, $offset);
      }
    }

    public function num_rows($result) {
      if (function_exists('mysqli_num_rows')) {
        return mysqli_num_rows($result);
      } else {
        return mysql_num_rows($result);
      }
    }

    public function free($result) {
      if (function_exists('mysqli_free_result')) {
        return mysqli_free_result($result);
      } else {
        return mysql_free_result($result);
      }
    }

    public function insert_id($link='default') {
      if (function_exists('mysqli_insert_id')) {
        return mysqli_insert_id($this->_links[$link]);
      } else {
        return mysql_insert_id($this->_links[$link]);
      }
    }

    public function affected_rows($link='default') {
      if (function_exists('mysqli_affected_rows')) {
        return mysqli_affected_rows($this->_links[$link]);
      } else {
        return mysql_affected_rows($this->_links[$link]);
      }
    }

    public function info($link='default') {
      if (function_exists('mysqli_info')) {
        return mysqli_info($this->_links[$link]);
      } else {
        return mysql_info($this->_links[$link]);
      }
    }

    public function input($string, $allowable_tags=false, $link='default') {

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
      if (function_exists('mysqli_real_escape_string')) {
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
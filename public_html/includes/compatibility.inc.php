<?php

// Check Version
  if (version_compare(phpversion(), '5.1.0', '<') == true) {
    exit('PHP5.1+ Required');
  }

// Register Globals
  if (ini_get('register_globals')) {
    ini_set('session.use_cookies', 'On');
    ini_set('session.use_trans_sid', 'Off');
    
    session_set_cookie_params(0, '/');
    session_start();
    
    foreach (array($_REQUEST, $_SESSION, $_SERVER, $_FILES) as $global) {
      foreach(array_keys($global) as $key) {
        global $$key;
        unset($$key);
      }
    }
  }
  
// Magic Quotes Fix
  if (ini_get('magic_quotes_gpc')) {
    function clean($data) {
      if (is_array($data)) {
        foreach ($data as $key => $value) {
          $data[clean($key)] = clean($value);
        }
      } else {
        $data = stripslashes($data);
      }
    
      return $data;
    }			
    
    $_GET = clean($_GET);
    $_POST = clean($_POST);
    $_REQUEST = clean($_REQUEST);
    $_COOKIE = clean($_COOKIE);
  }
  
// Windows Paths
  $_SERVER['SCRIPT_FILENAME'] = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
  
 // array_column() as of PHP 5.5
  if (!function_exists('array_column')) {
    function array_column(array $array, $column_key, $index_key=null) {
      $result = array();
      foreach($array as $arr){
        if(!is_array($arr)) continue;
        if (is_null($column_key)) {
          $value = $arr;
        } else {
          $value = $arr[$column_key];
        }
        if (!is_null($index_key)) {
          $key = $arr[$index_key];
          $result[$key] = $value;
        } else{
          $result[] = $value;
        }
      }
      return $result;
    }
  }
  
?>
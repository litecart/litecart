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
  
  if (!function_exists('http_response_code')) {
    function http_response_code($code = null) {
      if ($code !== NULL) {
        switch ($code) {
          case 100: $text = 'Continue'; break;
          case 101: $text = 'Switching Protocols'; break;
          case 200: $text = 'OK'; break;
          case 201: $text = 'Created'; break;
          case 202: $text = 'Accepted'; break;
          case 203: $text = 'Non-Authoritative Information'; break;
          case 204: $text = 'No Content'; break;
          case 205: $text = 'Reset Content'; break;
          case 206: $text = 'Partial Content'; break;
          case 300: $text = 'Multiple Choices'; break;
          case 301: $text = 'Moved Permanently'; break;
          case 302: $text = 'Moved Temporarily'; break;
          case 303: $text = 'See Other'; break;
          case 304: $text = 'Not Modified'; break;
          case 305: $text = 'Use Proxy'; break;
          case 400: $text = 'Bad Request'; break;
          case 401: $text = 'Unauthorized'; break;
          case 402: $text = 'Payment Required'; break;
          case 403: $text = 'Forbidden'; break;
          case 404: $text = 'Not Found'; break;
          case 405: $text = 'Method Not Allowed'; break;
          case 406: $text = 'Not Acceptable'; break;
          case 407: $text = 'Proxy Authentication Required'; break;
          case 408: $text = 'Request Time-out'; break;
          case 409: $text = 'Conflict'; break;
          case 410: $text = 'Gone'; break;
          case 411: $text = 'Length Required'; break;
          case 412: $text = 'Precondition Failed'; break;
          case 413: $text = 'Request Entity Too Large'; break;
          case 414: $text = 'Request-URI Too Large'; break;
          case 415: $text = 'Unsupported Media Type'; break;
          case 500: $text = 'Internal Server Error'; break;
          case 501: $text = 'Not Implemented'; break;
          case 502: $text = 'Bad Gateway'; break;
          case 503: $text = 'Service Unavailable'; break;
          case 504: $text = 'Gateway Time-out'; break;
          case 505: $text = 'HTTP Version not supported'; break;
          default: trigger_error('Unknown http status code "' . htmlentities($code) . '"', E_USER_WARNING); break;
        }
        
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $text, true, $code);
        
      } else {
        $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
      }
      
      return $code;
      
    }
  }
  
?>
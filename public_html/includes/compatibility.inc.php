<?php

// Check Version
  if (version_compare(phpversion(), '5.3.0', '<') == true) {
    exit('PHP5.3+ Required');
  }

// (Un)register Globals (PHP <5.4)
  if (ini_get('register_globals')) {
    foreach (array_keys(array_merge($_SERVER, $_ENV, !empty($_SESSION) ? $_SESSION : array(), $_COOKIE, $_REQUEST, $_FILES)) as $key) {
      if (isset($GLOBALS[$key])) unset($GLOBALS[$key]);
    }
  }

// Fix Magic Quotes
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

// Fix Windows Paths
  $_SERVER['SCRIPT_FILENAME'] = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);

// Emulate http_response_code() as of PHP 5.4
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

// Emulate array_column() as of PHP 5.5
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

// Emulate some $_SERVER variables
  if (empty($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
  if (empty($_SERVER['HTTP_HTTPS'])) $_SERVER['HTTP_HTTPS'] = 'off';

// Redefine some $_SERVER variables
  if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
  if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) $_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_FORWARDED_PORT'];
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') $_SERVER['HTTPS'] = 'on';

/*
// Redefine $_SERVER['REMOTE_ADDR'] (Can easily be spoofed by clients - Do not enable unless necessary)
  foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_CF_CONNECTING_IP') as $key) {
    if (!empty($_SERVER[$key])) {
      foreach (explode(',', $_SERVER[$key]) as $ip) {
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
          $_SERVER['REMOTE_ADDR'] = $ip;
        }
      }
    }
  }
*/

?>
<?php

// Check version
  if (version_compare(phpversion(), '5.3.0', '<') == true) {
    die('This application requires at minimum PHP 5.3 (Detected '. phpversion() .')');
  }

  if (version_compare(phpversion(), '5.4.0', '<') == true) {

  // (Un)register Globals
    if (ini_get('register_globals')) {
      foreach (array('_ENV', '_FILES', '_REQUEST', '_SERVER') as $superglobal) { // Note: $_SESSION is not populated before start_session()
        foreach (array_keys($$superglobal) as $key) {
          unset($GLOBALS[$key]);
        }
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

          $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
          header($protocol . ' ' . $code . ' ' . $text, true, $code);

        } else {
          $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
      }
    }
  }

  if (version_compare(phpversion(), '5.5.0', '<') == true) {

  // Emulate array_column() as of PHP 5.5
    if (!function_exists('array_column')) {
      function array_column(array $array, $column_key, $index_key=null) {
        $result = array();
        foreach ($array as $arr) {
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

  // Emulate password functions as of PHP 5.5 - Copyright (c) 2012 Anthony Ferrara
    if (!defined('PASSWORD_BCRYPT')) {
      define('PASSWORD_BCRYPT', 1);
      define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
      define('PASSWORD_BCRYPT_DEFAULT_COST', 10);
    }

    if (!function_exists('password_hash')) {
      function password_hash($password, $algo, array $options = array()) {
        if (!function_exists('crypt')) {
          trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
          return null;
        }
        if (is_null($password) || is_int($password)) {
          $password = (string) $password;
        }
        if (!is_string($password)) {
          trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
          return null;
        }
        if (!is_int($algo)) {
          trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
          return null;
        }
        $resultLength = 0;
        switch ($algo) {
          case PASSWORD_BCRYPT:
            $cost = PASSWORD_BCRYPT_DEFAULT_COST;
            if (isset($options['cost'])) {
              $cost = (int) $options['cost'];
              if ($cost < 4 || $cost > 31) {
                trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
                return null;
              }
            }
            $raw_salt_len = 16;
            $required_salt_len = 22;
            $hash_format = sprintf("$2y$%02d$", $cost);
            $resultLength = 60;
            break;
          default:
            trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
            return null;
        }
        $salt_req_encoding = false;
        if (isset($options['salt'])) {
          switch (gettype($options['salt'])) {
            case 'NULL':
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
              $salt = (string) $options['salt'];
              break;
            case 'object':
              if (method_exists($options['salt'], '__tostring')) {
                $salt = (string) $options['salt'];
                break;
              }
            case 'array':
            case 'resource':
            default:
              trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
              return null;
          }
          if (mb_strlen($salt, '8bit') < $required_salt_len) {
            trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", mb_strlen($salt, '8bit'), $required_salt_len), E_USER_WARNING);
            return null;
          } elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
            $salt_req_encoding = true;
          }
        } else {
          $buffer = '';
          $buffer_valid = false;
          if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
            $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
            if ($buffer) {
              $buffer_valid = true;
            }
          }
          if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $buffer = openssl_random_pseudo_bytes($raw_salt_len, $strong);
            if ($buffer && $strong) {
              $buffer_valid = true;
            }
          }
          if (!$buffer_valid && @is_readable('/dev/urandom')) {
            $file = fopen('/dev/urandom', 'r');
            $read = 0;
            $local_buffer = '';
            while ($read < $raw_salt_len) {
              $local_buffer .= fread($file, $raw_salt_len - $read);
              $read = mb_strlen($local_buffer, '8bit');
            }
            fclose($file);
            if ($read >= $raw_salt_len) {
              $buffer_valid = true;
            }
            $buffer = str_pad($buffer, $raw_salt_len, "\0") ^ str_pad($local_buffer, $raw_salt_len, "\0");
          }
          if (!$buffer_valid || mb_strlen($buffer, '8bit') < $raw_salt_len) {
            $buffer_length = mb_strlen($buffer, '8bit');
            for ($i = 0; $i < $raw_salt_len; $i++) {
              if ($i < $buffer_length) {
                $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
              } else {
                $buffer .= chr(mt_rand(0, 255));
              }
            }
          }
          $salt = $buffer;
          $salt_req_encoding = true;
        }
        if ($salt_req_encoding) {
          $base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
          $bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
          $base64_string = base64_encode($salt);
          $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        }
        $salt = mb_substr($salt, 0, $required_salt_len, '8bit');
        $hash = $hash_format . $salt;
        $ret = crypt($password, $hash);
        if (!is_string($ret) || mb_strlen($ret, '8bit') != $resultLength) {
          return false;
        }
        return $ret;
      }
    }

    if (!function_exists('password_get_info')) {
      function password_get_info($hash) {
        $return = array(
          'algo' => 0,
          'algoName' => 'unknown',
          'options' => array(),
        );
        if (mb_substr($hash, 0, 4, '8bit') == '$2y$' && mb_strlen($hash, '8bit') == 60) {
          $return['algo'] = PASSWORD_BCRYPT;
          $return['algoName'] = 'bcrypt';
          list($cost) = sscanf($hash, "$2y$%d$");
          $return['options']['cost'] = $cost;
        }
        return $return;
      }
    }

    if (!function_exists('password_needs_rehash')) {
      function password_needs_rehash($hash, $algo, array $options = array()) {
        $info = password_get_info($hash);
        if ($info['algo'] !== (int) $algo) {
          return true;
        }
        switch ($algo) {
          case PASSWORD_BCRYPT:
            $cost = isset($options['cost']) ? (int) $options['cost'] : PASSWORD_BCRYPT_DEFAULT_COST;
            if ($cost !== $info['options']['cost']) {
              return true;
            }
            break;
        }
        return false;
      }
    }

    if (!function_exists('password_verify')) {
      function password_verify($password, $hash) {
        if (!function_exists('crypt')) {
          trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
          return false;
        }
        $ret = crypt($password, $hash);
        if (!is_string($ret) || mb_strlen($ret, '8bit') != mb_strlen($hash, '8bit') || mb_strlen($ret, '8bit') <= 13) {
          return false;
        }
        $status = 0;
        for ($i = 0; $i < mb_strlen($ret, '8bit'); $i++) {
          $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }
        return $status === 0;
      }
    }
  }

  if (version_compare(phpversion(), '7.1', '>=') == true) {

  // Fix JSON serialize float precision issue in PHP 7.1+
    ini_set('serialize_precision', -1);
  }

// Emulate getallheaders() on non-Apache machines
  if (!function_exists('getallheaders')) {
    function getallheaders() {
      $headers = array();
      foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
          $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
      }
      return $headers;
    }
  }

// Fix Windows paths
  $_SERVER['SCRIPT_FILENAME'] = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);

// Emulate some $_SERVER variables
  if (empty($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
  if (empty($_SERVER['HTTP_HTTPS'])) $_SERVER['HTTP_HTTPS'] = 'off';

// Redefine some $_SERVER variables
  if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
  if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) $_SERVER['SERVER_PORT'] = $_SERVER['HTTP_X_FORWARDED_PORT'];
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') $_SERVER['HTTPS'] = 'on';

/*
// Redefine $_SERVER['REMOTE_ADDR'] (Can easily be spoofed by clients - Do not enable unless necessary)
  foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_REAL_IP', 'HTTP_CF_CONNECTING_IP') as $key) {
    if (!empty($_SERVER[$key])) {
      foreach (array_reverse(explode(',', $_SERVER[$key])) as $ip) {
        $ip = trim($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
          $_SERVER['REMOTE_ADDR'] = $ip;
        }
      }
    }
  }
*/

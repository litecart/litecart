<?php

// Check version
  if (version_compare(phpversion(), '5.4.0', '<') == true) {
    die('This application requires at minimum PHP 5.4 (Detected '. phpversion() .')');
  }

  if (version_compare(phpversion(), '5.5.0', '<') == true) {

  // Polyfill for array_column() as of PHP 5.5
    if (!function_exists('array_column')) {
      function array_column(array $array, $column_key, $index_key=null) {
        $result = [];
        foreach ($array as $arr) {
          if (!is_array($arr)) continue;
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

  // Polyfill for password functions as of PHP 5.5 - Copyright (c) 2012 Anthony Ferrara
    if (!defined('PASSWORD_BCRYPT')) {
      define('PASSWORD_BCRYPT', 1);
      define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
      define('PASSWORD_BCRYPT_DEFAULT_COST', 10);
    }

    if (!function_exists('password_hash')) {
      function password_hash($password, $algo, array $options = []) {
        if (!function_exists('crypt')) {
          trigger_error('Crypt must be loaded for password_hash to function', E_USER_WARNING);
          return null;
        }
        if (is_null($password) || is_int($password)) {
          $password = (string) $password;
        }
        if (!is_string($password)) {
          trigger_error('password_hash(): Password must be a string', E_USER_WARNING);
          return null;
        }
        if (!is_int($algo)) {
          trigger_error('password_hash() expects parameter 2 to be long, ' . gettype($algo) . ' given', E_USER_WARNING);
          return null;
        }
        $resultLength = 0;
        switch ($algo) {
          case PASSWORD_BCRYPT:
            $cost = PASSWORD_BCRYPT_DEFAULT_COST;
            if (isset($options['cost'])) {
              $cost = (int) $options['cost'];
              if ($cost < 4 || $cost > 31) {
                trigger_error(sprintf('password_hash(): Invalid bcrypt cost parameter specified: %d', $cost), E_USER_WARNING);
                return null;
              }
            }
            $raw_salt_len = 16;
            $required_salt_len = 22;
            $hash_format = sprintf("$2y$%02d$", $cost);
            $resultLength = 60;
            break;
          default:
            trigger_error(sprintf('password_hash(): Unknown password hashing algorithm: %s', $algo), E_USER_WARNING);
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
          if (!$buffer_valid && is_readable('/dev/urandom')) {
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
        $return = [
          'algo' => 0,
          'algoName' => 'unknown',
          'options' => [],
        ];
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
      function password_needs_rehash($hash, $algo, array $options = []) {
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
          trigger_error('Crypt must be loaded for password_verify to function', E_USER_WARNING);
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

// Polyfill for getallheaders() on non-Apache machines
  if (!function_exists('getallheaders')) {
    function getallheaders() {
      $headers = [];
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

// Polyfill for some $_SERVER variables in CLI
  if (php_sapi_name() === 'cli') {
    $_SERVER['DOCUMENT_ROOT'] = rtrim(FS_DIR_APP, '/');
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'https';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
  }

  if (empty($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = 'off';
  if (empty($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];

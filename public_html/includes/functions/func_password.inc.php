<?php

  class func_password {
    
    public static function password_generate($length=6) {
      $password = '';

      $possible = '!#$%@2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ';

      $maxlength = strlen($possible);
    
      if ($length > $maxlength) {
        $length = $maxlength;
      }
    
      $i = 0; 
      while ($i < $length) { 

        $char = substr($possible, mt_rand(0, $maxlength-1), 1);
          
        if (!strstr($password, $char)) { 
          $password .= $char;
          $i++;
        }
      }
      
      return $password;
    }
    
    if (!function_exists('password_hash')) {
      public static function password_hash($login, $password) {
        trigger_error('password_hash() is deprecated due to name collision in PHP 5.5. Use password_checksum() instead.', E_USER_DEPRECATED);
        return password_checksum($login, $password);
      }
    }
    
    public static function password_checksum($login, $password) {
      if (!defined('PASSWORD_SALT')) trigger_error('There is no password salt defined.', E_USER_ERROR);
      if (strlen($password) < 2) {
        return hash('sha256', $login . $password . PASSWORD_SALT);
      } else {
        $password = @str_split($password, ceil(strlen($password)/2));
        return hash('sha256', $login . $password[0] . PASSWORD_SALT . $password[1]);
      }
    }
  }

?>
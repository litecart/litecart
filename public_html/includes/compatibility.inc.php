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
  
?>
<?php

// Start redirecting output to the output buffer
  ob_start();
  
// Get config
  require_once(realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php');
  
// Autoloader
  function __autoload($name) {
    switch($name) {
      case (substr($name, 0, 4) == 'ctrl'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . $name . '.inc.php';
        break;
      case (substr($name, 0, 2) == 'ga'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'get_address/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'job'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 2) == 'ot'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 2) == 'os'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_success/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 2) == 'pm'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'ref'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . $name . '.inc.php';
        break;
      case (substr($name, 0, 2) == 'sm'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/' . $name . '.inc.php';
        break;
      default:
        require_once FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . $name . '.inc.php';
        break;
    }
  }
  
// Get compatibility
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'compatibility.inc.php');
  
// Set up the system object 
  $system = new system();
  
// Load dependencies
  $system->run('load_dependencies');
  
// Initiate system modules
  $system->run('initiate');
  
// Run start operations
  $system->run('startup');
  
// Run operations before capture
  $system->run('before_capture');
  
?>
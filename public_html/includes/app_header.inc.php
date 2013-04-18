<?php

// Start redirecting output to the output buffer
  ob_start();
  
// Get config
  if (!file_exists(realpath(dirname(__FILE__)) . '/config.inc.php')) {
    header('Location: ./install/');
    exit;
  }
  require_once(realpath(dirname(__FILE__)) . '/config.inc.php');
  
// Autoloader
  function __autoload($name) {
    switch($name) {
      case (substr($name, 0, 5) == 'ctrl_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'ga_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'get_address/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 4) == 'job_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'ot_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'os_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_success/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'pm_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 4) == 'ref_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . $name . '.inc.php';
        break;
      case (substr($name, 0, 3) == 'sm_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/' . $name . '.inc.php';
        break;
      case (substr($name, 0, 4) == 'url_'):
        require_once FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'seo_links/' . $name . '.inc.php';
        break;
      default:
        require_once FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . $name . '.inc.php';
        break;
    }
  }
  
// Set error handler
  function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    if (!(error_reporting() & $errno)) return;
    $errfile = preg_replace('#^'. FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'#', '~/', str_replace('\\', '/', $errfile));
    
    $trace = array_reverse(debug_backtrace());
    array_pop($trace);
    
    $traces = '';
    
    /*
    if (!empty($trace)) {
      $traces = array();
      foreach ($trace as $item) {
        $item['file'] = preg_replace('#^'. FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'#', '~/', str_replace('\\', '/', $item['file']));
        $traces[] = "<b>{$item['file']}</b> on line <b>{$item['line']}</b> in <b>{$item['function']}()</b>";
      }
      $traces = 'through ' . implode(' -> ', $traces);
    }
    */
    
    switch($errno) {
      case E_WARNING:
      case E_USER_WARNING:
        $output = "<b>Warning:</b> $errstr in <b>$errfile</b> on line <b>$errline</b> $traces";
        break;
      case E_STRICT:
      case E_NOTICE:
      case E_USER_NOTICE:
        $output = "<b>Notice:</b> $errstr in <b>$errfile</b> on line <b>$errline</b> $traces";
        break;
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $output = "<b>Deprecated:</b> $errstr in <b>$errfile</b> on line <b>$errline</b> $traces";
        break;
      default:
        $output = "<b>Fatal error:</b> $errstr in <b>$errfile</b> on line <b>$errline</b> $traces";
        $fatal = true;
        break;
    }
    
    if (!in_array(strtolower(ini_get('display_errors')), array('on', 'true', '1'))) {
      $output .= " $traces {$_SERVER['REQUEST_URI']}";
    }
    
    if (in_array(strtolower(ini_get('html_errors')), array(0, 'off', 'false')) || PHP_SAPI == 'cli') {
      echo strip_tags($output) . PHP_EOL;
    } else {
      echo $output . '<br />' . PHP_EOL;
    }
    
    if (ini_get('log_errors')) {
      error_log(strip_tags($output));
    }
    
    if (in_array($errno, array(E_ERROR, E_USER_ERROR))) exit;
  }
  
  set_error_handler('error_handler');
  
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
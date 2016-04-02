<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '1.3.5');
  
  if (!file_exists(realpath(dirname(__FILE__)) . '/config.inc.php')) {
    header('Location: ./install/');
    exit;
  }
  
// Start redirecting output to the output buffer
  ob_start();
  
// Get config
  require_once realpath(dirname(__FILE__)) . '/config.inc.php';
  
// Compatibility
  require_once FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'compatibility.inc.php';
  
// Virtual Modifications System
  require_once FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'vmod.inc.php';
  
// Autoloader
  spl_autoload_register(function ($class) {
    switch($class) {
      case (substr($class, 0, 5) == 'ctrl_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_CONTROLLERS . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'cm_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'customer/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 5) == 'func_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_FUNCTIONS . $class . '.inc.php');
        break;
      case (substr($class, 0, 4) == 'job_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 4) == 'mod_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'oa_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_action/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'ot_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'os_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_success/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'pm_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 4) == 'ref_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'sm_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 4) == 'url_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ROUTES . $class . '.inc.php');
        break;
      default:
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . $class . '.inc.php');
        break;
    }
  });
  
// Set error handler
  function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    if (!(error_reporting() & $errno)) return;
    $errfile = preg_replace('#^'. FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'#', '~/', str_replace('\\', '/', $errfile));
    
    $href_link = document::href_link('http://lmgtfy.com/', array('q' => $errstr));

    switch($errno) {
      case E_WARNING:
      case E_USER_WARNING:
        $output = "<b>Warning:</b> <a href=\"$href_link\" target=\"_blank\">$errstr</a> in <b>$errfile</b> on line <b>$errline</b>";
        break;
      case E_STRICT:
      case E_NOTICE:
      case E_USER_NOTICE:
        $output = "<b>Notice:</b> <a href=\"$href_link\" target=\"_blank\">$errstr</a> in <b>$errfile</b> on line <b>$errline</b>";
        break;
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $output = "<b>Deprecated:</b> <a href=\"$href_link\" target=\"_blank\">$errstr</a> in <b>$errfile</b> on line <b>$errline</b>";
        break;
      default:
        $output = "<b>Fatal error:</b> <a href=\"$href_link\" target=\"_blank\">$errstr</a> in <b>$errfile</b> on line <b>$errline</b>";
        break;
    }
    
    if (isset($_GET['debug'])) {
      $backtraces = debug_backtrace();
      $backtraces = array_slice($backtraces, 2);
      
      if (!empty($backtraces)) {
        foreach ($backtraces as $backtrace) {
          if (empty($backtrace['file'])) continue;
          $backtrace['file'] = preg_replace('#^'. FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'#', '~/', str_replace('\\', '/', $backtrace['file']));
          $output .= "<br />" . PHP_EOL . "  <- <b>{$backtrace['file']}</b> on line <b>{$backtrace['line']}</b> in <b>{$backtrace['function']}()</b>";
        }
      }
    }
    
    if (in_array(strtolower(ini_get('display_errors')), array('1', 'on', 'true'))) {
      if (in_array(strtolower(ini_get('html_errors')), array(0, 'off', 'false')) || PHP_SAPI == 'cli') {
        echo strip_tags($output) . PHP_EOL;
      } else {
        echo $output . '<br />' . PHP_EOL;
      }
    } else {
      if (!empty($_SERVER['REQUEST_URI'])) $output .= " {$_SERVER['REQUEST_URI']}";
    }
    
    if (ini_get('log_errors')) {
      error_log(strip_tags($output));
    }
    
    if (in_array($errno, array(E_ERROR, E_USER_ERROR))) exit;
  }
  
  set_error_handler('error_handler');
  
// Set up the system object 
  system::init();
  
// Load dependencies
  system::run('load_dependencies');
  
// Initiate system modules
  system::run('initiate');
  
// Run start operations
  system::run('startup');
  
// Run operations before capture
  system::run('before_capture');  
?>
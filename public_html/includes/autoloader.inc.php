<?php
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
      case (substr($class, 0, 3) == 'om_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order/' . $class . '.inc.php');
        break;
      case (substr($class, 0, 3) == 'ot_'):
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/' . $class . '.inc.php');
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
  }, false, true);
?>
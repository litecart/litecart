<?php
  spl_autoload_register(function($class) {

    switch($class) {

      case (substr($class, 0, 4) == 'abs_'):
        require vmod::check(FS_DIR_APP . 'includes/abstracts/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'cm_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/customer/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'ent_'):
        require vmod::check(FS_DIR_APP . 'includes/entities/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'job_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/jobs/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'mod_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'om_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/order/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'ot_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/order_total/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'pm_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/payment/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'ref_'):
        require vmod::check(FS_DIR_APP . 'includes/references/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'sm_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/shipping/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'url_'):
        if (is_file($file = vmod::check(FS_DIR_APP . 'backend/routes/' . $class . '.inc.php'))) require $file;
        if (is_file($file = vmod::check(FS_DIR_APP . 'frontend/routes/' . $class . '.inc.php'))) require $file;
        break;

      case (substr($class, 0, 5) == 'wrap_'):
        require vmod::check(FS_DIR_APP . 'includes/wrappers/' . $class . '.inc.php');
        break;

      default:
        if (is_file(FS_DIR_APP . 'includes/classes/' . $class . '.inc.php')) {
          require_once vmod::check(FS_DIR_APP . 'includes/classes/' . $class . '.inc.php');
          break;
        }

        require_once vmod::check(FS_DIR_APP . 'includes/library/lib_' . $class . '.inc.php');
        if (method_exists($class, 'init')) {
          call_user_func([$class, 'init']); // As static classes do not have a __construct() (PHP #62860)
        }
        break;
    }

  }, false, true);

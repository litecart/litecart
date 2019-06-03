<?php
  spl_autoload_register(function($class) {

    switch($class) {
      case (substr($class, 0, 5) == 'ctrl_'): // For backwards compatibility

        $new_class = preg_replace('#^ctrl_#', 'ent_', $class);

        if (is_file($file = FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'controllers/' . $class . '.inc.php')) {
          //trigger_error("Controllers ($class) are deprecated in favour of Entities ($new_class)", E_USER_DEPRECATED);
          require_once vmod::check($file);
          if (!class_exists($new_class, false)) {
            class_alias($class, $new_class);
          }
        }

        else if (is_file($file = FS_DIR_HTTP_ROOT . WS_DIR_ENTITIES . $new_class . '.inc.php')) {
          //trigger_error("Class $class is deprecated. Use instead $new_class", E_USER_DEPRECATED);
          require_once vmod::check($file);
          if (!class_exists($new_class, false)) {
            class_alias($new_class, $class);
          }
        }

        break;

      case (substr($class, 0, 3) == 'cm_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'customer/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'ent_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ENTITIES . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'job_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'jobs/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'mod_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'om_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'ot_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'order_total/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'pm_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'payment/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'ref_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_REFERENCES . $class . '.inc.php');
        break;

      case (substr($class, 0, 3) == 'sm_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_MODULES . 'shipping/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'url_'):
        require vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_ROUTES . $class . '.inc.php');
        break;

      case ($class == 'email'):
        //trigger_error('Class object email() is deprecated. Use instead ent_email()', E_USER_DEPRECATED);
        class_alias('ent_email', 'email', true);
        break;

      default:
        require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . $class . '.inc.php');
        break;
    }

  }, false, true);

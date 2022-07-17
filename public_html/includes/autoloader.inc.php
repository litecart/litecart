<?php

  spl_autoload_register(function($class) {

    switch($class) {

      case (substr($class, 0, 4) == 'abs_'):
        require 'app://includes/abstracts/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 3) == 'cm_'):
        require 'app://includes/modules/customer/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 4) == 'ent_'):
        require 'app://includes/entities/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 4) == 'job_'):
        require 'app://includes/modules/jobs/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 4) == 'mod_'):
        require 'app://includes/modules/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 3) == 'om_'):
        require 'app://includes/modules/order/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 3) == 'ot_'):
        require 'app://includes/modules/order_total/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 3) == 'pm_'):
        require 'app://includes/modules/payment/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 4) == 'ref_'):
        require 'app://includes/references/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 3) == 'sm_'):
        require 'app://includes/modules/shipping/' . $class . '.inc.php';
        break;

      case (substr($class, 0, 4) == 'url_'):
        if (is_file($file = 'app://backend/routes/' . $class . '.inc.php')) require $file;
        if (is_file($file = 'app://frontend/routes/' . $class . '.inc.php')) require $file;
        break;

      case (substr($class, 0, 5) == 'wrap_'):
        require 'app://includes/wrappers/' . $class . '.inc.php';
        break;

      case (substr($class, -7) == '_client'):
        require 'app://includes/clients/' . $class . '.inc.php';
        break;

      default:

        require 'app://includes/nodes/nod_' . $class . '.inc.php';

        if (method_exists($class, 'init')) {
          call_user_func([$class, 'init']); // As static classes do not have a __construct() (PHP #62860)
        }

        break;
    }
  }, true, false);

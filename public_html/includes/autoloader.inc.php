<?php

  spl_autoload_register(function($class) {

    switch (true) {

			case (preg_match('#^abs_#', $class)):

        require 'app://includes/abstracts/' . $class . '.inc.php';
        break;

			case (preg_match('#^chk_#', $class)):

        require 'app://includes/modules/checkout/' . $class . '.inc.php';
        break;

			case (preg_match('#_client$#', $class)):

        require 'app://includes/clients/' . $class . '.inc.php';
        break;

			case (preg_match('#^cm_#', $class)):
			case (preg_match('#^job_#', $class)):
			case (preg_match('#^om_#', $class)):
			case (preg_match('#^ot_#', $class)):
			case (preg_match('#^pm_#', $class)):
			case (preg_match('#^sm_#', $class)):

      // Patch modules for PHP 8.2 Compatibility
        if (version_compare(PHP_VERSION, 8.2, '>=')) {

          $search_replace = [
            '#^(cm_.*)#' => FS_DIR_APP . 'includes/modules/customer/$1.inc.php',
            '#^(job_.*)#' => FS_DIR_APP . 'includes/modules/jobs/$1.inc.php',
            '#^(om_.*)#' => FS_DIR_APP . 'includes/modules/order/$1.inc.php',
            '#^(ot_.*)#' => FS_DIR_APP . 'includes/modules/order_total/$1.inc.php',
            '#^(pm_.*)#' => FS_DIR_APP . 'includes/modules/payment/$1.inc.php',
            '#^(sm_.*)#' => FS_DIR_APP . 'includes/modules/shipping/$1.inc.php',
          ];

          $file = preg_replace(array_keys($search_replace), array_values($search_replace), $class);

          if (is_file($file)) {
            $source = file_get_contents($file);
            if (!preg_match('#\#\[AllowDynamicProperties\]#', $source)) {
              $source = preg_replace('#([ \t]*)class [a-zA-Z0-9_-]+ extends abs_module *\{(\n|\r\n?)#', '$1#[AllowDynamicProperties]$2$0', $source);
              file_put_contents($file, $source);
            }
          }
        }

        switch (true) {
					case (preg_match('#^cm_#', $class)):
            require 'app://includes/modules/customer/' . $class . '.inc.php';
            break;

					case (preg_match('#^job_#', $class)):
            require 'app://includes/modules/jobs/' . $class . '.inc.php';
            break;

					case (preg_match('#^om_#', $class)):
            require 'app://includes/modules/order/' . $class . '.inc.php';
            break;

					case (preg_match('#^ot_#', $class)):
            require 'app://includes/modules/order_total/' . $class . '.inc.php';
            break;

					case (preg_match('#^pm_#', $class)):
            require 'app://includes/modules/payment/' . $class . '.inc.php';
            break;

					case (preg_match('#^sm_#', $class)):
            require 'app://includes/modules/shipping/' . $class . '.inc.php';
            break;
        }

        break;

			case (preg_match('#^ent_#', $class)):

        require 'app://includes/entities/' . $class . '.inc.php';
        break;

			case (preg_match('#^mod_#', $class)):

        require 'app://includes/modules/' . $class . '.inc.php';
        break;

			case (preg_match('#^ref_#', $class)):

        require 'app://includes/references/' . $class . '.inc.php';
        break;

			case (preg_match('#^sm_#', $class)):
        require 'app://includes/modules/shipping/' . $class . '.inc.php';
        break;

			case (preg_match('#^url_#', $class)):

        if (is_file($file = 'app://backend/routes/' . $class . '.inc.php')) require $file;
        if (is_file($file = 'app://frontend/routes/' . $class . '.inc.php')) require $file;
        break;

			case (preg_match('#^stream_#', $class)):
        require 'app://includes/streams/' . $class . '.inc.php';
        break;

      default:

				if (is_file($file = 'app://includes/nodes/nod_' . $class . '.inc.php')) {
					require $file;

					if (method_exists($class, 'init')) {
						call_user_func([$class, 'init']); // As static classes do not have a __construct() (PHP #62860)
					}
				}

        break;
    }
  }, true, false);

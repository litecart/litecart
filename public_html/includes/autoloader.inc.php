<?php
  spl_autoload_register(function($class) {

    switch (true) {

      case (substr($class, 0, 4) == 'abs_'):
        require vmod::check(FS_DIR_APP . 'includes/abstracts/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 5) == 'ctrl_'): // For backwards compatibility

        $new_class = preg_replace('#^ctrl_#', 'ent_', $class);

        if (is_file($file = FS_DIR_APP . 'includes/controllers/' . $class . '.inc.php')) {
          trigger_error("Controllers ($class) are deprecated in favour of Entities ($new_class)", E_USER_DEPRECATED);
          require vmod::check($file);
          if (!class_exists($new_class, false)) {
            class_alias($class, $new_class);
          }
        }

        else if (is_file($file = FS_DIR_APP . 'includes/entities/' . $new_class . '.inc.php')) {
          trigger_error("Class $class is deprecated. Use $new_class", E_USER_DEPRECATED);
          require vmod::check($file);
          if (!class_exists($class, false)) {
            class_alias($new_class, $class);
          }
        }

        break;

      case (preg_match('#^(cm|job|om|ot|pm|sm)_#', $class)):

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
              $source = preg_replace('#([ \t]*)class [a-zA-Z0-9_-]+ *\{(\n|\r\n?)#', '$1#[AllowDynamicProperties]$2$0', $source);
              file_put_contents($file, $source);
            }
          }
        }

        switch ($class) {
          case (substr($class, 0, 3) == 'cm_'):
            require vmod::check(FS_DIR_APP . 'includes/modules/customer/' . $class . '.inc.php');
            break 2;

          case (substr($class, 0, 4) == 'job_'):
            require vmod::check(FS_DIR_APP . 'includes/modules/jobs/' . $class . '.inc.php');
            break 2;

          case (substr($class, 0, 3) == 'om_'):
            require vmod::check(FS_DIR_APP . 'includes/modules/order/' . $class . '.inc.php');
            break;

          case (substr($class, 0, 3) == 'ot_'):
            require vmod::check(FS_DIR_APP . 'includes/modules/order_total/' . $class . '.inc.php');
            break;

          case (substr($class, 0, 3) == 'pm_'):
            require vmod::check(FS_DIR_APP . 'includes/modules/payment/' . $class . '.inc.php');
            break;

          case (substr($class, 0, 3) == 'sm_'):
            require vmod::check(FS_DIR_APP . 'includes/modules/shipping/' . $class . '.inc.php');
            break;
        }

        break;

      case (substr($class, 0, 4) == 'ent_'):
        require vmod::check(FS_DIR_APP . 'includes/entities/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'mod_'):
        require vmod::check(FS_DIR_APP . 'includes/modules/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'ref_'):
        require vmod::check(FS_DIR_APP . 'includes/references/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 4) == 'url_'):
        require vmod::check(FS_DIR_APP . 'includes/routes/' . $class . '.inc.php');
        break;

      case (substr($class, 0, 5) == 'wrap_'):
        require vmod::check(FS_DIR_APP . 'includes/wrappers/' . $class . '.inc.php');
        break;

      case ($class == 'email'):
        trigger_error('Class object email() is deprecated. Use ent_email()', E_USER_DEPRECATED);
        class_alias('ent_email', 'email', true);
        break;

      case ($class == 'http_client'):
        trigger_error('Class object http_client() is deprecated. Use wrap_http()', E_USER_DEPRECATED);
        class_alias('wrap_http', 'http_client', true);
        break;

      case ($class == 'smtp'):
        trigger_error('Class object smtp() is deprecated. Use wrap_smtp()', E_USER_DEPRECATED);
        class_alias('wrap_smtp', 'smtp', true);
        break;

      case ($class == 'view'):
        trigger_error('Class object view() is deprecated. Use ent_view()', E_USER_DEPRECATED);
        class_alias('ent_view', 'view', true);
        break;

      default:

        if (is_file(vmod::check(FS_DIR_APP . 'includes/classes/' . $class . '.inc.php'))) {
          require vmod::check(FS_DIR_APP . 'includes/classes/' . $class . '.inc.php');
        }

        if (is_file(vmod::check(FS_DIR_APP . 'includes/library/lib_' . $class . '.inc.php'))) {
          require vmod::check(FS_DIR_APP . 'includes/library/lib_' . $class . '.inc.php');
        }

        if (method_exists($class, 'init')) {
          call_user_func([$class, 'init']); // As static classes do not have a __construct() (PHP #62860)
        }

        break;
    }

  }, true, true);

<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '2.3');

// Capture output buffer (use compression)
  ob_start('ob_gzhandler');

// Get config
  if (!defined('FS_DIR_APP')) {
    if (!file_exists(__DIR__ . '/../storage/config.inc.php')) {
      header('Location: ./install/');
      exit;
    }
    require_once __DIR__ . '/../storage/config.inc.php';
  }

// Virtual Modification System
  require_once FS_DIR_APP . 'includes/nodes/nod_vmod.inc.php';
  vmod::init();

// Compatibility and Polyfills
  require_once vmod::check(FS_DIR_APP . 'includes/compatibility.inc.php');

// Autoloader
  require_once vmod::check(FS_DIR_APP . 'includes/autoloader.inc.php');

// 3rd party autoloader (If present)
  if (is_file(FS_DIR_APP . 'vendor/autoload.php')) {
    require_once FS_DIR_APP . 'vendor/autoload.php';
  }

// Set error handler
  require_once vmod::check(FS_DIR_APP . 'includes/error_handler.inc.php');

// Jump-start some library modules
  class_exists('notices');
  class_exists('stats');

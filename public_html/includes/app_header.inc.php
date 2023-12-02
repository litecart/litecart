<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '2.5.5');
  define('SCRIPT_TIMESTAMP_START', microtime(true));

// Start redirecting output to the output buffer
  ob_start();

// Get config
  if (!defined('FS_DIR_APP')) {
    if (!file_exists(__DIR__ . '/config.inc.php')) {
      header('Location: ./install/');
      exit;
    }
    require_once __DIR__ . '/config.inc.php';
  }

// Virtual Modifications System
  require_once __DIR__ . '/library/lib_vmod.inc.php';
  vmod::init(); // Requires hard initialization as autoloader comes later

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

// Run operations before capture
  event::fire('before_capture');

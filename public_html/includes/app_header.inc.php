<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '2.2.0');

  if (!file_exists(__DIR__ . '/config.inc.php')) {
    header('Location: ./install/');
    exit;
  }

// Start redirecting output to the output buffer
  ob_start();

// Get config
  require_once __DIR__ . '/config.inc.php';

// Compatibility and Polyfills
  require_once FS_DIR_APP . 'includes/compatibility.inc.php';

// Virtual Modifications System
  require_once FS_DIR_APP . 'includes/classes/vmod.inc.php';

// Autoloader
  require_once vmod::check(FS_DIR_APP . 'includes/autoloader.inc.php');
  if (is_file(FS_DIR_APP . 'vendor/autoload.php')) {
    require_once FS_DIR_APP . 'vendor/autoload.php';
  }

// 3rd party autoloader (If present)
  if (is_file(FS_DIR_APP . 'vendor/autoload.php')) {
    require_once FS_DIR_APP . 'vendor/autoload.php';
  }

// Set error handler
  require_once vmod::check(FS_DIR_APP . 'includes/error_handler.inc.php');

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

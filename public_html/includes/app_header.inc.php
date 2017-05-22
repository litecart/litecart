<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '2.0.2');

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
  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'autoloader.inc.php');

// Set error handler
  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'error_handler.inc.php');

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

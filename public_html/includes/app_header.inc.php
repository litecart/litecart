<?php

// Start redirecting output to the output buffer
  ob_start();
  
// Get config
  require_once(realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config.inc.php');

// Get compatibility
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'compatibility.inc.php');

// Set up the system object 
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_CLASSES . 'system.inc.php');
  $system = new system();
  
// Load dependencies
  $system->run('load_dependencies');
  
// Initiate system modules
  $system->run('initiate');
  
// Run start operations
  $system->run('startup');

// Run operations before capture
  $system->run('before_capture');
  
?>
<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '2.6.4');
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
  class_exists('document');
  class_exists('notices');
  class_exists('stats');

// CSRF protection for state-changing requests
  if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS']) && session_status() === PHP_SESSION_ACTIVE) {

  // Excluded paths (payment gateway callbacks)
    $csrf_excluded_paths = ['order_process'];
    $csrf_skip = false;
    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    foreach ($csrf_excluded_paths as $path) {
      if (preg_match('#/' . preg_quote($path, '#') . '(?:/|$)#', $request_path)) {
        $csrf_skip = true;
        break;
      }
    }

    if (!$csrf_skip) {
      $submitted_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
      if (!hash_equals(session::csrf_token(), $submitted_token)) {
        http_response_code(403);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
          header('Content-Type: application/json');
          echo json_encode(['error' => 'CSRF token mismatch. Please reload the page and try again.']);
        } else {
          echo '<h1>403 Forbidden</h1><p>CSRF token mismatch. Please <a href="javascript:history.back()">go back</a> and try again.</p>';
        }
        exit;
      }
    }
  }

// Run operations before capture
  event::fire('before_capture');

  stats::$data['before_content'] = microtime(true) - SCRIPT_TIMESTAMP_START;

  stats::start_watch('content_capture');

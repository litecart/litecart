<?php
  function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {

    if (!(error_reporting() & $errno)) return;
    $errfile = preg_replace('#^'. FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'#', '~/', str_replace('\\', '/', $errfile));

    switch($errno) {
      case E_WARNING:
      case E_USER_WARNING:
        $output = "<strong>Warning:</strong> $errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br />" . PHP_EOL;
        break;
      case E_STRICT:
      case E_NOTICE:
      case E_USER_NOTICE:
        $output = "<strong>Notice:</strong> $errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br />" . PHP_EOL;
        break;
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $output = "<strong>Deprecated:</strong> $errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br />" . PHP_EOL;
        break;
      default:
        $output = "<strong>Fatal error:</strong> $errstr in <strong>$errfile</strong> on line <strong>$errline</strong><br />" . PHP_EOL;
        break;
    }

    $backtrace_output = '';
    $backtraces = array_slice(debug_backtrace(), 2);

    if (!empty($backtraces)) {
      foreach ($backtraces as $backtrace) {
        if (empty($backtrace['file'])) continue;
        $backtrace['file'] = preg_replace('#^'. FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .'#', '~/', str_replace('\\', '/', $backtrace['file']));
        $backtrace_output .= " ← <strong>{$backtrace['file']}</strong> on line <strong>{$backtrace['line']}</strong> in <strong>{$backtrace['function']}()</strong><br />" . PHP_EOL;
      }
    }

    if (in_array(strtolower(ini_get('display_errors')), array('1', 'on', 'true'))) {
      if (in_array(strtolower(ini_get('html_errors')), array(0, 'off', 'false')) || PHP_SAPI == 'cli') {
        echo strip_tags($output . (isset($_GET['debug']) ? $backtrace_output : ''));
      } else {
        echo $output . (isset($_GET['debug']) ? $backtrace_output : '');
      }
    }

    if (ini_get('log_errors')) {
      error_log(
        strip_tags($output . $backtrace_output) .
        "Request: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}" . PHP_EOL .
        "Client: {$_SERVER['REMOTE_ADDR']} (". gethostbyaddr($_SERVER['REMOTE_ADDR']) .")" . PHP_EOL .
        "User Agent: {$_SERVER['HTTP_USER_AGENT']}" . PHP_EOL
      );
    }

    if (in_array($errno, array(E_ERROR, E_USER_ERROR))) exit;
  }

  set_error_handler('error_handler');

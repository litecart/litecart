<?php

  function error_handler($errno, $errstr, $errfile, $errline) {

    if (!(error_reporting() & $errno)) return;

    $errfile = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '~/', str_replace('\\', '/', $errfile));

    switch($errno) {

      case E_STRICT:
        $output = "<strong>Strict:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong><br>" . PHP_EOL;
        break;

      case E_NOTICE:
      case E_USER_NOTICE:
        $output = "<strong>Notice:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong><br>" . PHP_EOL;
        break;

      case E_WARNING:
      case E_USER_WARNING:
      case E_COMPILE_WARNING:
      case E_RECOVERABLE_ERROR:
        $output = "<strong>Warning:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong><br>" . PHP_EOL;
        break;

      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $output = "<strong>Deprecated:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong><br>" . PHP_EOL;
        break;

      case E_PARSE:
      case E_ERROR:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
      case E_USER_ERROR:
        $output = "<strong>Fatal error:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong><br>" . PHP_EOL;
        break;

      default:
        $output = "<strong>Fatal error:</strong> ". htmlspecialchars($errstr) ." in <strong>$errfile</strong> on line <strong>$errline</strong><br>" . PHP_EOL;
        break;
    }

    $backtrace_output = '';
    $backtraces = array_slice(debug_backtrace(), 2);

    if (!empty($backtraces)) {
      foreach ($backtraces as $backtrace) {
        if (empty($backtrace['file'])) continue;
        $backtrace['file'] = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '~/', str_replace('\\', '/', $backtrace['file']));
        $backtrace_output .= " ← <strong>{$backtrace['file']}</strong> on line <strong>{$backtrace['line']}</strong> in <strong>{$backtrace['function']}()</strong><br>" . PHP_EOL;
      }
    }

    if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
      if (!filter_var(ini_get('html_errors'), FILTER_VALIDATE_BOOLEAN) || $_SERVER['SERVER_SOFTWARE'] == 'CLI') {
        $stripped_output = strip_tags($output . (isset($_GET['debug']) ? $backtrace_output : ''));
        $stripped_output = html_entity_decode($stripped_output);
        echo $stripped_output;
      } else {
        echo $output . (isset($_GET['debug']) ? $backtrace_output : '');
      }
    }

    if (filter_var(ini_get('log_errors'), FILTER_VALIDATE_BOOLEAN)) {
      error_log(
        strip_tags($output . $backtrace_output) .
        "Request: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}" . PHP_EOL .
        "Host: {$_SERVER['HTTP_HOST']}" . PHP_EOL .
        "Client: {$_SERVER['REMOTE_ADDR']} (". gethostbyaddr($_SERVER['REMOTE_ADDR']) .")" . PHP_EOL .
        "User Agent: {$_SERVER['HTTP_USER_AGENT']}" . PHP_EOL .
        (!empty($_SERVER['HTTP_REFERER']) ? "Referer: {$_SERVER['HTTP_REFERER']}" . PHP_EOL : '')
      );
    }

    if (in_array($errno, [E_PARSE, E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR])) {
      http_response_code(500);
      exit;
    }
  }

  set_error_handler('error_handler');

// Pass fatal errors to error handler
  function exception_handler($e) {
    error_handler(E_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
  }

  set_exception_handler('exception_handler');

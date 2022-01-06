<?php

  define('SQL_DATE', 'Y-m-d');
  define('SQL_DATETIME', 'Y-m-d H:i:s');

// Returns value for variable or falls back to a substituting value on empty(). Similar to $var ?? $fallback
  function fallback(mixed &$var, mixed $fallback=null):mixed {
    if (!empty($var)) return $var;
    return $fallback;
  }

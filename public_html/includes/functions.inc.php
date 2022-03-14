<?php

  define('SQL_DATE', 'Y-m-d');
  define('SQL_DATETIME', 'Y-m-d H:i:s');

// Returns value for variable or falls back to a substituting value on nil(). Similar to $var ?? $fallback
  function fallback(&$var, $fallback=null) {
    if (!nil($var)) return $var;
    return $fallback;
  }

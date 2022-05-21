<?php

  define('SQL_DATE', 'Y-m-d');
  define('SQL_DATETIME', 'Y-m-d H:i:s');

// Checks if variable is not set, null, (bool)false, (int)0, (float)0.00, (string)"", (string)"0", (string)"0.00", or (array)[].
  function nil(&$var) {
    if (is_array($var)) {
      foreach ($var as $node) {
        if (!nil($node)) return !1;
      }
    }
    return (empty($var) || (is_numeric($var) && (float)$var == 0));
  }

// Returns value for variable or falls back to a substituting value on nil(). Similar to $var ?? $fallback
  function fallback(&$var, $fallback=null) {
    if (!nil($var)) return $var;
    return $fallback;
  }

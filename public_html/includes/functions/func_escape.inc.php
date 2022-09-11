<?php

  function escape_html($string) {
    return htmlspecialchars((string)$string);
  }

  function escape_js($string) {
    return addcslashes($string, "\\\"\'\r\n");
  }

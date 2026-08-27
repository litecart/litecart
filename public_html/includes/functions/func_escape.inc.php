<?php

	function escape_attr($string) {
		return addcslashes(escape_html($string), "\r\n");
	}

  function escape_html($string) {
    return htmlspecialchars((string)$string);
  }

  function escape_js($string) {
    return addcslashes($string, "\\\"\'\r\n");
  }

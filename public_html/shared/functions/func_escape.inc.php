<?php

	function escape_attr(string|null $string=''): string {
		return addcslashes(escape_html($string), "\r\n");
	}

	function escape_html(string|null $string=''): string {
		return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
	}

	function escape_js(string|null $string=''): string {
		return addcslashes((string)$string, "\"'`\r\n\\");
	}

	function escape_mysql(string|null $string=''): string {
		return preg_replace('#[\x00\x0A\x0D\x1A\x22\x27\x5C]#u', '\\\$0', (string)$string);
	}

	function escape_mysql_like(string|null $string=''): string {
		return preg_replace('#[\x00\x0A\x0D\x1A\x22\x27\x3F\x5C\x5F]#u', '\\\$0', (string)$string);
	}

	function escape_mysql_fulltext(string|null $string=''): string {
		return preg_replace('#[\x00\x0A\x0D\x1A\x22\x27\x3F\x5C\x5F]#u', '\\\$0', (string)$string);
	}

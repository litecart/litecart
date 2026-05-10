<?php

	/*
		Safe generation of storage/config.inc.php from the shipped template.

		The legacy strtr() approach embedded user-supplied strings directly
		into PHP string literals, allowing breakout via embedded quotes or
		backslashes (e.g. client_ip="x']) || system($_GET['cmd']) || ['").

		This helper replaces each placeholder by first running its value
		through var_export(), which always emits a syntactically valid
		single-quoted PHP string literal with all special characters escaped.

		Template convention: placeholders are written as '{PLACEHOLDER}' with
		surrounding single quotes. The replacement substitutes the ENTIRE
		quoted token (including the outer quotes) with the var_export output,
		because var_export produces its own quotes.
	*/

	/*
		Validate a client IP for inclusion in the config. Returns either
		the validated IP or the loopback fallback — never the raw input.
	*/
	function install_sanitise_client_ip($input) {
		$ip = is_string($input) ? trim($input) : '';
		if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
			return '127.0.0.1';
		}
		return $ip;
	}

	/*
		Serialise a string value into a safe PHP literal.
		Wrapper around var_export so callers don't have to import the
		idiom repeatedly and to give us a single choke point if we ever
		need to switch the escape strategy.
	*/
	function install_config_literal($value) {
		return var_export((string)$value, true);
	}

	/*
		Render the config file contents from the install/config template.

		$values must contain every placeholder key expected by the template.
		Keys:
			STORAGE_FOLDER, ADMIN_FOLDER, DB_SERVER, DB_USERNAME,
			DB_PASSWORD, DB_DATABASE, DB_TABLE_PREFIX, CLIENT_IP,
			STORE_TIME_ZONE, HMAC_KEY_REMEMBER_ME.

		Missing keys trigger an explicit Exception — we prefer failing the
		install over silently writing 'null' into a security-relevant file.
	*/
	function install_render_config($template_path, array $values) {

		$template = file_get_contents($template_path);
		if ($template === false) {
			throw new Exception('Could not read config template at ' . $template_path);
		}

		$required = [
			'STORAGE_FOLDER', 'ADMIN_FOLDER',
			'DB_SERVER', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_TABLE_PREFIX',
			'CLIENT_IP', 'STORE_TIME_ZONE', 'HMAC_KEY_REMEMBER_ME',
		];

		foreach ($required as $key) {
			if (!array_key_exists($key, $values)) {
				throw new Exception('install_render_config: missing placeholder value for ' . $key);
			}
		}

		// Sanitise values whose semantics we understand. Everything else
		// gets neutralised by var_export, but we still normalise a few.
		$values['CLIENT_IP'] = install_sanitise_client_ip($values['CLIENT_IP']);

		$output = $template;
		foreach ($values as $key => $value) {
			// Replace the quoted placeholder token "'{KEY}'" (including
			// the surrounding single quotes in the template) with a
			// var_export-generated literal.
			$token = "'{" . $key . "}'";
			$literal = install_config_literal($value);
			$output = str_replace($token, $literal, $output);
		}

		return $output;
	}

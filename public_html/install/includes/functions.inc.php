<?php

	include_once __DIR__.'/../../includes/functions/func_file.inc.php';

	/*
		Returns true when the current process is a CLI invocation. The CLI
		polyfill in init.inc.php normalises $_SERVER['SERVER_SOFTWARE'] to
		'CLI' before this function is reachable, so a single comparison
		suffices. Defense-in-depth against mis-configured FPM pools is left
		to the SAPI check; entry points may also gate on php_sapi_name().
	*/
	function install_is_cli() {
		return ($_SERVER['SERVER_SOFTWARE'] ?? '') === 'CLI';
	}

	/*
		Backwards-compatible alias for the previous standalone helper. Both
		install.php and upgrade.php still call this name; future call sites
		may use csp_send_headers() directly.
	*/
	function install_send_security_headers() {
		csp_send_headers();
	}

	function csp_send_headers() {

		header('Content-Security-Policy: ' . implode('; ', [
			"default-src 'self'",
			"script-src 'self' 'nonce-". NONCE ."'",
			"style-src 'self' 'unsafe-inline'",
			"img-src 'self' data: ",
			"font-src 'self' data: 'nonce-". NONCE ."'",
			"connect-src 'self'",
			"form-action 'self'",
			"frame-ancestors 'none'",
			"base-uri 'self'",
		]));

		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header('Referrer-Policy: same-origin');

		if ($_SERVER['SERVER_SOFTWARE'] !== 'CLI' && $_SERVER['HTTPS'] !== 'off') {
			header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}

	function br($output) {
		if (is_array($output)) {
			echo implode('<br>'.PHP_EOL, $output);
		} else {
			echo $output .'<br>'. PHP_EOL;
		}
	}

	function return_bytes($string) {
		sscanf($string, '%u%c', $number, $suffix);
		if (isset($suffix)) {
			$number = $number * pow(1024, strpos(' KMG', strtoupper($suffix)));
		}
		return $number;
	}

	function perform_action($action, $payload, $on_error='skip') {

		switch ($action) {

			case 'copy':

				foreach ($payload as $source => $target) {

					if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
						if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $target)) continue;
					}

					br('Copying files from '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $source) .' to '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $target) . '...');

					$results = [];

					if (!file_xcopy($source, $target, false, $results)) {

						foreach ($results as $file => $result) {

							if (!$result) {

								echo '  - '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

								if ($on_error == 'skip') {
									br(' <span class="warning">[Skipped]</span>');
								} else {
									br(' <span class="error">[Failed]</span>');
									exit;
								}
							}
						}
					}
				}

				break;

			case 'custom':

				foreach ($payload as $source => $operations) {

					if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
						if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
					}

					$results = [];

					if (!$files = file_search($source)) {
						$results[] = false;
					}


					foreach ($files as $file) {

						br('Performing custom actions on ' . preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file) .'...');

						foreach ($operations as $i => $operation) {

							echo '  - Operation '. $i +1;

							$result = $operation($file);

							if ($result) {
								br([
									' <span class="ok">[OK]</span>',
									'',
								]);

							} else if ($on_error == 'skip') {
								br([
									' <span class="warning">[Skipped]</span>',
									'',
								]);

							} else {
								br([
									' <span class="error">[Failed]</span>',
									'',
								]);
								exit;
							}

							$results[] = $result;
						}
					}
				}

				break;

			case 'delete':

				foreach ($payload as $source) {

					if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
						if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
					}

					br('Deleting '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $source) .'...');

					$results = [];

					if (!file_delete($source, true, $results)) {

						foreach ($results as $file => $result) {

							if (!$result) {

								echo '  - '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

								if ($on_error == 'skip') {
									br(' <span class="warning">[Skipped]</span>');
								} else {
									br(' <span class="error">[Failed]</span>');
									exit;
								}
							}
						}
					}
				}

				break;

			case 'move':
			case 'rename':

				foreach ($payload as $source => $target) {

					if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
						if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
					}

					br('Moving '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $source) .' to '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $target) .'...');

					$results = [];

					if (!file_move($source, $target, false, $results)) {

						foreach ($results as $file => $result) {

							if (!$result) {

								echo '  - '. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

								if ($on_error == 'skip') {
									br(' <span class="warning">[Skipped]</span>');
								} else {
									br(' <span class="error">[Failed]</span>');
									exit;
								}
							}
						}
					}
				}

				break;

				case 'modify':

					foreach ($payload as $source => $operations) {

						if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
							if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
						}

						$results = [];

						if (!$files = file_search($source)) {
							$results[] = false;
						}

						foreach ($files as $file) {

							br('Modifying ' . preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $source) .'...');

							$contents = file_get_contents($file);
							$contents = preg_replace('#(\r\n?|\n)#u', PHP_EOL, $contents);

							foreach ($operations as $i => $operation) {

								if (!empty($operation['regex'])) {
									$contents = preg_replace($operation['search'], $operation['replace'], $contents, -1, $count);
								} else {
									$contents = str_replace($operation['search'], $operation['replace'], $contents, $count);
								}

								if (!$count) {
									echo '  - Operation #'. $i+1;

									if ($on_error == 'skip') {
										br(' <span class="warning">[Skipped]</span>');

									} else {
										br([
											' <span class="error">[Failed]</span>',
											'  Search: ' . $operation['search'],
											'  Replace: ' . $operation['replace'],
											'',
										]);
										exit;
									}
								}

								$results[] = file_put_contents($file, $contents);
							}
						}
					}

					break;

			default:
				throw new Error("Unknown action ($action)");
		}
	}


	/*
		Returns true when the installation is marked complete.
		A present storage/install.lock file is authoritative; file size and
		contents are irrelevant — only existence counts.
	*/
	function install_is_locked() {
		return is_file(FS_DIR_STORAGE . 'install.lock');
	}

	/*
		Terminate the current request with an HTTP 403 and a short,
		non-sensitive message. Used by installer entry points that detect
		a completed installation.
	*/
	function install_reject_locked() {
		if (!headers_sent()) {
			http_response_code(403);
			header('Content-Type: text/plain; charset=UTF-8');
		}
		echo 'Installation already completed. Remove storage/install.lock to reinstall.' . PHP_EOL;
		exit;
	}

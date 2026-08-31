<?php

	// CLI option list. Kept as documentation of the accepted options; the
	// values are parsed by init.inc.php.
	$INSTALL_CLI_OPTIONS = [
		'db_server::', 'db_username:', 'db_password::', 'db_database:', 'db_table_prefix::', 'db_collation::',
		'document_root:', 'timezone::', 'backend_alias::', 'username::', 'password::', 'development_type::', 'cleanup::',
		'client_ip::',
		'store_name::', 'store_email::', 'country_code::', 'store_country_code::', 'store_language_code::', 'store_time_zone::',
	];

	require_once __DIR__ . '/includes/init.inc.php';

	csp_send_headers();

	if (is_cli()) {
		// CLI sets $_REQUEST['install'] explicitly after option parsing below.
		$_REQUEST['install'] = true;
	}

	// Normalize alternative parameter names to what the steps expect. The web
	// form and the CI workflow use store_* names, while the steps read
	// country_code / timezone.
	$_REQUEST['country_code'] ??= $_REQUEST['store_country_code'] ?? '';
	$_REQUEST['timezone'] ??= $_REQUEST['store_time_zone'] ?? null;
	$_REQUEST['language_code'] ??= $_REQUEST['store_language_code'] ?? null;

	// AC-1, AC-2: reject installer runs on a completed installation.
	// Applies to both web requests and CLI — removing the lock file is an
	// explicit, human decision; no --force flag is exposed.
	if (install_is_locked()) {
		install_send_security_headers();
		install_reject_locked();
	}

	// Set platform name
	if (preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents(FS_DIR_APP . 'shared/app_header.inc.php'), $matches)) {
		define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);
	} else {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'Could not get platform name</p>' . PHP_EOL  . PHP_EOL);
	}

	// Set platform version
	if (preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents(FS_DIR_APP . 'shared/app_header.inc.php'), $matches)) {
		define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);
	} else {
		throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . 'Could not get platform version</p>' . PHP_EOL  . PHP_EOL);
	}

	require_once FS_DIR_APP . 'shared/compatibility.inc.php';

	if (is_cli()) {

		if (!isset($argv[1]) || (in_array($argv[1], ['help', '-h', '--help', '/?']))) {
			echo implode(PHP_EOL, [
				'',
				PLATFORM_NAME .'® '. PLATFORM_VERSION,
				'Copyright (c) '. date('Y') .' LiteCart AB',
				'https://www.litecart.net/',
				'Usage: php index.php install [options]',
				'',
				'Options:',
				'  --db_server          Set database hostname (Default: localhost)',
				'  --db_username        Set database username',
				'  --db_password        Set database user password',
				'',
				'  --db_database        Set database name',
				'  --db_table_prefix    Set database table prefix (Default: lc_).',
				'  --db_collation       Set database collation (Default: utf8mb4_swedish_ci)',
				'  --document_root      Set document root',
				'',
				'  --store_name         Set store name',
				'  --store_email        Set store email',
				'  --country_code       Set store country code (e.g. US)',
				'',
				'  --timezone           Set timezone e.g. Europe/London',
				'',
				'  --backend_alias       Set admin folder name (Default: admin)',
				'  --username           Set admin username',
				'  --password           Set admin user password',
				'',
				'  --development_type   Set development type "standard" or "advanced" (Default: standard)',
				'  --cleanup            Delete the install/ directory after finishing the installation.',
				'',
			]);
			exit;
		}

		// Options already collected into $_REQUEST by init.inc.php.
	}

	// DOCUMENT_ROOT may have been set by init.inc.php (from $_SERVER or
	// --document_root). Only define it here as a fallback / error gate.
	if (!defined('DOCUMENT_ROOT')) {
		if (!empty($_SERVER['DOCUMENT_ROOT'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/') . '/');

		} else if (is_cli() && !empty($_REQUEST['document_root'])) {
			define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', realpath($_REQUEST['document_root'])), '/') . '/');

		} else {
			throw new Exception('<span class="error">[Error]</span>' . PHP_EOL . ' Could not detect \$_SERVER[\'DOCUMENT_ROOT\']. If you are using CLI, make sure you pass the parameter "document_root" e.g. --document_root="/var/www/mysite.com/src"</p>' . PHP_EOL  . PHP_EOL);
		}
	}

	if (empty($_REQUEST['install'])) {
		header('Location: index.php', 302);
		exit;
	}

	ob_start();

	if (!defined('VMOD_DISABLED')) {
		define('VMOD_DISABLED', 'true');
	}

	require_once FS_DIR_APP . 'shared/shorthand.inc.php';

	require_once FS_DIR_APP . 'shared/nodes/nod_database.inc.php';
	require_once FS_DIR_APP . 'shared/nodes/nod_functions.inc.php';
	require_once FS_DIR_APP . 'shared/clients/http_client.inc.php';
	require_once FS_DIR_APP . 'shared/functions/func_file.inc.php';
	require_once FS_DIR_APP . 'shared/functions/func_csv.inc.php';
	require_once FS_DIR_APP . 'shared/error_handler.inc.php';

	require_once __DIR__ . '/includes/functions.inc.php';

	$requirements = json_decode(file_get_contents(__DIR__ . '/requirements.json'), true);

	try {

		## Perform installation ############################################

		echo '<h1>LiteCart Installer</h1>' . PHP_EOL . PHP_EOL;

		// Execute sub-steps in order
		foreach (scandir(__DIR__ . '/steps/') as $file) {
			if (preg_match('#^[0-9]+-.*\.inc\.php$#', $file)) {
				require __DIR__ . '/steps/' . $file;
			}
		}

		### ################################################################

		echo implode(PHP_EOL, [
			'<h2>Complete</h2>',
			'<p>Installation complete!</p>',
			'<p>You may now log in to the <a href="../'. BACKEND_ALIAS .'/">backend</a> and start configuring your store.</p>',
			'<p>Check out the <a href="https://wiki.litecart.net/" target="_blank">LiteCart Wiki</a> website for some great tips. Turn to our <a href="https://www.litecart.net/forums/" target="_blank">Community Forums</a> if you have questions.</p>',
		]);

		if (!is_cli()) {
			echo implode(PHP_EOL, [
				'<form method="get" action="http://x.com/intent/tweet" target="_blank">',
				'  <input type="hidden" value="https://www.litecart.net/">',
				'  <label class="form-group">',
				'    <div class="input-group">',
				'      <input type="text" class="form-input" name="text" value="Woohoo! I just installed #LiteCart and I am super excited! :)">',
				'      <button class="btn btn-primary" type="submit">Tweet!</button>',
				'    </div>',
				'  </label>',
				'</form>',
			]);
		}

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'',
			'[ABORTED] ' . $t->getMessage(),
			'',
		]);

		if (is_cli()) {
			echo install_cli_format(ob_get_clean());
			exit(1);
		}
	}

	if (!empty($_REQUEST['redirect'])) {
		header('Location: '. $_REQUEST['redirect'], 303);
		exit;
	}

	// Emit the buffered output. CLI gets formatted plain text; web output is
	// captured by the front controller and wrapped in the shared layout.
	$content = ob_get_clean();
	echo is_cli() ? install_cli_format($content) : $content;

	if (is_cli()) exit;

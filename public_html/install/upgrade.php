<?php

	/*
		Unattended Upgrade:
		upgrade.php?upgrade=true&redirect={url}
	*/

	ini_set('memory_limit', -1);
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	@set_time_limit(900);

	// CLI options used by the shared bootstrap when getopt() runs.
	$INSTALL_CLI_OPTIONS = [
		'from_version::', 'development_type::', 'backup::', 'cleanup::',
	];

	require_once __DIR__ . '/includes/init.inc.php';

	if ($_SERVER['SERVER_SOFTWARE'] == 'CLI') {

		if (!isset($argv[1]) || (in_array($argv[1], ['help', '-h', '--help', '/?']))) {
			echo implode(PHP_EOL, [
				'',
				'LiteCart® 3.0.0',
				'Copyright (c) '. date('Y') .' LiteCart AB',
				'https://www.litecart.net/',
				'Usage: php '. basename(__FILE__) .' [options]',
				'',
				'Options:',
				'  --from_version       Manually set version migrating from. Omit for auto detection',
				'  --development_type   Set development type "standard" or "development" (Default: standard)',
				'  --backup             Backup the database before running upgrade (Default: true)',
				'  --cleanup            Delete the install/ directory after finishing the upgrade.',
				'',
			]);
			exit;
		}

		// $_REQUEST already populated by init.inc.php's getopt call.
		$_REQUEST['upgrade'] = true;

		if (isset($_REQUEST['cleanup'])) {
			$_REQUEST['cleanup'] = true;
		}
	}

	// Include config
	if (is_file(__DIR__ . '/../storage/config.inc.php')) {
		include(__DIR__ . '/../storage/config.inc.php'); // 3.0.0+

	} else if (is_file(__DIR__ . '/../includes/config.inc.php')) { // Prior to 3.x
		include(__DIR__ . '/../includes/config.inc.php');

	} else {

		require_once __DIR__ . '/includes/header.inc.php';

		echo implode(PHP_EOL, [
			'<h2>No Installation Detected</h2>',
			'<p>Warning: No configuration file was found.</p>',
			'<p><a class="btn btn-default" href="index.php">Click here to install instead</a></p>',
		]);

		require_once 'includes/footer.inc.php';
		return;
	}

	error_reporting(E_ALL);
	ini_set('ignore_repeated_errors', 'On');
	ini_set('log_errors', 'Off');
	ini_set('display_errors', 'On');
	ini_set('html_errors', 'On');

	if ($_SERVER['SERVER_SOFTWARE'] != 'CLI') {
		ini_set('display_errors', 'Off');
		require_once __DIR__ . '/includes/header.inc.php';
	}

	require_once __DIR__ . '/includes/functions.inc.php';

	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME); // Prior to 2.2.x
	}

	if (!defined('FS_DIR_STORAGE')) {
		define('FS_DIR_STORAGE', FS_DIR_APP . 'storage/'); // Prior to 3.x
	}

	if (!defined('WS_DIR_STORAGE')) {
		define('WS_DIR_STORAGE', WS_DIR_APP. 'storage/'); // Prior to 2.5.x
	}

	require_once FS_DIR_APP . 'includes/error_handler.inc.php';
	require_once FS_DIR_APP . 'includes/functions/func_file.inc.php';
	require_once FS_DIR_APP . 'includes/nodes/nod_database.inc.php';
	require_once FS_DIR_APP . 'includes/nodes/nod_event.inc.php';
	require_once FS_DIR_APP . 'includes/nodes/nod_functions.inc.php';
	require FS_DIR_APP . 'includes/nodes/nod_stats.inc.php';

	// AC-5, AC-6: the web upgrader requires an authenticated administrator.
	// CLI runs are exempt (see install_is_cli() for detection rules).
	// Session and administrator nodes are loaded lazily so that a broken
	// schema can still reach the CLI path; any exception during init is
	// treated as "not logged in" and the user gets redirected to login.
	if (!install_is_cli()) {
		try {
			if (!class_exists('session')) {
				require_once FS_DIR_APP . 'includes/nodes/nod_session.inc.php';
				session::init();
			}
			if (!class_exists('administrator')) {
				require_once FS_DIR_APP . 'includes/nodes/nod_administrator.inc.php';
				administrator::init();
			}
			$__upgrade_authenticated = administrator::check_login();
		} catch (Throwable $t) {
			error_log('upgrade.php auth gate failed: ' . $t->getMessage());
			$__upgrade_authenticated = false;
		}

		if (!$__upgrade_authenticated) {
			install_send_security_headers();
			http_response_code(401);
			header('Content-Type: text/html; charset=UTF-8');
			$admin_login_url = (defined('WS_DIR_ADMIN') ? WS_DIR_ADMIN : '/admin/') . 'login';
			echo '<!DOCTYPE html><html><head><title>Upgrade — Authentication Required</title></head><body>'
				. '<h1>Authentication Required</h1>'
				. '<p>The web upgrader requires an administrator session. '
				. '<a href="' . htmlspecialchars($admin_login_url, ENT_QUOTES) . '">Sign in</a> and retry, '
				. 'or run <code>php upgrade.php</code> from the command line.</p>'
				. '</body></html>';
			exit;
		}
	}

	$requirements = json_decode(file_get_contents(__DIR__ . '/requirements.json'), true);

	// Set platform name
	preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../includes/app_header.inc.php'), $matches);
	define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

	// Set platform version
	preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../includes/app_header.inc.php'), $matches);
	define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);

	if (!PLATFORM_VERSION) {
		die('Could not identify target version.');
	}

	// Get current platform database version
	$platform_database_version = database::query(
		"select `value` from ". DB_TABLE_PREFIX ."settings
		where `key` = 'platform_database_version'
		limit 1;"
	)->fetch('value');

	define('PLATFORM_DATABASE_VERSION', $platform_database_version);

	// List supported upgrades
	$supported_versions = ['1.0' => '1.0'];
	foreach (glob(__DIR__ . '/migrations/*') as $file) {
		if (preg_match('#/([^/]+).(?:inc.php|sql)$#', $file, $matches)) {
			$supported_versions[$matches[1]] = $matches[1];
		}
	}

	usort($supported_versions, function($a, $b) {
		return version_compare($a, $b, '>') ? 1 : -1;
	});

	if (empty($_REQUEST['development_type'])) {
		if (is_file($file = FS_DIR_APP . 'frontend/templates/default/.development')) {
			$_REQUEST['development_type'] = file_get_contents($file);
		}
	}

	if (!empty($_REQUEST['upgrade'])) {

		ob_start(function($buffer) {

			if (install_is_cli()) {
				$buffer = install_cli_format($buffer);
			}

			return $buffer;
		});

		try {

			ignore_user_abort(true);

			echo '<h1>Upgrade '. PLATFORM_VERSION .'</h1>' . PHP_EOL . PHP_EOL;

			## Perform installation ################################################

			register_shutdown_function(function(){
				$buffer = ob_get_clean();
				echo install_is_cli() ? install_cli_format($buffer) : $buffer;
			});

			echo '<h1>LiteCart Installer</h1>' . PHP_EOL . PHP_EOL;

			// Execute sub-steps in order
			foreach (scandir(__DIR__ . '/includes/steps/upgrade/') as $file) {
				if (preg_match('#^[0-9]+-.*\.inc\.php$#', $file)) {
					require __DIR__ . '/includes/steps/upgrade/' . $file;
				}
			}

			########################################################################

			echo implode(PHP_EOL, [
				'<h2>Complete</h2>',
				'',
				'<p style="font-weight: bold;">Upgrade complete! Please delete the <strong>~/install/</strong> folder.</p>',
				'',
			]);

			if (!empty($_REQUEST['redirect'])) {
				header('Location: '. $_REQUEST['redirect'], 303);
				exit;
			}

		} catch (Throwable $t) {

			// Rollback if we are in a transaction
			if (defined('PLATFORM_DATABASE_VERSION') && version_compare(PLATFORM_DATABASE_VERSION, '3.0.0', '>=')) {
				database::rollback();
			}

			echo implode(PHP_EOL, [
				'<h2>Upgrade Failed</h2>',
				'',
				'<p style="font-weight: bold;">The upgrade failed. Please check the error log for more information.</p>',
				'',
				'<p>Error: '. htmlspecialchars($t->getMessage()) .'</p>',
				'',
			]);
		}

		echo ob_get_clean();

		if ($_SERVER['SERVER_SOFTWARE'] == 'CLI') exit;

		require('includes/footer.inc.php');
		exit;
	}

?>
<style>
html {
	display: table;
	width: 100%;
}

body {
	display: table-cell;
	vertical-align: middle;
}

.glass-edges {
	max-width: 640px;
}

input[name="development_type"] {
	display: none;
}

input[name="development_type"] + div {
	display: inline-block;
	padding: 15px;
	margin: 7.5px;
	border: 1px solid rgb(0 0 0 / 10%);
	border-radius: 15px;
	width: 250px;
	height: 145px;
	text-align: center;
	cursor: pointer;
}

input[name="development_type"] + div .type {
	font-size: 1.5em;
	line-height: 1.5em;
}

input[name="development_type"] + div .title {
	font-size: 1.25em;
	font-weight: bold;
	line-height: 1.5em;
}

input[name="development_type"]:checked + div {
	border-color: #333;
}
</style>

<form name="upgrade_form" method="post">
	<h1>Upgrade <?php echo PLATFORM_VERSION; ?></h1>

	<h2>Application</h2>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">MySQL/MariaDB Server</div>
				<div class="form-input">
					<?php echo DB_SERVER; ?>
				</div>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">MySQL/MariaDB Database</div>
				<div class="form-input">
					<?php echo DB_DATABASE; ?>
				</div>
			</label>
		</div>
	</div>

	<div class="grid">
		<?php if (defined('PLATFORM_DATABASE_VERSION')) { ?>
		<div class="col-md-3">
			<label class="form-group">
				<div class="form-label">Current Version</div>
				<div class="form-input"><?php echo PLATFORM_DATABASE_VERSION; ?></div>
			</label>
		</div>
		<?php } else { ?>
		<div class="col-md-3">
			<label class="form-group">
				<div class="form-label">Select the <?php echo PLATFORM_NAME; ?> version you are upgrading from:</div>
				<select class="form-input" name="from_version">
					<option value="">-- Select Version --</option>
					<?php foreach ($supported_versions as $version) echo '<option value="'. $version .'"'. ((isset($_REQUEST['from_version']) && $_REQUEST['from_version'] == $version) ? 'selected' : '') .'>'. PLATFORM_NAME .' '. $version .'</option>' . PHP_EOL; ?>
				</select>
			</label>
		</div>
		<?php } ?>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">New Version</div>
				<div class="form-input"><?php echo PLATFORM_VERSION; ?></div>
			</label>
		</div>
	</div>

	<label class="form-group">
		<input class="form-check" type="checkbox" name="backup" value="true" checked> Backup my database before performing the upgrade.
	</label>

	<label class="form-group">
		<input type="checkbox" class="form-check" name="skip_updates" value="0"> Skip downloading the latest updates
	</label>

	<h2>Development</h2>

	<div class="form-group" style="display: flex;">
		<label>
			<input name="development_type" value="standard" type="radio" checked>
			<div>
				<div class="type">Standard</div>
				<div class="title">
					.css<br>
					.js
				</div>
				<small class="description">(Uncompressed files)</small>
			</div>
		</label>

		<label>
			<input name="development_type" value="advanced" type="radio">
			<div>
				<div class="type">Advanced</div>
				<div class="title">
					.scss + .min.css<br>
					.js + .min.js
				</div>
				<small class="description">
					(Requires a <a href="https://www.litecart.net/addons/163/developer-kit" target="_blank">SCSS compiler</a>)
				</small>
			</div>
		</label>
	</div>

	<button class="btn btn-success btn-block" type="submit" name="upgrade" value="true" onclick="if(!confirm('Warning! The procedure cannot be undone.')) return false;" style="font-size: 1.5em; padding: 0.5em;">Upgrade To <?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></button>
</form>

<?php	require 'includes/footer.inc.php'; ?>
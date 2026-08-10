<?php

	/*
		Unattended Upgrade:
		upgrade.php?upgrade=true&redirect={url}

		CLI Usage:
		php index.php upgrade --from_version=2.3.0 --development_type=standard
	*/

	ini_set('memory_limit', -1);
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	@set_time_limit(900);

	// CLI options. Kept as documentation of the accepted options; the
	// values are parsed by init.inc.php.
	$INSTALL_CLI_OPTIONS = [
		'from_version::', 'development_type::', 'backup::', 'cleanup::',
	];

	// Load an existing site config. The front controller (install/index.php)
	// already bootstrapped init.inc.php, so constants it defined (FS_DIR_APP,
	// FS_DIR_STORAGE, WS_DIR_APP, WS_DIR_STORAGE, DOCUMENT_ROOT) are redefined
	// here with identical values — suppress the E_WARNING noise with @.
	if (is_file(__DIR__ . '/../../storage/config.inc.php')) {
		$installation_detected = true;
		@include(__DIR__ . '/../../storage/config.inc.php'); // 3.0.0+

	} else if (is_file(__DIR__ . '/../../includes/config.inc.php')) { // Prior to 3.x
		$installation_detected = true;
		@include(__DIR__ . '/../../includes/config.inc.php');

	} else {
		$installation_detected = false;
	}

	require_once __DIR__ . '/../init.inc.php';

	csp_send_headers();

	if (is_cli()) {

		if (!isset($argv[1]) || (in_array($argv[1], ['help', '-h', '--help', '/?']))) {
			echo implode(PHP_EOL, [
				'',
				'LiteCart® 3.0.0',
				'Copyright (c) '. date('Y') .' LiteCart AB',
				'https://www.litecart.net/',
				'Usage: php index.php upgrade [options]',
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

		// Options already collected into $_REQUEST by init.inc.php.
		$_REQUEST['upgrade'] = true;

		if (isset($_REQUEST['cleanup'])) {
			$_REQUEST['cleanup'] = true;
		}
	}

	// Include config
	if (!$installation_detected) {

		$content = implode(PHP_EOL, [
			'<h2>No Installation Detected</h2>',
			'<p>Warning: No configuration file was found.</p>',
			'<p><a class="btn btn-default" href="index.php">Click here to install instead</a></p>',
		]);

		if (is_cli()) {
			echo install_cli_format($content) . PHP_EOL;
			exit;
		}

		echo $content;
		return;
	}

	error_reporting(E_ALL);
	ini_set('ignore_repeated_errors', 'On');
	ini_set('log_errors', 'Off');
	ini_set('display_errors', 'On');
	ini_set('html_errors', 'On');

	if (!is_cli()) {
		ini_set('display_errors', 'Off');
	}

	require_once __DIR__ . '/../functions.inc.php';

	if (!defined('FS_DIR_APP')) {
		define('FS_DIR_APP', FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME); // Prior to 2.2.x
	}

	if (!defined('FS_DIR_STORAGE')) {
		define('FS_DIR_STORAGE', FS_DIR_APP . 'storage/'); // Prior to 3.x
	}

	if (!defined('WS_DIR_STORAGE')) {
		define('WS_DIR_STORAGE', WS_DIR_APP. 'storage/'); // Prior to 2.5.x
	}

	require_once FS_DIR_APP . 'shared/error_handler.inc.php';
	require_once FS_DIR_APP . 'shared/functions/func_file.inc.php';
	require_once FS_DIR_APP . 'shared/nodes/nod_database.inc.php';
	require_once FS_DIR_APP . 'shared/nodes/nod_event.inc.php';
	require_once FS_DIR_APP . 'shared/nodes/nod_functions.inc.php';
	require FS_DIR_APP . 'shared/nodes/nod_stats.inc.php';

	$requirements = json_decode(file_get_contents(__DIR__ . '/../requirements.json'), true);

	// Set platform name
	preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../../shared/app_header.inc.php'), $matches);
	define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

	// Set platform version
	preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents(__DIR__.'/../../shared/app_header.inc.php'), $matches);
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

	if (!empty($platform_database_version)) {
		$version_detected = true;
		define('PLATFORM_DATABASE_VERSION', $platform_database_version);
	} else if (!empty($_REQUEST['from_version'])) {
		define('PLATFORM_DATABASE_VERSION', $_REQUEST['from_version']);
	} else {
		define('PLATFORM_DATABASE_VERSION', false);
	}

	// List supported upgrades
	$supported_versions = ['1.0' => '1.0'];
	foreach (glob(__DIR__ . '/../migrations/*') as $file) {
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

		ob_start();

		try {

			ignore_user_abort(true);

			echo '<h1>Upgrade '. PLATFORM_VERSION .'</h1>' . PHP_EOL . PHP_EOL;

			## Perform installation ################################################

			echo '<h1>LiteCart Installer</h1>' . PHP_EOL . PHP_EOL;

			// Execute sub-steps in order
			foreach (scandir(__DIR__ . '/../steps/upgrade/') as $file) {
				if (preg_match('#^[0-9]+-.*\.inc\.php$#', $file)) {
					require __DIR__ . '/../steps/upgrade/' . $file;
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
			if (version_compare(PLATFORM_DATABASE_VERSION, '3.0.0', '>=')) {
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

			if (is_cli()) exit(1);
		}

		// Emit the buffered output. CLI gets formatted plain text; web output
		// is captured by the front controller and wrapped in the shared layout.
		$content = ob_get_clean();
		echo is_cli() ? install_cli_format($content) : $content;
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
		<?php if ($version_detected) { ?>
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
					<?php foreach ($supported_versions as $version) { ?>
					<option value="<?php echo $version; ?>"<?php echo (isset($_REQUEST['from_version']) && $_REQUEST['from_version'] == $version) ? ' selected' : ''; ?>><?php echo PLATFORM_NAME .' '. $version; ?></option>
					<?php } ?>
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
		<input type="checkbox" class="form-check" name="download_updates" value="1" checked> Download the latest updates
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

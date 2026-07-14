<?php

	require_once __DIR__ . '/includes/init.inc.php';
	require_once __DIR__ . '/includes/header.inc.php';

	// Detect an existing installation
	if (is_file(FS_DIR_APP . 'includes/config.inc.php') || is_file(FS_DIR_STORAGE . 'config.inc.php')) {
		$installation_detected = true;
	}

	// Set defaults
	if (!$_POST) {
		$_POST['country_code'] = $_GET['country_code']
			?? $_SERVER['HTTP_CF_IPCOUNTRY']
			?? $_SERVER['HTTP_X_COUNTRY_CODE']
			?? $_SERVER['HTTP_X_COUNTRY']
			?? '';
	}

	$requirements = json_decode(file_get_contents(__DIR__ . '/requirements.json'), true);

	$writables = [
		FS_DIR_STORAGE . 'cache/',
		FS_DIR_STORAGE . 'data/',
		FS_DIR_STORAGE . 'images/',
		FS_DIR_STORAGE . 'config.inc.php',
		FS_DIR_STORAGE . 'vmods/',
		FS_DIR_STORAGE . '.htaccess',
	];

	$timezones = [];
	foreach (timezone_identifiers_list() as $timezone) {
		$timezone = explode('/', $timezone);
		if (!in_array($timezone[0], [
			'Africa',
			'America',
			'Antarctica',
			'Arctic',
			'Asia',
			'Atlantic',
			'Australia',
			'Europe',
			'Indian',
			'Pacific'
		]) || empty($timezone[1])) continue;
		$timezones[] = implode('/', $timezone);
	}

	// Get list over countries from the i18n repository
	$countries = (function() {
		if (!$response = file_get_contents('https://raw.githubusercontent.com/litecart/i18n/refs/heads/master/countries.csv')) {
			throw new Error('Unable to retrieve country data');
		}
		if (!$result = f::csv_decode($response)) {
			throw new Error('Unable to parse country data');
		}
		return array_column($result, 'name', 'code');
	})();

?>
<style>
ul {
	break-inside: avoid;
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

.text-ellipsis {
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
</style>

<h1>Installer</h1>

<h2>System Requirements</h2>

<div style="columns: 320px auto; margin-bottom: 2em;">
	<h3>PHP</h3>

	<ul>
		<li>PHP <?php echo $requirements['scripting']['php']['minimumVersion']; ?>+ (Recommended: <?php echo $requirements['scripting']['php']['recommendedVersion']; ?>+) <?php echo (version_compare(PHP_VERSION, '8.0', '>='))? '<span class="ok">['. PHP_VERSION .']</span>' : '<span class="error">['. PHP_VERSION .']</span>'; ?></li>
		<li>register_globals = <?php echo ini_get('register_globals') ?: 'off'; ?> <?php echo in_array(strtolower(ini_get('register_globals')), ['off', 'false', '', '0']) ? '<span class="ok">[OK]</span>' : '<span class="error">[Alert! Must be disabled]</span>'; ?></li>
		<li>arg_separator.output = <?php echo htmlspecialchars(ini_get('arg_separator.output')); ?> <?php echo (ini_get('arg_separator.output') == '&') ? '<span class="ok">[OK]</span>' : '<span class="error">[Not recommended]</span>'; ?></li>
		<li>memory_limit = <?php echo ini_get('memory_limit'); ?> <?php echo (return_bytes(ini_get('memory_limit')) >= 128*1024*1024) ? '<span class="ok">[OK]</span>' : '<span class="warning">[128M+ recommended]</span>'; ?></li>
		<li>Extensions
			<ul>
				<li>apc / apcu <?php echo (extension_loaded('apcu') || extension_loaded('apc')) ? '<span class="ok">[OK]</span>' : '<span class="warning">[Missing, optional]</span>'; ?></li>
				<li>dom <?php echo extension_loaded('dom') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>fileinfo <?php echo extension_loaded('fileinfo') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, optional]</span>'; ?></li>
				<li>gd / imagick <?php echo extension_loaded('imagick') ? '<span class="ok">[OK]</span>' : (extension_loaded('gd') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'); ?></li>
				<li>intl <?php echo extension_loaded('intl') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>json <?php echo extension_loaded('json') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>libxml <?php echo extension_loaded('libxml') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>mbstring <?php echo extension_loaded('mbstring') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>mysqli <?php echo extension_loaded('mysqli') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>mysqlnd <?php echo extension_loaded('mysqlnd') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>openssl <?php echo extension_loaded('openssl') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>simplexml <?php echo extension_loaded('simplexml') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
				<li>zip <?php echo extension_loaded('zip') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing, required]</span>'; ?></li>
			</ul>
		</li>
	</ul>

	<h3>HTTP Server</h3>

	<ul>
		<li>Apache Version 2 / LiteSpeed</li>
		<li>Modules
			<ul>
				<?php if (function_exists('apache_get_modules')) $installed_apache_modules = apache_get_modules(); ?>
				<li>mod_deflate <?php if (!empty($installed_apache_modules)) echo in_array('mod_deflate', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="warning">[Not Detected]</span>'; ?></li>
				<li>mod_env <?php if (!empty($installed_apache_modules)) echo in_array('mod_env', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="warning">[Not Detected]</span>'; ?></li>
				<li>mod_headers <?php if (!empty($installed_apache_modules)) echo in_array('mod_headers', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="warning">[Not Detected]</span>'; ?></li>
				<li>mod_rewrite <?php if (!empty($installed_apache_modules)) echo in_array('mod_rewrite', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="warning">[Not Detected]</span>'; ?></li>
			</ul>
		</li>
	</ul>

	<h3>Database Server</h3>

	<ul>
		<?php foreach ($requirements['database'] as $database) { ?>
		<li>
			<?php echo $database['name']; ?> <?php echo $database['minimumVersion']; ?>+ (Recommended: <?php echo $database['recommendedVersion']; ?>+)
		</li>
		<?php } ?>
	</ul>

	<h2>Writables</h2>

	<ul>
<?php
	foreach ($writables as $path) {
		if ((file_exists($path) && is_writable($path)) || is_writable(dirname($path))) {
			echo '    <li>~/'. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path) .' <span class="ok">[OK]</span></li>' . PHP_EOL;
		} else {
			echo '    <li>~/'. preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $path) .' <span class="error">[Not writable]</span></li>' . PHP_EOL;
		}
	}
?>
	</ul>
</div>

<h2>Installation Parameters</h2>

<form name="installation_form" method="post" action="install.php">

	<input class="form-input" name="client_ip" type="hidden" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">

	<h3>File System</h3>

	<div class="grid">
		<div class="col-md-9">
			<label class="form-group">
				<div class="form-label">Installation Path</div>
				<div class="form-input text-ellipsis" title="<?php echo htmlspecialchars(FS_DIR_APP); ?>"><?php echo htmlspecialchars(FS_DIR_APP); ?></div>
			</label>
		</div>

		<div class="col-md-3">
			<label class="form-group">
				<div class="form-label">Storage Directory</div>
				<div class="form-input">storage/</div>
			</label>
		</div>
	</div>

	<h3>Database</h3>

	<div class="grid">

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Type</div>
				<select class="form-select" name="db_type" required>
					<option value="mysql">MySQL / MariaDB</option>
				</select>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Hostname</div>
				<input class="form-input" name="db_server" type="text" placeholder="localhost">
			</label>
		</div>
	</div>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Database</div>
				<input class="form-input" type="text" name="db_database" required>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Collation</div>
				<select class="form-select" name="db_collation" required>
					<option>utf8mb4_bin</option>
					<option>utf8mb4_general_ci</option>
					<option selected>utf8mb4_unicode_ci</option>
					<option>utf8mb4_icelandic_ci</option>
					<option>utf8mb4_latvian_ci</option>
					<option>utf8mb4_romanian_ci</option>
					<option>utf8mb4_slovenian_ci</option>
					<option>utf8mb4_polish_ci</option>
					<option>utf8mb4_estonian_ci</option>
					<option>utf8mb4_spanish_ci</option>
					<option>utf8mb4_swedish_ci</option>
					<option>utf8mb4_turkish_ci</option>
					<option>utf8mb4_czech_ci</option>
					<option>utf8mb4_danish_ci</option>
					<option>utf8mb4_lithuanian_ci</option>
					<option>utf8mb4_slovak_ci</option>
					<option>utf8mb4_spanish2_ci</option>
					<option>utf8mb4_roman_ci</option>
					<option>utf8mb4_persian_ci</option>
					<option>utf8mb4_esperanto_ci</option>
					<option>utf8mb4_hungarian_ci</option>
					<option>utf8mb4_sinhala_ci</option>
				</select>
				<div class="form-check">
					<input type="checkbox" name="set_default_collation" value="1" checked> Set default collation for the database
				</div>
			</label>
		</div>
	</div>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Username</div>
				<input class="form-input" type="text" name="db_username" required>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Password</div>
				<input class="form-input" type="password" name="db_password">
			</label>
		</div>
	</div>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Table Prefix</div>
				<input class="form-input" name="db_table_prefix" type="text" value="lc_" style="max-width: 50%;">
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Demo Data</div>
				<label class="form-check">
					<input name="demo_data" type="checkbox" value="true" <?php if (!file_exists('data/demo/data.sql')) echo 'disabled'; ?>> Install demo data
				</label>
			</label>
		</div>

	</div>

	<h3>Store Information</h3>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Store Name</div>
				<input class="form-input" name="store_name" type="text" value="My Store" required>
			</label>
			</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Store Email</div>
				<input class="form-input" name="store_email" type="text" value="store@email.com" required>
			</label>
		</div>
	</div>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Country</div>
				<select class="form-input" name="country_code" required>
					<option value="">-- Select --</option>
					<?php foreach ($countries as $code => $name) echo '<option value="'. $code .'">'. $name .'</option>' . PHP_EOL; ?>
				</select>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Time Zone</div>
				<select class="form-input" name="store_time_zone" required>
					<option value="">-- Select --</option>
					<?php	foreach ($timezones as $timezone) { ?>
					<option><?php echo $timezone; ?></option>
					<?php } ?>
				</select>
			</label>
		</div>
	</div>

	<h3>Development</h3>

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

	<h3>Backend</h3>

	<div class="grid">

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Backend URL</div>
				<div class="input-group">
					<span class="input-group-text"><?php echo preg_replace('#install/.*$#', '', strtok($_SERVER['REQUEST_URI'], '?')); ?></span>
					<input class="form-input" name="backend_alias" type="text" value="admin" required>
				</div>
			</label>
		</div>

		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Username</div>
				<input class="form-input" name="username" type="text" id="username" value="admin" required>
			</label>
		</div>
	</div>

	<div class="grid">
		<div class="col-md-6">
			<label class="form-group">
				<div class="form-label">Password</div>
				<input class="form-input" name="password" type="password" id="password" required>
			</label>
		</div>

	</div>

	<hr>

	<div class="text-center">
		This software is licensed under <a href="https://creativecommons.org/licenses/by-nd/4.0/" target="blank">Creative Commons BY-ND 4.0</a>.
	</div>

	<div class="form-group text-center">
		<label class="form-check">
			<input id="accept_terms" name="accept_terms" value="1" type="checkbox" required> I agree to the terms and conditions.
		</label>
	</div>

	<input class="btn btn-success btn-block" type="submit" name="install" value="Install Now" onclick="if (document.getElementById('accept_terms').value != 1) return false; if(!confirm('This will now install LiteCart. Any existing databases tables will be overwritten with new data.')) return false;" style="font-size: 1.5em; padding: 0.5em;">
</form>

<?php if (!empty($installation_detected)) { ?>
<div id="modal-warning-existing-installation" style="display: none; width: 320px;">
	<h2>Existing Installation Detected</h2>
	<p>Warning: An existing installation has been detected. It <u>will be deleted</u> if you continue!</p>
	<p><a class="btn btn-default" href="upgrade.php">Click here to upgrade instead <i class="icon-arrow-right"></i></a></p>
</div>
<?php } ?>

<script nonce="<?php echo htmlspecialchars(NONCE, ENT_QUOTES); ?>">
waitFor('jQuery', function($){

	<?php if (!empty($installation_detected)) { ?>
	// Warn about existing installation
	$.litebox('#modal-warning-existing-installation');
	<?php } ?>

	// Attempt to determine country from browser
	if (!$('select[name="country_code"]').val() || !$('select[name="store_time_zone"]').val()) {
		$.get('http://ip-api.com/json/?fields=countryCode,timezone', function(data){

			if (!$('select[name="country_code"]').val() && data.countryCode && data.countryCode.length == 2) {
				$('select[name="country_code"]').val(data.countryCode);
			}

			if (!$('select[name="store_time_zone"]').val() && data.timezone && data.timezone.length) {
				$('select[name="store_time_zone"]').val(data.timezone);
			}
		});
	}

});
</script>

<?php require(__DIR__.'/includes/footer.inc.php'); ?>
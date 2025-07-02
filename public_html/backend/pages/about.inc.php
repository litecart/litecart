<?php

	breadcrumbs::add(t('title_dashboard', 'Dashboard'), WS_DIR_ADMIN);
	breadcrumbs::add(t('title_about', 'About'), document::link());

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['errors'])) {
				throw new Exception(t('error_must_select_errors', 'You must select errors'));
			}

			$log_file = ini_get('error_log');

			ini_set('memory_limit', -1); // Unlimit memory for reading log file
			$content = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

			foreach ($_POST['errors'] as $error) {
				$content = preg_replace('#\[\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+\] '. preg_quote($error, '#') . addcslashes(PHP_EOL, "\r\n") .'[^\[]*#s', '', $content, -1, $count);
				if (!$count) {
					throw new Exception('Failed deleting error from log');
				}
			}

			file_put_contents($log_file, $content);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// CPU Usage
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
		if (function_exists('sys_getloadavg')) {
			$cpu_usage = round(sys_getloadavg()[0], 2);
		}
	}

	// Memory Usage
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {

		if (@is_readable('/proc/meminfo')) {
			$fh = fopen('/proc/meminfo','r');

			while ($line = fgets($fh)) {
				$pieces = [];
				if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
					$ram_usage = $pieces[1];
					continue;
				}
				if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
					$ram_free = $pieces[1];
					continue;
				}
			}

			fclose($fh);

			$ram_usage = round($ram_usage / ($ram_usage + $ram_free) * 100, 2);
		}
	}

	// Server Uptime
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
		if (@is_readable('/proc/uptime')) {
			$raw_uptime = round((float)file_get_contents('/proc/uptime'));
			$seconds = fmod($raw_uptime, 60);  $raw_uptime = intdiv($raw_uptime, 60);
			$minutes = $raw_uptime % 60;  $raw_uptime = intdiv($raw_uptime, 60);
			$hours = $raw_uptime % 24;  $raw_uptime = intdiv($raw_uptime, 24);
			$days = $raw_uptime;

			if ($days) {
				$uptime = $days .' day(s)';
			} else if ($hours) {
				$uptime = $hours .' hour(s)';
			} else if ($minutes) {
				$iptime = $minutes .' minute(s)';
			} else if ($seconds) {
				$uptime = $seconds .' second(s)';
			}
		}
	}

	$machine = [

			'name' => php_uname('n'),
			'architecture' => php_uname('m'),
			'os' => [
				'name' => php_uname('s') .' '. php_uname('r'),
				'version' => php_uname('v'),
			],
			'ip_address' => $_SERVER['SERVER_ADDR'],
			'hostname' => gethostbyaddr($_SERVER['SERVER_ADDR']),
			'cpu_usage' => fallback($cpu_usage, ''),
			'memory_usage' => fallback($memory_usage, ''),
			'uptime' =>  fallback($uptime, ''),
	];

	$web_server = [
		'name' => fallback($_SERVER['SERVER_SOFTWARE'], ''),
		'sapi' => php_sapi_name(),
		'current_user' => get_current_user(),
		'loaded_modules' => function_exists('apache_get_modules') ? apache_get_modules() : [],
	];

	$php = [
		'version' => PHP_VERSION .' ('. ((PHP_INT_SIZE === 8) ? '64-bit' : '32-bit') .')',
		'whoami' => (function_exists('exec') && !in_array('exec', preg_split('#\s*,\s*#', ini_get('disabled_functions')))) ? exec('whoami') : '',
		'loaded_extensions' => (function_exists('get_loaded_extensions') && !in_array('get_loaded_extensions', preg_split('#\s*,\s*#', ini_get('disabled_functions')))) ? get_loaded_extensions() : [],
		'disabled_functions' => ini_get('disabled_functions') ?  preg_split('#\s*,\s*#', ini_get('disabled_functions')) : [],
		'memory_limit' => ini_get('memory_limit'),
		'ini' => ini_get_all(null, false),
	];

	sort($php['loaded_extensions'], SORT_NATURAL);
	sort($php['disabled_functions'], SORT_NATURAL);

	$database = [
			'name' => database::server_info(),
			'library' => mysqli_get_client_info(),
			'hostname' => DB_SERVER,
			'user' => DB_USERNAME,
			'database' => DB_DATABASE,
	];

	// Errors
	$errors = [];

	if ($log_file = ini_get('error_log')) {

		if (($filesize = filesize($log_file)) > 1024e6) {
			notices::add('warnings', t('warning_truncating_extremely_large_log_file', 'Truncating an extremely large log file') .' ('. language::number_format($filesize / (1024 * 1024)) .' Mbytes)');
			file_put_contents($logfile, '');
		}

		$iniatial_memory_limit = ini_get('memory_limit');
		ini_set('memory_limit', -1); // Unlimit memory for reading log file

		$entries = preg_replace('#(\r\n?|\n)#', PHP_EOL, file_get_contents($log_file));

		if (preg_match_all('#\[(\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/]+)\] (.*?)'. addcslashes(PHP_EOL, "\r\n") .'([^\[]*)#s', $entries, $matches)) {

			foreach (array_keys($matches[0]) as $i) {

				$checksum = crc32($matches[2][$i]);

				if (!isset($errors[$checksum])) {
					$errors[$checksum] = [
						'error' => $matches[2][$i],
						'backtrace' => $matches[3][$i],
						'occurrences' => 1,
						'last_occurrence' => strtotime($matches[1][$i]),
						'critical' => preg_match('#(Parse|Fatal) error:#s', $matches[2][$i]) ? true : false,
					];
				} else {
					$errors[$checksum]['occurrences']++;
					//$rows[$checksum]['backtrace'] = $matches[3][$i];
					$errors[$checksum]['last_occurrence'] = strtotime($matches[1][$i]);
				}
			}
		}

		uasort($errors, function($a, $b) {

			if ($a['critical'] != $b['critical']) {
				return ($a['critical'] > $b['critical']) ? -1 : 1;
			}

			if ($a['occurrences'] != $b['occurrences']) {
				return ($a['occurrences'] > $b['occurrences']) ? -1 : 1;
			}

			return ($a['last_occurrence'] > $b['last_occurrence']) ? -1 : 1;
		});

		unset($entries);

		ini_set('memory_limit', $iniatial_memory_limit); // Restore limit
	}

?>
<style>
.tabs {
	background: transparent;
	border-bottom: none;
}
.tabs .nav-link:not(.active) {
	background: #eee;
}
.tab-content {
	margin: 0;
	background: none;
}
#box-about {
	position: relative;
	overflow: hidden;
}
#box-about table + table {
	margin-top: 2em;
}
#box-about tbody th {
	text-align: left;
}
#box-about tr > *:first-child {
	width: 250px;
}
#box-about meter {
	width: 500px;
}
#box-about .credits-wrapper {
	position: absolute;
	top: 2em;
	inset-inline-end: 2em;
	width: 400px;
	height: 600px;
	padding: 2em;
	background: #fff;
	border: 1px solid var(--default-border-color);
	box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
	border-radius: var(--border-radius);
	overflow: hidden;
	transform: translateY(0%);
	z-index: 999;
}
#box-error-log tr.critical {
	background: #c002;
}
#box-error-log td {
	white-space: wrap !important;
	cursor: default;
}
</style>

<nav class="tabs">
	<a class="tab-item active" data-toggle="tab" href="#tab-system"><?php echo t('title_system', 'System'); ?></a>
	<a class="tab-item" data-toggle="tab" href="#tab-php-ini"><?php echo t('title_php_ini', 'PHP.ini'); ?></a>
	<a class="tab-item" data-toggle="tab" href="#tab-errors"><?php echo t('title_error_log', 'Error Log'); ?></a>
</nav>

<div class="tab-contents">
	<div id="tab-system" class="tab-content active">
		<div id="box-about" class="card">
			<div class="card-header">
				<div class="card-title">
					<?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?>
				</div>
			</div>

			<table class="table data-table">
				<thead>
					<tr>
						<th colspan="2">Application</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>System Path</th>
						<td><?php echo FS_DIR_APP; ?></td>
					</tr>
					<tr>
						<th>Document Root</th>
						<td><?php echo DOCUMENT_ROOT; ?></td>
					</tr>
					<tr>
						<th>Web Path</th>
						<td><?php echo WS_DIR_APP; ?></td>
					</tr>
				</tbody>
			</table>

			<table class="table data-table">
				<thead>
					<tr>
						<th colspan="2">Machine</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Name</th>
						<td><?php echo $machine['name']; ?></td>
					</tr>
					<tr>
						<th>IP Address</th>
						<td><?php echo $machine['ip_address']; ?></td>
					</tr>
					<tr>
						<th>Hostname</th>
						<td><?php echo $machine['hostname']; ?></td>
					</tr>
					<tr>
						<th>Operating System</th>
						<td><?php echo $machine['os']['name']; ?></td>
					</tr>
					<tr>
						<th>Operating System Version</th>
						<td><?php echo $machine['os']['version']; ?></td>
					</tr>
					<tr>
						<th>System Architecture</th>
						<td><?php echo $machine['architecture']; ?></td>
					</tr>
					<tr>
						<th>CPU Usage</th>
						<td><?php echo !empty($machine['cpu_usage']) ? '<meter class="memory-usage" value="'. (float)$machine['cpu_usage'] .'" max="100" min="0" high="30" low="10" optimum="5"></meter>' : '<em>n/a</em>'; ?></td>
					</tr>
					<tr>
						<th>Memory Usage</th>
						<td><?php echo !empty($machine['memory_usage']) ? '<meter class="memory-usage" value="'. (float)$machine['memory_usage'] .'" max="100" min="0" high="30" low="10" optimum="5"></meter>' : '<em>n/a</em>'; ?></td>
					</tr>
					<tr>
						<th>Uptime</th>
						<td><?php echo fallback($uptime, '<em>n/a</em>'); ?></td>
					</tr>
				</tbody>
			</table>

			<table class="table data-table">
				<thead>
					<tr>
						<th colspan="2">Web Server</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Daemon</th>
						<td><?php echo fallback($web_server['name'], '<em>Unknown</em>'); ?></td>
					</tr>
					<tr>
						<th>SAPI</th>
						<td><?php echo $web_server['sapi']; ?></td>
					</tr>
					<tr>
						<th>Current User</th>
						<td><?php echo $web_server['current_user']; ?></td>
					</tr>
					<tr>
						<th>Enabled Modules</th>
						<td><?php echo !empty($web_server['loaded_modules']) ? implode(', ', $web_server['loaded_modules']) : '<em>n/a</em>'; ?></td>
					</tr>
				</tbody>
			</table>

			<table class="table data-table">
				<thead>
					<tr>
						<th colspan="2">PHP</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Version</th>
						<td><?php echo $php['version']; ?></td>
					</tr>
					<tr>
						<th>Whoami</th>
						<td><?php echo fallback($php['whoami'], '<em>Unknown</em>'); ?></td>
					</tr>
					<tr>
						<th>PHP Extensions</th>
						<td style="columns: 100px auto;"><div><?php echo !empty($php['loaded_extensions']) ? implode('</div><div>', $php['loaded_extensions']) : '<em>None</em>'; ?></div></td>
					</tr>
					<tr>
						<th>Disabled PHP Functions</th>
						<td><?php echo !empty($php['disabled_functions']) ? implode(', ', $php['disabled_functions']) : '<em>None</em>'; ?></td>
					</tr>
					<tr>
						<th>Memory Limit</th>
						<td><?php echo fallback($php['memory_limit'], '<em>n/a</em>'); ?></td>
					</tr>
				</tbody>
			</table>

			<table class="table data-table">
				<thead>
					<tr>
						<th colspan="2">Database</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Server Name</th>
						<td><?php echo $database['name']; ?></td>
					</tr>
					<tr>
						<th>Client Library</th>
						<td><?php echo $database['library']; ?></td>
					</tr>
					<tr>
						<th>Hostname</th>
						<td><?php echo $database['hostname']; ?></td>
					</tr>
					<tr>
						<th>User</th>
						<td><?php echo $database['user']; ?></td>
					</tr>
					<tr>
						<th>Database</th>
						<td><?php echo $database['database']; ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div id="tab-php-ini" class="tab-content">
		<div class="card">
			<div class="card-header">
				<div class="card-title">
					<?php echo t('title_php_configuration', 'PHP Configuration'); ?>
				</div>
				<div>&nbsp;</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_input_search('filter', true, 'placeholder="'. t('title_filter', 'Filter') .'"'); ?>
			</div>

			<table id="php-config" class="table data-table">
				<thead>
					<tr>
						<th><?php echo t('title_setting', 'Setting'); ?></th>
						<th><?php echo t('title_value', 'Value'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($php['ini'] as $key => $value) { ?>
					<tr>
						<td><tt><?php echo functions::escape_html($key); ?></tt></td>
						<td><?php echo functions::escape_html($value); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<div id="tab-errors" class="tab-content">
		<div id="box-error-log" class="card">
			<div class="card-header">
				<div class="card-title">
					<?php echo t('title_error_log', 'Error Log'); ?>
				</div>
			</div>

			<?php echo functions::form_begin('errors_form', 'post'); ?>

			<table class="table data-table">
				<thead>
					<tr>
						<th><?php echo functions::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
						<th class="main"><?php echo t('title_error', 'Error'); ?></th>
						<th><?php echo t('title_occurrences', 'Occurrences'); ?></th>
						<th><?php echo t('title_last_occurrence', 'Last Occurrence'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($errors as $error) { ?>
					<tr>
						<td><?php echo functions::form_checkbox('errors[]', $error['error'], !empty($_POST['parse']) ? 'disabled' : ''); ?></td>
						<td style="white-space: normal;"><?php echo functions::escape_html($error['error']); ?><br>
							<div class="backtrace">
								<?php echo nl2br(functions::escape_html($error['backtrace'])); ?>
							</div>
						</td>
						<td class="text-center"><?php echo $error['occurrences']; ?></td>
						<td><?php echo functions::datetime_when($error['last_occurrence']); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<div class="card-body">
				<div id="actions">
					<?php echo functions::form_button_predefined('delete'); ?>
				</div>
			</div>

			<?php echo functions::form_end(); ?>
		</div>
	</div>
</div>

<script>
	<?php if (!empty($machine['cpu_usage']) || !empty($machine['memory_usage'])) { ?>
	setInterval(function() {
		$.ajax({
			cache: false,
			dataType: 'html',
			success: function(result) {
				var $cpu_usage = $('meter.cpu-usage', result);
				var $memory_usage = $('meter.memory-usage', result);
				$('meter.cpu-usage').replaceWith($cpu_usage)
				$('meter.memory-usage').replaceWith($memory_usage)
			},
		});
	}, 3000);
	<?php } ?>

	// Filter
	$('input[name="filter"]').on('input', function() {
		var filter = $(this).val().toLowerCase();
		$('#php-config tbody tr').each(function() {
			if ($(this).text().toLowerCase().indexOf(filter) !== -1) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	});

	// Checkbox toggle
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
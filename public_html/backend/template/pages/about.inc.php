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
	border-radius: var(--border-radius);
	overflow: hidden;
	transform: translateY(0%);
}
#box-about .credits {
	position: absolute;
	margin-top: 100%;
	animation: 30s credits linear infinite;
	/*animation-delay: 5s;*/
	animation-play-state: running;
}
#box-about .credits:hover {
	animation-play-state: paused;
}
@keyframes credits {
	from { transform: translateY(0%); }
  to { transform: translateY(-100%); }
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
	<a class="tab-item active" data-toggle="tab" href="#tab-system"><?php echo language::translate('title_system', 'System'); ?></a>
	<a class="tab-item" data-toggle="tab" href="#tab-errors"><?php echo language::translate('title_error_log', 'Error Log'); ?></a>
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
						<td><?php echo !empty($uptime) ? $uptime : '<em>n/a</em>'; ?></td>
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
						<td><?php echo !empty($web_server['name']) ? $web_server['name'] : '<em>Unknown</em>'; ?></td>
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
						<td><?php echo !empty($php['whoami']) ? $php['whoami'] : '<em>Unknown</em>'; ?></td>
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
						<td><?php echo !empty($php['memory_limit']) ? $php['memory_limit'] : '<em>n/a</em>'; ?></td>
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

			<div class="credits-wrapper">
				<div class="credits text-center">
					<h1><?php echo PLATFORM_NAME; ?> <?php echo PLATFORM_VERSION; ?></h1>
					<h3>No Nonsense Coding</h3>
					<p><em>Making as much sense as possible out of as little effort as possible.</em></p>
					# LiteCart contains work by the following third parties:
<pre style="white-space: pre-wrap;">
## Graphics

* (cart) T. Almroth - CC BY-SA 4.0 - https://www.tim-international.net/
* (wishlist) T. Almroth - CC BY-SA 4.0 - https://www.tim-international.net/
* (logotype) T. Almroth - CC BY-SA 4.0 - https://www.tim-international.net/
* (no-image/camera) T. Almroth - CC BY-SA 4.0 - https://www.tim-international.net/
* (newsletter) T. Almroth - CC BY-SA 4.0 - http://www.tim-international.net/

## Fonts

* Fira Sans - Google Fonts - Free for commercial use - https://www.googlefonts.com/
* Inter - Google Fonts - Free for commercial use - https://www.googlefonts.com/
* Reprobate (captcha) - Handwriting of Mike Sedillo - Free for commercial use - https://www.apostrophiclab.com/

## Font Icons

* Fontawesome - MIT License - https://www.fontawesome.io/
* Material Symbols - Apache License Version 2.0 - https://fonts.google.com/icons

## JavaScripts

* Bootstrap Carousel.js by Twitter - MIT License - https://getbootstrap.com/
* Bootstrap Collapse.js by Twitter - MIT License - https://getbootstrap.com/
* Chartist by Gion Kunz - MIT and WTFPL License - https://gionkunz.github.io/chartist-js/
* jQuery by jQuery Foundation - MIT License - https://www.jquery.com/
* Trumbowyg by Alex-D - MIT license - https://alex-d.github.io/Trumbowyg/
</pre>

				</div>
			</div>
		</div>
	</div>

	<div id="tab-php-ini" class="tab-content">
		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo language::translate('title_setting', 'Setting'); ?></th>
					<th><?php echo language::translate('title_value', 'Value'); ?></th>
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

	<div id="tab-errors" class="tab-content">
		<div id="box-error-log" class="card">
			<div class="card-header">
				<div class="card-title">
					<?php echo language::translate('title_error_log', 'Error Log'); ?>
				</div>
			</div>

			<?php echo functions::form_begin('errors_form', 'post'); ?>

			<table class="table data-table">
				<thead>
					<tr>
						<th><?php echo functions::draw_fonticon('icon-square-check checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
						<th class="main"><?php echo language::translate('title_error', 'Error'); ?></th>
						<th><?php echo language::translate('title_occurrences', 'Occurrences'); ?></th>
						<th><?php echo language::translate('title_last_occurrence', 'Last Occurrence'); ?></th>
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
					<?php echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger"', 'delete'); ?>
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

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>
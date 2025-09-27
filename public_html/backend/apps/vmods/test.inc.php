<?php

	$_GET['debug'] = true;
	$_GET['vmod'] = isset($_GET['vmod']) ? basename($_GET['vmod']) : null;

	document::$title[] = t('title_test_vmod', 'Test vMod');

	breadcrumbs::add(t('title_vmods', 'vMods'), document::ilink(__APP__.'/vmods'));
	breadcrumbs::add($_GET['vmod'], document::ilink(__APP__.'/view', ['vmod' => $_GET['vmod']]));
	breadcrumbs::add(t('title_test_vmod', 'Test vMod'), document::ilink());

	try {

		if (empty($_GET['vmod'])) {
			throw new Exception('No vmod provided');
		}

		$file = 'storage://vmods/' . $_GET['vmod'];

		if (!is_file($file)) {
			throw new Exception('The vmod does not exist');
		}

		$vmod = vmod::parse($file);

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	// Test results

	$result = [
		'name' => $vmod['name'],
		'files' => [],
	];

	foreach (array_keys($vmod['files']) as $key) {

		$glob_pattern = $vmod['files'][$key]['name'];

		// Apply path aliases
		if (!empty(vmod::$aliases)) {
			$glob_pattern = preg_replace(array_keys(vmod::$aliases), array_values(vmod::$aliases), $glob_pattern);
		}

		$result['pathfiles'][$glob_pattern] = [
			'pathfile' => $glob_pattern,
			'files' => [],
		];

		$files = glob(FS_DIR_APP . $glob_pattern, GLOB_BRACE);

		foreach ($files as $file) {

			$short_file = preg_replace('#^'. preg_quote(FS_DIR_APP, '#') .'#', '', $file);

			$result['pathfiles'][$glob_pattern]['files'][$short_file] = [
				'file' => $short_file,
				'operations' => [],
				'error' => '',
			];

			$buffer = file_get_contents($file);

			foreach ($vmod['files'][$key]['operations'] as $i => $operation) {

				try {

					$result['pathfiles'][$glob_pattern]['files'][$short_file]['operations'][$i] = [
						'error' => '',
					];

					$found = preg_match_all($operation['find']['pattern'], $buffer, $matches, PREG_OFFSET_CAPTURE);

					if (!$found) {
						switch ($operation['onerror']) {
							case 'ignore':
								continue 2;
							case 'abort':
							case 'warning':
							default:
								throw new Exception('Search not found', E_USER_WARNING);
								continue 2;
						}
					}

					if (!empty($operation['find']['indexes'])) {
						rsort($operation['find']['indexes']);

						foreach ($operation['find']['indexes'] as $index) {
							$index = $index - 1; // [0] is the 1st in computer language

							if ($found > $index) {
								$buffer = substr_replace($buffer, preg_replace($operation['find']['pattern'], $operation['insert'], $matches[0][$index][0]), $matches[0][$index][1], strlen($matches[0][$index][0]));
							}
						}

					} else {
						$buffer = preg_replace($operation['find']['pattern'], $operation['insert'], $buffer, -1, $count);

						if (!$count && $operation['onerror'] != 'skip') {
							throw new Exception('Failed to perform insert');
							continue;
						}
					}

				} catch (Exception $e) {
					$result['pathfiles'][$glob_pattern]['files'][$short_file]['operations'][$i]['error'] = $e->getMessage();
					$result['pathfiles'][$glob_pattern]['files'][$short_file]['error'] = 'Directive contains errors';
					$result['pathfiles'][$glob_pattern]['error'] = 'Directive contains errors';
				}
			}
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_test_vmod', 'Test vMod'); ?>
		</div>
	</div>

	<div class="card-body">
		<h2><?php echo functions::escape_html($vmod['name']); ?></h2>
	</div>

	<table class="table data-table">
		<thead>
			<tr>
				<th class="main"><?php echo t('title_file', 'File'); ?></th>
				<th><?php echo t('title_result', 'Result'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($result['pathfiles'] as $pathfile) { ?>
			<tr>
				<td>
					<h3><?php echo functions::escape_html($pathfile['pathfile']); ?></h3>
					<?php foreach ($pathfile['files'] as $file) { ?>
					<div><?php echo functions::escape_html($file['file']); ?> <?php echo empty($file['error']) ? functions::draw_fonticon('icon-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('icon-times', 'style="color: #c00;"'); ?></div>
					<ul>
						<?php foreach ($file['operations'] as $i => $operation) { ?>
						<li>Operation #<?php echo $i+1; ?> <?php echo empty($operation['error']) ? functions::draw_fonticon('icon-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('icon-times', 'style="color: #c00;"') .'<br>'. $operation['error']; ?></li>
						<?php } ?>
					</ul>
					<?php } ?>
				</td>
				<td style="font-size: 3em;">
					<?php echo empty($pathfile['error']) ? functions::draw_fonticon('icon-check', 'style="color: #7ccc00;"') : functions::draw_fonticon('icon-times', 'style="color: #c00;"'); ?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
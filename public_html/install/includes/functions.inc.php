<?php

	include __DIR__.'/../../includes/functions/func_file.inc.php';

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

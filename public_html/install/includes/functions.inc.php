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

					echo 'Copying '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source) .' to '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $target);

					if (file_xcopy($source, $target, $results)) {
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

						echo 'Performing custom actions on ' . preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $file) .'...<br>' . PHP_EOL;

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

					echo 'Deleting '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source);

					if (file_delete($source, $results)) {
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
				}

				break;

			case 'move':
			case 'rename':

				foreach ($payload as $source => $target) {

					if (defined('DISABLE_FILE_MIGRATIONS') && filter_var(DISABLE_FILE_MIGRATIONS, FILTER_VALIDATE_BOOLEAN)) {
						if (!preg_match('#^'. preg_quote(FS_DIR_STORAGE, '#') .'#', $source)) continue;
					}

					echo 'Moving '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source) .' to '. preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $target);

					if (file_move($source, $target, $results)) {
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

							echo 'Modifying ' . preg_replace('#^('. preg_quote(FS_DIR_STORAGE, '#') .'|'. preg_quote(FS_DIR_APP, '#') .')#', '', $source) .'...<br>' . PHP_EOL;

							$contents = file_get_contents($file);
							$contents = preg_replace('#(\r\n?|\n)#u', PHP_EOL, $contents);

							foreach ($operations as $i => $operation) {

								echo '  - Operation #'. $i+1;

								if (!empty($operation['regex'])) {
									$contents = preg_replace($operation['search'], $operation['replace'], $contents, -1, $count);
								} else {
									$contents = str_replace($operation['search'], $operation['replace'], $contents, $count);
								}

								if ($count) {
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
										'  Search: ' . $operation['search'],
										'  Replace: ' . $operation['replace'],
										'',
									]);
									exit;
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

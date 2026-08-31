<?php

	### Database > Tables > Demo Data #############################

	if (!empty($_REQUEST['demo_data'])) {

		try {

			echo '<p>Writing demo data... ' . PHP_EOL;

			## Import CSV Files

			if ($data_files = glob(__DIR__.'/../data/demo/*.csv')) {

				foreach ($data_files as $file) {

					$table = DB_TABLE_PREFIX . basename($file, '.csv');

					$contents = strtr(file_get_contents($file), [
						'{STORE_NAME}' => isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : '',
						'{STORE_EMAIL}' => isset($_REQUEST['store_email']) ? $_REQUEST['store_email'] : '',
						'{STORE_COUNTRY_CODE}' => isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '',
						'CURRENT_TIMESTAMP' => date('Y-m-d H:i:s'),
					]);

					$rows = f::csv_decode($contents);

					$column_names = array_keys($rows[0]);

					// Look up each column's nullability
					$column_nullable = [];
					foreach (database::query("show fields from ". $table)->fetch_all() as $field) {
						$column_nullable[$field['Field']] = ($field['Null'] === 'YES');
					}

					$query = "INSERT INTO `". database::input($table) ."` (`". implode('`, `', database::input($column_names)) ."`) VALUES ";

					foreach ($rows as $columns) {

						$values = [];

						foreach ($column_names as $index => $name) {

							$value = $columns[$index] ?? '';

							if ($value !== '') {
								$values[] = "'" . database::input($value) . "'";
								continue;
							}

							$values[] = !empty($column_nullable[$name]) ? 'NULL' : "''";
						}

						$query .= "(" . implode(', ', $values) . "),";
					}

					$query = rtrim($query, ',') . ";";

					echo basename($file) .'... ';
					database::query($query);

				}

				echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
			}

			## Import SQL Files

			$sql = file_get_contents(__DIR__.'/../data/demo/data.sql');

			if (!empty($sql)) {
				$sql = preg_replace('#\r\n?#', "\n", $sql);
				$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

				foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
					$query = preg_replace('#^-- .*?\R+#m', '', $query);
					database::query($query);
				}
			}

			echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} catch (Exception $e) {
			echo implode(PHP_EOL, [
				'<span class="error">[Error]</span>',
				'<div class="error-message">'. $e->getMessage() .'</div></p>',
				'',
				'',
			 ]);
		}
	}

	### Files > Demo Data #########################################

	if (!empty($_REQUEST['demo_data'])) {

		try {

			echo '<p>Copying demo files...</p>' . PHP_EOL;

			perform_action('copy', [
				__DIR__.'/../data/demo/storage/' => FS_DIR_STORAGE
			]);

			echo PHP_EOL;

		} catch (Exception $e) {
			echo implode(PHP_EOL, [
				'<p>Copy demo files... <span class="error">[Error]</span></p>',
				'<div class="error-message">'. $e->getMessage() .'</div>',
				'',
				'',
			]);
		}
	}

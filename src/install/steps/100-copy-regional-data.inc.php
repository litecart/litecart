<?php

	### Copy Regional Data #######################################

	if (!empty($_REQUEST['country_code'])) {

		$directories = f::file_search(__DIR__.'/../data/*'. $_REQUEST['country_code'] .'*/', GLOB_BRACE);

		if (!empty($directories)) {

			foreach ($directories as $dir) {
				$dir = basename($dir);

				if ($dir == 'demo') continue;
				if ($dir == 'default') continue;

				echo '<p>Patching installation with data from regional directory ' . $dir . '/...' . PHP_EOL;

				## Import CSV Files

				if ($data_files = f::file_search(__DIR__.'/../data/'. $dir .'/*.csv')) {

					echo '<p>Writing database table data from CSV files... ' . PHP_EOL;

					foreach ($data_files as $file) {

						try {

							echo '<p>' . basename($file) .' ';

							$table = DB_PREFIX . basename($file, '.csv');

							$contents = file_get_contents($file);

							foreach ([
								'{STORE_NAME}' => isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : '',
								'{STORE_EMAIL}' => isset($_REQUEST['store_email']) ? $_REQUEST['store_email'] : '',
								'{STORE_COUNTRY_CODE}' => isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '',
								'CURRENT_TIMESTAMP' => date('Y-m-d H:i:s'),
							] as $search => $replace) {
								$contents = str_replace($search, database::input($replace), $contents);
							}

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

								foreach ($column_names as $name) {

									$value = $columns[$name] ?? '';

									if ($value) {
										$values[] = "'" . strtr(database::input($value, true), ['\\\\n' => '\\n']) . "'";
									} else if ($column_nullable[$name]) {
										$values[] = 'NULL';
									} else {
										$values[] = "''";
									}
								}

								$query .= "(" . implode(', ', $values) . "),";
							}

							$query = rtrim($query, ',') . ";";

							database::query($query);

							echo '<span class="ok">✔</span>' . PHP_EOL;

						} catch (Throwable $t) {
							echo '<span class="error">𐄂</span>';
						}
					}

					echo '</p>' . PHP_EOL;
				}

				## Import SQL Files

				if ($data_files = f::file_search(__DIR__.'/../data/'. $dir .'/*.sql')) {

					echo '<p>Writing database table data from SQL files... ' . PHP_EOL;

					foreach ($data_files as $file) {

						if (empty(file_get_contents($file))) continue;

						try {

							echo ' '. basename($file) .' ';

							$sql = file_get_contents($file);

							$sql = preg_replace('#\r\n?#', "\n", $sql);
							$sql = str_replace('`lc_', '`'.DB_PREFIX, $sql);

							foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
								$query = preg_replace('#^-- .*?\R+#m', '', $query);
								database::query($query);
							}

							echo '<span class="ok">✔</span>';

						} catch (Throwable $t) {
							echo '<span class="error">[𐄂]</span>';
						}
					}

					echo '</p>' . PHP_EOL;
				}

				## Copy Files

				try {

					perform_action('copy', [
						__DIR__.'/../data/'. $dir .'/src/' => FS_DIR_APP,
						__DIR__.'/../data/'. $dir .'/storage/' => FS_DIR_STORAGE,
					]);

				} catch (Throwable $t) {
					echo implode(PHP_EOL, [
						'<p>Copy regional files ('. $dir .')... <span class="error">[Error]</span></p>',
						'<div class="error-message">'. $t->getMessage() .'</div>',
						'',
						'',
					]);
				}
			}
		}

		echo PHP_EOL;
	}

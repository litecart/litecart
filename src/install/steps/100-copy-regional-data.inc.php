<?php

	### Copy Regional Data #######################################

	if (!empty($_REQUEST['country_code'])) {

		$directories = f::file_search(__DIR__.'/../data/*'. $_REQUEST['country_code'] .'*/', GLOB_BRACE);

		if (!empty($directories)) {

			foreach ($directories as $dir) {
				$dir = basename($dir);

				if ($dir == 'demo') continue;
				if ($dir == 'default') continue;

				echo '<p>Patching installation with regional data from directory ' . $dir . '/...' . PHP_EOL;

				## Import CSV Files

				if ($data_files = f::file_search(__DIR__.'/../data/'. $dir .'/*.csv')) {

					echo '<p>Writing database table data from CSV files... ' . PHP_EOL;

					foreach ($data_files as $file) {

						$table = DB_TABLE_PREFIX . basename($file, '.csv');

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

						$query = "INSERT INTO `". database::input($table) ."` (`". implode('`, `', database::input(array_keys($rows[0]))) ."`) VALUES ";

						foreach ($rows as $columns) {
							$query .= "('". implode("', '", database::input($columns)) ."'),";
						}

						$query = rtrim($query, ',') . ";";

						echo 'Importing '. basename($file) .'... ';
						database::query($query);

						echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
					}

					echo PHP_EOL;
				}

				## Import SQL Files

				if ($data_files = f::file_search(__DIR__.'/../data/'. $dir .'/*.sql')) {

					echo '<p>Writing database table data from SQL files... ' . PHP_EOL;

					foreach ($data_files as $file) {
						$sql = file_get_contents($file);

						if (empty($sql)) continue;

						$sql = preg_replace('#\r\n?#', "\n", $sql);
						$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

						foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
							$query = preg_replace('#^-- .*?\R+#m', '', $query);
							database::query($query);
						}
					}
				}

				## Copy Files

				perform_action('copy', [
					__DIR__.'/../data/'. $dir .'/src/' => FS_DIR_APP,
					__DIR__.'/../data/'. $dir .'/storage/' => FS_DIR_STORAGE,
				]);
			}
		}

		echo PHP_EOL;
	}

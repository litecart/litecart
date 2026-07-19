<?php

	### Database > Tables > Demo Data #############################

	if (!empty($_REQUEST['demo_data'])) {
		echo '<p>Writing demo data... ' . PHP_EOL;

		## Import CSV Files

		if ($data_files = glob(__DIR__.'/../../data/demo/*.csv')) {

			foreach ($data_files as $file) {

				$table = DB_TABLE_PREFIX . basename($file, '.csv');

				$contents = file_get_contents($file);

				foreach ([
					'{STORE_NAME}' => isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : '',
					'{STORE_EMAIL}' => isset($_REQUEST['store_email']) ? $_REQUEST['store_email'] : '',
					'{STORE_COUNTRY_CODE}' => isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '',
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

		$sql = file_get_contents(__DIR__.'/../../data/demo/data.sql');

		if (!empty($sql)) {
			$sql = preg_replace('#\r\n?#', "\n", $sql);
			$sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

			foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
				$query = preg_replace('#^-- .*?\R+#m', '', $query);
				database::query($query);
			}
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	}

	### Files > Demo Data #########################################

	if (!empty($_REQUEST['demo_data'])) {
		echo '<p>Copying demo files...</p>' . PHP_EOL;

		perform_action('copy', [
			__DIR__.'/../../data/demo/storage/' => FS_DIR_STORAGE
		]);

		echo PHP_EOL;
	}

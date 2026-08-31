<?php

	### Run Migrations #####################################################

	$current_version = PLATFORM_DATABASE_VERSION;

	foreach ($supported_versions as $version) {

		if (version_compare(PLATFORM_DATABASE_VERSION, $version, '>=')) {
			continue;
		}

		try {

			if (version_compare($current_version, '3.0.0', '>=')) {
				database::query('start transaction;');
			}

			if (file_exists(__DIR__ . '/../../migrations/'. $version .'.sql')) {

				echo '<p>Upgrading database to '. $version .'...</p>' . PHP_EOL . PHP_EOL;

				$sql = file_get_contents(__DIR__ . '/../../migrations/'. $version .'.sql');
				$sql = str_replace('`lc_', '`'.DB_PREFIX, $sql);
				$sql = str_replace("'lc_", "'".DB_PREFIX, $sql);

				foreach (preg_split('#^-- -----*$#m', $sql, -1, PREG_SPLIT_NO_EMPTY) as $query) {
					$query = preg_replace('#^-- .*?\R+#m', '', $query);
					if (!empty($query)) {
						database::query($query);
					}
				}
			}

			if (file_exists(__DIR__ . '/../../migrations/'. $version .'.inc.php')) {
				echo '<p>Upgrading system to '. $version .'...</p>' . PHP_EOL . PHP_EOL;
				include(__DIR__ . '/../../migrations/'. $version .'.inc.php');
			}

			if (version_compare($current_version, '3.0.0', '>=')) {
				database::query('commit;');
			}

			echo '<p>Set platform database version... ';

			database::query(
				"update ". DB_PREFIX ."settings
				set `value` = '". database::input($version) ."'
				where `key` = 'platform_database_version'
				limit 1;"
			);

			echo '<strong>'. $version .'</strong></p>' . PHP_EOL . PHP_EOL;

		} catch (Throwable $t) {

			if (version_compare($current_version, '3.0.0', '>=')) {
				database::query('rollback;');
			}

			echo implode(PHP_EOL, [
				'<span class="error">[Error]</span>',
				'<div class="error-message">'. $t->getMessage() .'</div></p>',
				'',
				'',
			]);
		}

		$current_version = $version;
	}

<?php

	### Regional Data Patch #######################################

	if (!empty($_REQUEST['country_code'])) {
		echo '<p>Patching installation with regional data...' . PHP_EOL;

		$directories = f::file_search('data/*{'. $_REQUEST['country_code'] .',XX}*/', GLOB_BRACE);

		if (!empty($directories)) {
			foreach ($directories as $dir) {

				$dir = basename($dir);
				if ($dir == 'demo') continue;
				if ($dir == 'default') continue;

				foreach (glob('data/'. $dir .'/*.sql') as $file) {
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

			perform_action('copy', [
				"data/$dir/public_html/" => FS_DIR_APP,
				"data/$dir/storage/" => FS_DIR_STORAGE,
			]);
		}

		echo PHP_EOL;
	}

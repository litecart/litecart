<?php

	### Database > Tables > Demo Data #############################

	if (!empty($_REQUEST['demo_data'])) {
		echo '<p>Writing demo data... ';

		$sql = file_get_contents('data/demo/data.sql');

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

		perform_action('copy', ['data/demo/storage/' => FS_DIR_STORAGE]);

		echo PHP_EOL;
	}

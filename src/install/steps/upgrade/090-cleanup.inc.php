<?php

	### Reset Error Log ###################################################

	try {

		echo '<p>Reset error log... ';

		if (file_put_contents(FS_DIR_STORAGE . 'logs/errors.log', '') === false) {
			throw new Exception('Could not reset error log');
		}

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $t->getMessage() .'</div></p>',
			'',
			'',
		]);
	}

	### Clear Cache ########################################################

	try {

		echo '<p>Clear cache... ';

		database::query(
			"update ". DB_PREFIX ."settings
			set value = '1'
			where `key` = 'cache_clear'
			limit 1;"
		);

		perform_action('delete', [
			FS_DIR_STORAGE . 'vmods/.cache/*.php',
			FS_DIR_STORAGE . 'vmods/.cache/.checked',
			FS_DIR_STORAGE . 'vmods/.cache/.modifications',
		]);

		echo '<span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $t->getMessage() .'</div></p>',
			'',
			'',
		]);
	}

	### Ensure Storage Skeletons ###########################################

	try {

		echo '<p>Ensuring storage skeleton files... ';

		$copied = 0;

		foreach ([
			'logs/.htaccess',
		] as $file) {

			$dst = FS_DIR_STORAGE . $file;
			$src = __DIR__ . '/../../data/default/storage/' . $file;

			if (is_file($dst) || !is_file($src)) continue;

			$dst_dir = dirname($dst);
			if (!is_dir($dst_dir)) {
				mkdir($dst_dir, 0755, true);
			}

			if (copy($src, $dst)) $copied++;
		}

		echo implode(PHP_EOL, [
			($copied ? "$copied file(s) installed. " : 'Nothing to do. '),
			'<span class="ok">[OK]</span></p>',
			'',
			'',
		]);

	} catch (Throwable $t) {
		echo implode(PHP_EOL, [
			'<span class="error">[Error]</span>',
			'<div class="error-message">'. $t->getMessage() .'</div></p>',
			'',
			'',
		]);
	}

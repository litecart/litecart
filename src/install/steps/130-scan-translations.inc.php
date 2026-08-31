<?php

	### Scan translations #########################################

	try {

		echo "<p>Scanning installation for translations... ";

		$translations = [];

		$dir_iterator = new RecursiveDirectoryIterator(FS_DIR_APP);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $file) {
			if (!preg_match('#\.php$#', $file)) continue;
			if (preg_match('#vmods/.cache/#', $file)) continue;

			$pattern = '#'. implode(['language::translate\((?:(?!\$)', '(?:(__CLASS__)?\.)?', '(?:[\'"])([^\'"]+)(?:[\'"])', '(?:,?\s+(?:[\'"])([^\'"]+)?(?:[\'"]))?', '(?:,?\s+?(?:[\'"])([^\'"]+)?(?:[\'"]))?', ')\)']) .'#';

			if (!preg_match_all($pattern, file_get_contents($file), $matches)) continue;

			for ($i=0; $i<count($matches[0]); $i++) {
				if ($matches[1][$i]) {
					$code = substr(pathinfo($file, PATHINFO_BASENAME), 0, strpos(pathinfo($file, PATHINFO_BASENAME), '.')) . $matches[2][$i];
				} else {
					$code = $matches[2][$i];
				}
				$translations[strtolower($code)] = strtr($matches[3][$i], ["\\r" => "\r", "\\n" => "\n"]);
			}

		}

		foreach ($translations as $code => $translation) {
			database::query(
				"insert ignore into ". DB_TABLE_PREFIX ."translations
				(code, `text`, html, created_at)
				values ('". database::input($code) ."', '". database::input(json_encode(['en' => $translation], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true) ."', '". (($translation != strip_tags($translation)) ? 1 : 0) ."', '". date('Y-m-d H:i:s') ."');"
			);
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

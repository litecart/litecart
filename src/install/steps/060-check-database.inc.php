<?php

	### Database > Connection #####################################

	echo '<p>Connecting to database server on '. DB_SERVER .'... ';

	if (!extension_loaded('mysqli')) {
		throw new Exception(' <span class="error">[Error]</span> MySQLi is not installed or configured for PHP</p>' . PHP_EOL  . PHP_EOL);

	} else if (!database::connect('default', DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE, 'utf8')) {
		throw new Exception(' <span class="error">[Error]</span> Unable to connect</p>' . PHP_EOL  . PHP_EOL);

	} else {
		echo 'Connected! <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	}

	### Database > Check Version ##################################

	$database_software = database::query(
		"SELECT VERSION();"
	)->fetch(function($row) use ($requirements) {
		if (preg_match('#mariadb#i', $row['VERSION()'])) {
			return [
				'name' => 'MariaDB',
				'version' => strtok($row['VERSION()'], '-'),
				'min_version' => $requirements['database']['mariadb']['minimumVersion'],
				'recommended_version' => $requirements['database']['mariadb']['recommendedVersion'],
			];
		}
		return [
			'name' => 'MySQL',
			'version' => $row['VERSION()'],
			'min_version' => $requirements['database']['mysql']['minimumVersion'],
			'recommended_version' => $requirements['database']['mysql']['recommendedVersion'],
		];
	});

	echo $database_software['name'] .' '. $database_software['version'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

	echo '<p>Checking database software... ';

	if (version_compare($database_software['version'], $database_software['min_version'], '<')) {
		throw new Exception($database_software['name'] .' '. $database_software['version'] . ' <span class="error">[Error] '.  $database_software['name'] .' '. $database_software['min_version'] .'+ required</span></p>');

	} else if (version_compare($database_software['version'], $database_software['recommended_version'], '<')) {
		echo $database_software['name'] .' '. $database_software['version'] .' <span class="ok">[OK]</span><br>'
			. '<span class="warning">'. $database_software['name'] .' '. $database_software['recommended_version'] .'+ recommended</span></span></p>';

	} else {
		echo $database_software['name'] .' '. $database_software['version'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	}

	### Database > Check Charset ##################################

	echo '<p>Checking database default character set... ';

	$charset = database::query(
		"select DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME from information_schema.SCHEMATA
		where schema_name = '". database::input(DB_DATABASE) ."'
		limit 1;"
	)->fetch();

	if (!$charset) {
		throw new Exception(' <span class="error">[Error] Failed to retrieve character set</span></p>');
	}

	if (strtok($charset['DEFAULT_CHARACTER_SET_NAME'], '_') != strtok($_REQUEST['db_collation'], '_')) {

		if (!empty($_REQUEST['set_default_collation'])) {

			database::query(
				"ALTER DATABASE `". DB_DATABASE ."`
				CHARACTER SET ". strtok($_REQUEST['db_collation'], '_') ." COLLATE ". $_REQUEST['db_collation'] .";"
			);

			echo 'Setting '. strtok($_REQUEST['db_collation'], '_') . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo $charset['DEFAULT_CHARACTER_SET_NAME'] . ' <span class="warning">[Warning]</span> The database default charset is not \''. strtok($_REQUEST['db_collation'], '_') .'\' and you might experience problems with mixed character sets in the future. Try performing the following MySQL/MariaDB query: "ALTER DATABASE `'. DB_DATABASE .'` CHARACTER SET '. strtok($_REQUEST['db_collation'], '_') .' COLLATE '. $_REQUEST['db_collation'] .';"</p>';
		}

	} else {
		echo $charset['DEFAULT_CHARACTER_SET_NAME'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	}

	echo '<p>Checking database default collation... ';

	if ($charset['DEFAULT_COLLATION_NAME'] != $_REQUEST['db_collation']) {

		if (!empty($_REQUEST['set_default_collation'])) {

			database::query(
				"ALTER DATABASE `". DB_DATABASE ."`
				CHARACTER SET ". strtok($_REQUEST['db_collation'], '_') ." COLLATE ". $_REQUEST['db_collation'] .";"
			);

			echo 'Setting '. $_REQUEST['db_collation'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;

		} else {
			echo $charset['DEFAULT_COLLATION_NAME'] . ' <span class="warning">[Warning]</span> The database default collation is not \''. $_REQUEST['db_collation'] .'\' and you might experience future trouble with mixed collations. Try performing the following MySQL query: "ALTER DATABASE `'. DB_DATABASE .'` CHARACTER SET '. strtok($_REQUEST['db_collation'], '_') .' COLLATE '. $_REQUEST['db_collation'] .';"</p>';
		}

	} else {
		echo $charset['DEFAULT_COLLATION_NAME'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL . PHP_EOL;
	}

<?php

	try {

		// Simple AJAX endpoint to read/update a single cell in a table

		if (empty($_POST['table']) || empty($_POST['primary_column']) || (!isset($_POST['pkv'])) || empty($_POST['column'])) {
			throw new Exception('Missing parameters', 400);
		}

		// Validate table exists
		if (!database::query(
			"SHOW TABLES LIKE '". database::input($_POST['table']) ."'"
		)->num_rows) {
			throw new Exception('Invalid table', 400);
		}

		// Get column metadata
		$column_info = database::query(
			"SHOW FULL COLUMNS FROM `". database::input($_POST['table']) ."`
			WHERE `Field` = '". database::input($_POST['column']) ."'
			LIMIT 1;"
		)->fetch(function($column) {
			return [
				'name' => $column['Field'],
				'type' => $column['Type'],
				'length' => preg_match('#\((.*?)\)#', $column['Type'], $matches) ? $matches[1] : '',
				'null' => preg_match('#^yes$#i', $column['Null']) ? true : false,
				'unsigned' => preg_match('#^unsigned$#i', $column['Type']) ? true : false,
				'zerofill' => preg_match('#^zerofill$#i', $column['Type']) ? true : false,
				'primary' => preg_match('#^pri$#i', $column['Key']) ? true : false,
				'key' => $column['Key'],
				'default' => $column['Default'],
				'auto_increment' => preg_match('#auto_increment#i', $column['Extra']) ? true : false,
				'collation' => $column['Collation'],
				'comment' => $column['Comment'],
			];
		});

		if (!$column_info) {
			throw new Exception('Invalid column', 400);
		}

		// Determine SQL value and validation
		$is_null = false;
		if ($value === '' && $column_info['null']) {
			$is_null = true;
		}

		if (!$is_null) {

			// Determine value based on column type (switch for clarity)
			switch (true) {

				case preg_match('#^tinyint\(1\)#i', $column_info['type']):
					$val = ($value === '1' || strtolower($value) === 'true' || $value === 'on') ? 1 : 0;
					break;

				case preg_match('#int|tinyint|smallint|mediumint|bigint#i', $column_info['type']):
					if ($value === null || $value === '') {
						$val = 0;
					} else if (!is_numeric($value)) {
						throw new Exception('Invalid integer value', 400);
					} else {
						$val = (int)$value;
					}
					if ($column_info['unsigned'] && $val < 0) {
						throw new Exception('Negative value not allowed for unsigned column', 400);
					}
					break;

				case preg_match('#decimal|float|double#i', $column_info['type']):
					if ($value === null || $value === '') {
						$val = 0;
					} else if (!is_numeric($value)) {
						throw new Exception('Invalid numeric value', 400);
					} else {
						$val = (string)$value;
					}
					if ($column_info['unsigned'] && (float)$val < 0) {
						throw new Exception('Negative value not allowed for unsigned column', 400);
					}
					break;

				default:
					$val = $value;
					break;
			}
		}

		// Build and run update
		database::query(
			"update `". database::input($_POST['table']) ."`
			set `". database::input($_POST['column']) ."` = ". ($is_null ? 'NULL' : "'". database::input($val) ."'") ."
			where `". database::input($_POST['primary_column']) ."` = '". database::input($_POST['pkv']) ."'
			limit 1;"
		);

		if (!database::affected_rows()) {

			throw new Exception("update `". database::input($_POST['table']) ."`
			set `". database::input($_POST['column']) ."` = ". ($is_null ? 'NULL' : "'". database::input($val) ."'") ."
			where `". database::input($_POST['primary_column']) ."` = '". database::input($_POST['pkv']) ."'
			limit 1;", 500);
			throw new Exception('No rows affected', 500);
		}

		// Prepare display value
		if ($is_null) {
			$display = '<em>NULL</em>';
		} else {
			$display = $val;

			// Zerofill handling for integer types
			if ($column_info['zerofill'] && preg_match('#^\d+#', $column_info['type'])) {
				$width = (int)preg_replace('#,.*$#', '', $column_info['type']);
				if (is_numeric($display)) {
					$display = str_pad((string)$display, $width, '0', STR_PAD_LEFT);
				}
			}

			$display = f::escape_html($display);
		}

		$result = [
			'success' => true,
			'value' => $display
		];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json; charset='. mb_http_output());
	echo f::format_json($result);
	exit;

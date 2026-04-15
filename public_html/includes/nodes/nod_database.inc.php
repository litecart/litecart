<?php

	/*
	* PHP wrapper for MariaDB/MySQL.
	* Procedural style as performance seems a bit faster than object-oriented
	*/

	class database {

		public static $links = [];
		public static $stats = [
			'duration' => 0,
			'queries' => 0,
		];

		public static function init() {
			event::register('shutdown', [__CLASS__, 'disconnect']);
		}

		public static function connect($link='default', $server=DB_SERVER, $username=DB_USERNAME, $password=DB_PASSWORD, $database=DB_DATABASE, $charset='utf8mb4') {

			if (!isset(self::$links[$link])) {

				$timestamp = microtime(true);

				mysqli_report(MYSQLI_REPORT_OFF);

				self::$links[$link] = mysqli_init();

				if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
					self::set_option(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1, $link);
				} else {
					trigger_error('Undefined constant MYSQLI_OPT_INT_AND_FLOAT_NATIVE', E_USER_WARNING);
				}

				if (!mysqli_real_connect(self::$links[$link], $server, $username, $password, $database)) {
					throw new Error('Could not connect to database: '. mysqli_connect_errno() .' - '. mysqli_connect_error());
				}

				if (($duration = microtime(true) - $timestamp) > 1 && in_array('storage', stream_get_wrappers())) {
					error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL connection established in '. number_format($duration, 3, '.', ' ') .' s.' . PHP_EOL, 3, 'storage://logs/performance.log');
				}

				self::$stats['duration'] += $duration;
			}

			if (!is_object(self::$links[$link])) {
				throw new Error('Invalid database link');
			}

			if (!empty($charset)) {
				self::set_charset($charset, $link);
			}

			$sql_modes = self::query("select @@SESSION.sql_mode as sql_mode;", $link)->fetch(function($row){
				return preg_split('#\s*,\s*#', $row['sql_mode'], -1, PREG_SPLIT_NO_EMPTY);
			});

			// Remove some undesired SQL modes
			foreach ([
				'TRADITIONAL',         // Shortcut flag for a bunch of other flags like below
				'STRICT_ALL_TABLES',   // Strict mode [MySQL 5.7+, MariaDB 10.2.4+]
				'STRICT_TRANS_TABLES', // Strict mode [MySQL 5.7+, MariaDB 10.2.4+]
				'ONLY_FULL_GROUP_BY',  // Requiring an undesired amount of columns in group by clause to conform with indexes [MySQL 5.7+]
			] as $undesired_mode) {
				if (($key = array_search($undesired_mode, $sql_modes)) !== false) {
					unset($sql_modes[$key]);
				}
			}

			self::query("SET SESSION sql_mode = '". database::input(implode(',', $sql_modes)) ."';", $link);
			self::query("SET names '". database::input($charset) ."';", $link);
			self::query("SET SESSION default_storage_engine = InnoDB;", $link);

			return self::$links[$link];
		}

		public static function server_info($link='default') {

			if (!$result = mysqli_get_server_info(self::$links[$link])) {
				trigger_error('Could not get server info for MySQL connection: '. mysqli_errno(self::$links[$link]) .' - '. mysqli_error(self::$links[$link]), E_USER_WARNING);
				return false;
			}

			return $result;
		}

		public static function set_charset($charset, $link='default') {

			if (!$result = mysqli_set_charset(self::$links[$link], $charset)) {
				trigger_error('Could not set charset for MySQL connection: '. mysqli_errno(self::$links[$link]) .' - '. mysqli_error(self::$links[$link]), E_USER_WARNING);
			}

			return true;
		}

		public static function set_option($option, $value, $link='default') {

			if (!$result = mysqli_options(self::$links[$link], $option, $value)) {
				trigger_error('Could not set option '. $option .' to '. $value, E_USER_WARNING);
			}

			return true;
		}

		public static function disconnect($link=null) {

			if ($link) {
				$links = [$link => self::$links[$link]];
			} else {
				$links = self::$links;
			}

			$errors = false;

			foreach (array_keys($links) as $name) {
				if (!isset($links[$name]) || !is_object($links[$name])) {
					$errors = true;
					continue;
				}

				mysqli_close($links[$name]);
				unset(self::$links[$name]);
			}

			return $errors;
		}

		public static function query($sql, $link='default') {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			$timestamp = microtime(true);

			$result = mysqli_query(self::$links[$link], $sql);

			if ($result === false) {

				$ref = uniqid('db_');

				if (in_array('storage', stream_get_wrappers())) {
					error_log('['. date('Y-m-d H:i:s e') .']['. $ref .'] MySQL Error: '. mysqli_errno(self::$links[$link]) .' - '. mysqli_error(self::$links[$link]) .' | Query: '. $sql . PHP_EOL, 3, 'storage://logs/errors.log');
				}

				throw new Error('MySQL Error: ' . mysqli_errno(self::$links[$link]) .' - '. preg_replace('#\s+#', ' ', mysqli_error(self::$links[$link])) .' (Ref: '. $ref .')');
			}

			if (($duration = microtime(true) - $timestamp) > 5 && in_array('storage', stream_get_wrappers())) {
				error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 5, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $sql) . PHP_EOL, 3, 'storage://logs/performance.log');
			}

			self::$stats['queries']++;
			self::$stats['duration'] += $duration;

			if ($result instanceof mysqli_result) {
				return new database_statement($sql, $link, $result);
			}

			return $result;
		}

		public static function multi_query($sql, $link='default') {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			$timestamp = microtime(true);

			if (mysqli_multi_query(self::$links[$link], $sql) === false) {
				$ref = uniqid('db_');
				if (in_array('storage', stream_get_wrappers())) {
					error_log('['. date('Y-m-d H:i:s e') .']['. $ref .'] MySQL Error: '. mysqli_errno(self::$links[$link]) .' - '. mysqli_error(self::$links[$link]) .' | Query: '. $sql . PHP_EOL, 3, 'storage://logs/errors.log');
				}
				throw new Error('MySQL Error: ' . mysqli_errno(self::$links[$link]) .' - '. preg_replace('#\r#', ' ', mysqli_error(self::$links[$link])) .' (Ref: '. $ref .')');
			}

			$results = [];

			do {
				if ($result = mysqli_store_result(self::$links[$link])) {
					$results[] = new database_statement($sql, $link, $result);
				}
			} while (mysqli_next_result(self::$links[$link]));

			self::$stats['queries']++;
			self::$stats['duration'] += microtime(true) - $timestamp;

			return $results;
		}

		public static function prepare($sql, $link='default') {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			$timestamp = microtime(true);

			$result = new database_statement($sql, $link);

			if (($duration = microtime(true) - $timestamp) > 5 && in_array('storage', stream_get_wrappers())) {
				error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 5, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $sql) . PHP_EOL, 3, 'storage://logs/performance.log');
			}

			self::$stats['duration'] += $duration;

			return $result;
		}

		public static function fetch($result, $column='') {
			return $result->fetch($column);
		}

		public static function fetch_all($result, $column=null, $index_column=null) {
			return $result->fetch_all($column, $index_column);
		}

		public static function seek($result, $offset) {
			return $result->seek($offset);
		}

		public static function num_rows($result) {
			return $result->num_rows;
		}

		public static function free($result) {
			return $result->free();
		}

		public static function insert_id($link='default') {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_insert_id(self::$links[$link]);
		}

		public static function affected_rows($link='default') {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_affected_rows(self::$links[$link]);
		}

		public static function info($link='default') {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			return mysqli_info(self::$links[$link]);
		}

		public static function begin_transaction($link='default') {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			return mysqli_begin_transaction(self::$links[$link]);
		}

		public static function commit($link='default') {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_commit(self::$links[$link]);
		}

		public static function rollback($link='default') {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_rollback(self::$links[$link]);
		}

		public static function create_variable($field, $value=null) {

			if (!$field) {
				return null;
			}

			if (is_string($field)) {
				$field = [
					'Type' => $field,
					'Default' => null,
				];
			}

			if (!empty($field['Default'])) {
				switch (true) {

					case (preg_match('#^null$#i', $field['Default'])):
						return null;

					case (preg_match('#^\'\'$#', $field['Default'])):
						return '';

					case (preg_match('#^\{\}$#', $field['Default'])):
					case (preg_match('#^\[\]$#', $field['Default'])):
						return [];

					case (preg_match('#^now(\(\))?$#i', $field['Default'])):
					case (preg_match('#^current_timestamp(\(\))?$#i', $field['Default'])):
						return date('Y-m-d H:i:s');

					case (preg_match('#^current_date(\(\))?$#i', $field['Default'])):
						return date('Y-m-d');

					case (preg_match('#^current_time(\(\))?$#i', $field['Default'])):
						return date('H:i:s');

					case (isset($field['Comment']) && preg_match('#TYPE:JSON$#i', $field['Comment'])): // Requires "show full columns" data
					case (isset($field['Comment']) && preg_match('#TYPE:ARRAY$#i', $field['Comment'])): // Requires "show full columns" data
						return [];
				}
			}

			switch (true) {
				case (preg_match('#^(bit|int|tinyint|smallint|mediumint|bigint)#i', $field['Type'])):
					return intval(!is_null($value) ? $value : $field['Default']);

				case (preg_match('#^(decimal|double|float)#i', $field['Type'])):
					return floatval(!is_null($value) ? $value : $field['Default']);

				default:
					return strval(!is_null($value) ? $value : $field['Default']);
			}
		}

		public static function input($input, $allowable_tags=false, $trim=true, $link='default') {

			if (is_array($input)) {
				foreach (array_keys($input) as $key) {
					$input[$key] = self::input($input[$key], $allowable_tags, $trim, $link);
				}
				return $input;
			}

			if ($input == '') {
				return '';
			}

			if (in_array(gettype($input), ['null', 'boolean', 'double', 'integer', 'float'])) {
				return $input;
			}

			if ($allowable_tags !== true) {
				if ($allowable_tags != '') {
					$input = strip_tags($input, $allowable_tags);
				} else {
					$input = strip_tags($input);
				}
			}

			if ($trim === true) {
				$input = trim($input);
			} else if ($trim != '') {
				$input = trim($input, $trim);
			}

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			return mysqli_real_escape_string(self::$links[$link], $input);
		}

		public static function input_fulltext($input, $allowable_tags=false, $trim=true, $link='default') {
			$input = self::input($input, $allowable_tags, $trim, $link);
			$input = preg_replace('#[+\-<>\(\)~*\"@; ]+#', ' ', $input);
			return $input;
		}

		public static function input_like($input, $allowable_tags=false, $trim=true, $link='default') {
			$input = self::input($input, $allowable_tags, $trim, $link);
			$input = addcslashes($input, '%_');
			return $input;
		}
	}

	class database_statement {

		private $_result;
		private $_statement;
		private $_link;

		public function __construct($statement, $link = 'default', $result = null) {
			$this->_statement = $statement;
			$this->_link = $link;
			$this->_result = $result;
		}

		public function __call($method, $arguments) {
			return call_user_func_array([$this->_result, $method], $arguments);
		}

		// Magic getters for properties of mysqli_result
		public function __get($name) {

			if (!$this->_result) {
				$this->execute();
			}

			return $this->_result->$name;
		}

		public function __set($name, $value) {
			// Do nothing
		}

		public function bind(...$args) {

			if (!$this->_statement) {
				trigger_error('No prepared statement available for binding parameters', E_USER_WARNING);
				return $this;
			}

			$sql = $this->_statement;

			foreach ($args as $nth => $params) {

				if (!is_array($params) || array_is_list($params)) {
					trigger_error('Invalid bind arguments format (expecting associative array)', E_USER_WARNING);
					continue;
				}

				// Flatten the parameters
				$flattened = f::array_flatten($params, '.');

				// Step through each character in the query
				for ($i = 0; $i < strlen($sql); $i++) {

					// Skip over a value clause
					if (preg_match("#[`']s#", $sql[$i]) && $sql[$i - 1] != "\\") {
						$value_wrapper = $sql[$i];
						for ($n = $i + 1; $n < strlen($sql); $n++) {
							if ($sql[$n] == $value_wrapper && $sql[$n - 1] != "\\") break;
						}
						$i = $n;

						// Restart at cursor position
						$i--;
						continue;
					}

					// Remove #comments
					if ($sql[$i] == '#' && ($i == 0 || $sql[$i-1] != "\\")) {
						for ($n = $i + 1; $n < strlen($sql); $n++) {
							if (($sql[$n] == "\r" || $sql[$n] == "\n") && $sql[$n - 1] != "\\") {
								if ($sql[$n] == "\r" && $sql[$n+1] == "\n") $n++; // Windows CRLF
								break;
							}
						}
						$sql = substr($sql, 0, $i) . substr($sql, $n + 1);

						// Restart at cursor position
						$i--;
						continue;
					}

					// Remove -- comments
					if ($sql[$i] == '-' && $sql[$i+1] == '-' && $sql[$i+2] == ' ') {

						// Find end of line
						for ($n = $i + 3; $n < strlen($sql); $n++) {
							if ($sql[$n] == "\r" || $sql[$n] == "\n") {
								if ($sql[$n] == "\r" && $sql[$n+1] == "\n") $n++; // Windows CRLF
								break;
							}
						}

						// Commit replacement
						$sql = substr($sql, 0, $i) . substr($sql, $n + 1);

						// Restart at cursor position
						$i--;
						continue;
					}

					// Remove /* comments */
					if ($sql[$i] == '/' && $sql[$i+1] == '*') {

						// Find end of comment
						for ($n = $i + 2; $n < strlen($sql); $n++) {
							if ($sql[$n] == '/' && $sql[$n-1] == '*') break;
						}

						// Commit replacement
						$sql = substr($sql, 0, $i) . substr($sql, $n + 1);

						// Restart at cursor position
						$i--;
						continue;
					}

					// Process a detected parameter placeholder
					if ($sql[$i] == ':') {

						// Find end of parameter
						for ($n = $i + 1; $n < strlen($sql); $n++) {
							if (in_array($sql[$n], [' ', ';', "\r", "\n"]) || $i == strlen($sql)) break;
						}

						// Extract parameter name
						$param = substr($sql, $i+1, $n-1 - $i);

						// Match parameter name with input parameter
						if (isset($flattened[$param])) {
							switch (gettype($flattened[$param])) {

								case 'integer':
								case 'bool':
									$value = (int)$flattened[$param];
									break;

								case 'double':
									$value = (float)$flattened[$param];
									break;

								case 'string':
									$value = "'". database::input($flattened[$param]) ."'";
									break;

								default:
									trigger_error('Unsupported parameter type ('. gettype($flattened[$param]) .')', E_USER_WARNING);
									continue 2;
							}

						} else {
							trigger_error('Unmatched parameter name ('. $param .')', E_USER_WARNING);
							continue;
						}

						// Commit replacement
						$sql = substr($sql, 0, $i) . $value . substr($sql, $n + 1);

						// Move cursor to end of parameter
						$i = $i + strlen($value);
					}
				}
			}

			$this->_statement = $sql;
			$this->_result = null;

			return $this;
		}

		public function execute() {

			if (!$this->_statement) {
				trigger_error('No prepared statement available for execution', E_USER_WARNING);
				return false;
			}

			$timestamp = microtime(true);

			$this->_result = mysqli_query(database::$links[$this->_link], $this->_statement);

			if ($this->_result === false) {

				$ref = uniqid('db_');

				if (in_array('storage', stream_get_wrappers())) {
					error_log('['. date('Y-m-d H:i:s e') .']['. $ref .'] MySQL Error: '. mysqli_errno(database::$links[$link]) .' - '. mysqli_error(database::$links[$link]) .' | Query: '. $sql . PHP_EOL, 3, 'storage://logs/errors.log');
				}

				throw new Error('MySQL Error: ' . mysqli_errno(database::$links[$link]) .' - '. preg_replace('#\s+#', ' ', mysqli_error(database::$links[$link])) .' (ref: '. $ref .')');
			}

			if (($duration = microtime(true) - $timestamp) > 5 && in_array('storage', stream_get_wrappers())) {
				error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 5, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $this->_statement) . PHP_EOL, 3, 'storage://logs/performance.log');
			}

			database::$stats['queries']++;
			database::$stats['duration'] += $duration;

			return $this;
		}

		public function export(&$object) {
			return $object = $this;
		}

		public function fields() {
			return array_column(mysqli_fetch_fields($this->_result), 'name');
		}

		public function fetch($filter=null) {

			if (!$this->_result) {
				$this->execute();
			}

			$timestamp = microtime(true);

			$row = mysqli_fetch_assoc($this->_result);

			if ($row !== null && $filter) {
				switch (gettype($filter)) {

					case 'array':
						$result = array_intersect_key($row, array_flip($filter));
						break;

					case 'string':
						if (isset($row[$filter])) {
							$result = $row[$filter];
						} else {
							$result = false;
						}
						break;

					case 'object':
						$result = call_user_func_array($filter, [&$row]);
						if ($result === null) { // Was no result returned?
							$result = $row;
						}
						break;

					default:
						$row = false;
						break;
				}

			} else {
				$result = $row;
			}

			database::$stats['duration'] += microtime(true) - $timestamp;

			return $result;
		}

		public function fetch_all($filter=null, $index_column=null) {

			if (!$this->_result) {
				$this->execute();
			}

			$timestamp = microtime(true);

			if ($filter || $index_column) {

				$rows = [];

				while ($row = mysqli_fetch_assoc($this->_result)) {

					if ($filter) {

						switch (gettype($filter)) {

							case 'array':
								$result = array_intersect_key($row, array_flip($filter));
								break;

							case 'string':
								if (isset($row[$filter])) {
									$result = $row[$filter];
								} else {
									$result = false;
								}
								break;

							case 'object':
								$result = call_user_func_array($filter, [&$row]);
								if ($result === null) { // Was no result returned?
									$result = $row;
								}
								break;

							default:
								$result = false;
						}

					} else {
						$result = $row;
					}

					if (empty($result) && !is_numeric($result)) {
						continue;
					}

					if ($index_column) {

						if (isset($row[$index_column])) {
							$rows[$row[$index_column]] = $result;
						} else {
							trigger_error('Index column not found in row ('. $index_column .')', E_USER_WARNING);
							$rows[] = false;
						}

					} else {
						$rows[] = $result;
					}
				}

			} else {
				$rows = mysqli_fetch_all($this->_result, MYSQLI_ASSOC);
			}

			database::$stats['duration'] += microtime(true) - $timestamp;

			return $rows;
		}

		public function fetch_page($filter=null, $index_column=null, &$page=1, $items_per_page=null, &$num_rows=null, &$num_pages=null) {

			$timestamp = microtime(true);

			if (empty($page) || !is_numeric($page) || $page < 1) {
				$page = 1;
			}

			if (!$items_per_page) {
				$items_per_page = settings::get('data_table_rows_per_page');
			}

			$rows = [];

			// If original query is available and looks like a SELECT, use COUNT + LIMIT/OFFSET
			if (!$this->_result && $this->_statement && preg_match('#^\s*SELECT\b#i', $this->_statement)) {

				// Strip trailing semicolon
				$query = preg_replace('#\s*;\s*$#', '', $this->_statement);

				// Remove any existing LIMIT clause to get accurate count
				$query = preg_replace('#\s+LIMIT\s+\d+(\s*,\s*\d+)?\s*$#i', '', $query);

				// Count total rows by wrapping the sanitized query
				$num_rows = database::query(
					"SELECT COUNT(*) AS c FROM (
						". $query ."
					) AS _count", $this->_link
				)->fetch('c');

				$num_pages = ($items_per_page > 0) ? ceil($num_rows / $items_per_page) : 0;

				$offset = ($page - 1) * $items_per_page;

				database::$stats['duration'] += microtime(true) - $timestamp;

				if ($offset < $num_rows) {
					$paged_sql = $query . ' LIMIT ' . (int)$offset . ', ' . (int)$items_per_page;
					$rows = database::query($paged_sql, $this->_link)->fetch_all($filter, $index_column);
				}

				return $rows;
			}

			if (!$this->_result) {
				$this->execute();
			}

			$num_rows = mysqli_num_rows($this->_result);
			$num_pages = ceil($num_rows / $items_per_page);
			$pointer = ((int)$page -1) * $items_per_page;

			if ($pointer < $num_rows) {

				mysqli_data_seek($this->_result, $pointer);

				for ($i=0; $i < $items_per_page; $i++) {

					$row = $this->fetch($filter, $index_column);
					$pointer++;

					if (!empty($row) || !is_numeric($row)) {
						$rows[] = $row;
					}

					if ($pointer == $num_rows) {
						break; // We reached the end of the result set
					}
				}
			}

			database::$stats['duration'] += microtime(true) - $timestamp;

			return $rows;
		}

		public function each(callable $function) {
			while ($row = $this->fetch()) {
				call_user_func($function, $row);
			}
		}

		public function seek($offset) {
			mysqli_data_seek($this->_result, $offset);
			return $this;
		}

		public function num_rows() {
			return mysqli_num_rows($this->_result);
		}

		public function free() {
			if ($this->_result) {
				return mysqli_free_result($this->_result);
			}
			return true;
		}

		public function __destruct() {
			$this->free();
		}
	}

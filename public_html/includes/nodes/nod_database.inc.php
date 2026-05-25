<?php

	/*
		PHP wrapper for MariaDB/MySQL.
		Procedural style as performance seems a bit faster than object-oriented
	*/

	class database {

		public static $links = [];
		public static $stats = [
			'duration' => 0,
			'queries' => 0,
		];

		public static function init(): void {
			event::register('shutdown', [__CLASS__, 'disconnect']);
		}

		public static function connect(string $link='default', string $server=DB_SERVER, string $username=DB_USERNAME, string $password=DB_PASSWORD, string $database=DB_DATABASE, string $charset='utf8mb4'): mysqli {

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

			// Set connection charset
			self::query("SET names '". database::input($charset) ."';", $link);

			// Set default storage engine
			self::query("SET SESSION default_storage_engine = InnoDB;", $link);

			// Set time zone for current session
			if ($timezone = ini_get('date.timezone')) {
				$datetime = new \DateTime('now', new \DateTimezone($timezone));
				self::query("SET time_zone = '". database::input($datetime->format('P')) ."';", $link);
			}

			return self::$links[$link];
		}

		public static function server_info(string $link='default'): string|false {

			if (!$result = mysqli_get_server_info(self::$links[$link])) {
				trigger_error('Could not get server info for MySQL connection: '. mysqli_errno(self::$links[$link]) .' - '. mysqli_error(self::$links[$link]), E_USER_WARNING);
				return false;
			}

			return $result;
		}

		public static function set_charset(string $charset, string $link='default'): bool {

			if (!$result = mysqli_set_charset(self::$links[$link], $charset)) {
				trigger_error('Could not set charset for MySQL connection: '. mysqli_errno(self::$links[$link]) .' - '. mysqli_error(self::$links[$link]), E_USER_WARNING);
			}

			return true;
		}

		public static function set_option(int $option, $value, string $link='default'): bool {

			if (!$result = mysqli_options(self::$links[$link], $option, $value)) {
				trigger_error('Could not set option '. $option .' to '. $value, E_USER_WARNING);
			}

			return true;
		}

		public static function disconnect(string|int|null $link=null): bool {

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

		public static function query(string $sql, string $link='default'): database_statement|bool {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			$timestamp = microtime(true);

			$result = mysqli_query(self::$links[$link], $sql);

			if ($result === false) {

				$reference = uniqid('db_');

				if (!defined('DEBUG') || !DEBUG) {
					error_log('['. date('Y-m-d H:i:s e') .']['. $reference .'] Failing Query: '. $sql . PHP_EOL, 3, 'storage://logs/errors.log');
				}

				throw new Error(implode(PHP_EOL, [
					"MySQL Error: ". mysqli_errno(self::$links[$link]) .': '. preg_replace('#\s+#', ' ', mysqli_error(self::$links[$link])),
					defined('DEBUG') && DEBUG ? "Query: ". $sql : "Reference: $reference",
				]));
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

		public static function multi_query(string $sql, string $link='default'): array {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			$timestamp = microtime(true);

			if (mysqli_multi_query(self::$links[$link], $sql) === false) {

				$reference = uniqid('db_');

				if (!defined('DEBUG') || !DEBUG) {
					error_log('['. date('Y-m-d H:i:s e') .']['. $reference .'] Failing Query: '. $sql . PHP_EOL, 3, 'storage://logs/errors.log');
				}

				throw new Error(implode(PHP_EOL, [
					"MySQL Error: ". mysqli_errno(self::$links[$link]) .': '. preg_replace('#\s+#', ' ', mysqli_error(self::$links[$link])),
					defined('DEBUG') && DEBUG ? "Query: ". $sql : "Reference: $reference",
				]));
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

		public static function prepare(string $sql, string $link='default'): database_statement {

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

		public static function fetch(database_statement $result, string $column=''): array|false|string|int|float|null {
			return $result->fetch($column);
		}

		public static function fetch_all(database_statement $result, string|int|null $column=null, string|int|null $index_column=null): array {
			return $result->fetch_all($column, $index_column);
		}

		public static function seek(database_statement $result, int $offset): database_statement {
			return $result->seek($offset);
		}

		public static function num_rows(database_statement $result): int {
			return $result->num_rows;
		}

		public static function free(database_statement $result): bool {
			return $result->free();
		}

		public static function insert_id(string $link='default'): int|false {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_insert_id(self::$links[$link]);
		}

		public static function affected_rows(string $link='default'): int|false {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_affected_rows(self::$links[$link]);
		}

		public static function info(string $link='default'): string|false {
			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			return mysqli_info(self::$links[$link]);
		}

		public static function begin_transaction(string $link='default'): bool {

			if (!isset(self::$links[$link])) {
				self::connect($link);
			}

			return mysqli_begin_transaction(self::$links[$link]);
		}

		public static function commit(string $link='default'): bool {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_commit(self::$links[$link]);
		}

		public static function rollback(string $link='default'): bool {

			if (!isset(self::$links[$link])) {
				return false;
			}

			return mysqli_rollback(self::$links[$link]);
		}

		public static function create_variable(array|string $field, int|float|string|array|null $value=null): int|float|string|array|null {

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

					default:
						return null;
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

		public static function input(array|string|int|float|null $input, bool $allowable_tags=false, bool $trim=true, string $link='default'): array|string|int|float|null {

			if (is_array($input)) {
				foreach (array_keys($input) as $key) {
					$input[$key] = self::input($input[$key], $allowable_tags, $trim, $link);
				}
				return $input;
			}

			// Pass non-string scalars through unchanged. Must run before the empty-check
			// below, otherwise `null == ''` (loose compare) would coerce null to ''.
			// gettype() returns 'NULL' / 'double' (not 'null' / 'float') — use those.
			if (in_array(gettype($input), ['NULL', 'boolean', 'integer', 'double'], true)) {
				return $input;
			}

			if ($input === '') {
				return '';
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

		public static function input_fulltext(array|string|int|float|null $input, bool $allowable_tags=false, bool $trim=true, string $link='default'): array|string|int|float|null {
			$input = self::input($input, $allowable_tags, $trim, $link);
			$input = preg_replace('#[+\-<>\(\)~*\"@; ]+#', ' ', $input);
			return $input;
		}

		public static function input_like(array|string|int|float|null $input, bool $allowable_tags=false, bool $trim=true, string $link='default'): array|string|int|float|null {
			$input = self::input($input, $allowable_tags, $trim, $link);
			$input = addcslashes($input, '%_');
			return $input;
		}

		/*
			Validate a string for safe use as a backtick-quoted SQL identifier
			(column or table base name). Unlike input(), which escapes string
			literals, there is no portable way to escape inner backticks, so
			the helper rejects anything outside the set A-Z / a-z / 0-9 / _ / -.

			Hyphen is included because BCP-47 locale codes ("zh-cn", "pt-br")
			are valid inside MySQL backticks and LiteCart accepts them in the
			language-create form. Everything else stays out so that semicolons,
			quotes, whitespace and null bytes cannot leak through.

			Optional allowlist: when provided, the name must also be a member
			of that list. Use it to pin to e.g. configured language codes:
					database::identifier($code, array_keys(language::$languages))

			Throws InvalidArgumentException on rejection. Callers catch this
			and translate to a 400-level response or a skip-with-warning,
			depending on whether the input came from a request or from the DB.
		*/
		public static function identifier(string $name, array|null $allowlist = null): string {

			if (!is_string($name) || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) {
				throw new InvalidArgumentException('Invalid SQL identifier');
			}

			if ($allowlist !== null) {
				if (!is_array($allowlist) || !in_array($name, $allowlist, true)) {
					throw new InvalidArgumentException('SQL identifier not in allowlist');
				}
			}

			return $name;
		}
	}

	class database_statement {

		private $_result;
		private $_statement;
		private $_link;

		public function __construct(string $statement, string $link = 'default', mysqli_result|null $result = null) {
			$this->_statement = $statement;
			$this->_link = $link;
			$this->_result = $result;
		}

		public function __destruct() {

			if ($this->_result) {
				$this->_result->free();
			}
		}

		public function __call(string $method, array $arguments) {
			return call_user_func_array([$this->_result, $method], $arguments);
		}

		// Magic getters for properties of mysqli_result
		public function __get(string $name): mixed {

			if (!isset($this->_result)) {
				$this->execute();
			}

			return $this->_result->$name;
		}

		public function __set(string $name, mixed $value): void {
			// Do nothing
		}

		public function bind(mixed ...$args): self {

			if (!$this->_statement) {
				trigger_error('No prepared statement available for binding parameters', E_USER_WARNING);
				return $this;
			}

			$sql = $this->_statement;

			foreach ($args as $nth => $params) {

				if (is_array($params)) {
					// Flatten the parameters
					$flattened = f::array_flatten($params, '.');
				} else {
					// Assign numeric placeholder :0 :1 for a non-array argument.
					$flattened = ["$nth" => $params];
				}

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

						// Commit replacement (keep the break character at $n — it belongs to the SQL, not the placeholder)
						$sql = substr($sql, 0, $i) . $value . substr($sql, $n);

						// Move cursor to end of parameter
						$i = $i + strlen($value);
					}
				}
			}

			$this->_statement = $sql;
			$this->_result = null;

			return $this;
		}

		public function execute(): self {

			if ($this->_result) {
				return $this; // Already executed
			}

			if (!$this->_statement) {
				trigger_error('No prepared statement available for execution', E_USER_WARNING);
				return false;
			}

			$timestamp = microtime(true);

			if (!isset(database::$links[$this->_link])) {
				database::connect($this->_link);
			}

			$this->_result = mysqli_query(database::$links[$this->_link], $this->_statement);

			if ($this->_result === false) {

				$reference = uniqid('db_');

				if (!defined('DEBUG') || !DEBUG) {
					error_log('['. date('Y-m-d H:i:s e') .']['. $reference .'] Failing Query: '. $sql . PHP_EOL, 3, 'storage://logs/errors.log');
				}

				throw new Error(implode(PHP_EOL, [
					"MySQL Error: ". mysqli_errno(database::$links[$this->_link]) .': '. preg_replace('#\s+#', ' ', mysqli_error(database::$links[$this->_link])),
					defined('DEBUG') && DEBUG ? "Query: ". $this->_statement : "Reference: $reference",
				]));
			}

			if (($duration = microtime(true) - $timestamp) > 5 && in_array('storage', stream_get_wrappers())) {
				error_log('['. date('Y-m-d H:i:s e').'] Warning: A MySQL query executed in '. number_format($duration, 5, '.', ' ') .' s. Query: '. str_replace("\r\n", "\r\n  ", $this->_statement) . PHP_EOL, 3, 'storage://logs/performance.log');
			}

			database::$stats['queries']++;
			database::$stats['duration'] += $duration;

			return $this;
		}

		public function export(&$object): self {
			return $object = $this;
		}

		public function fields(): array {
			return array_column(mysqli_fetch_fields($this->_result), 'name');
		}

		public function fetch($filter=null): mixed {

			if (!isset($this->_result)) {
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

		public function fetch_all(array|callable|string|null $filter=null, string|int|null $index_column=null): array {

			if (!isset($this->_result)) {
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

		public function fetch_page(array|callable|string|null $filter=null, string|int|null $index_column=null, int|null &$page=1, int|null $items_per_page=null, int|null &$num_rows=null, int|null &$num_pages=null): array {

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

				// Build a count-friendly query by replacing the initial SELECT ... FROM with SELECT 1 FROM
				// This avoids selecting all columns (e.g. SELECT * , json_value(...) AS title) which can
				// cause duplicate column name errors when used in a derived table.
				$count_query = preg_replace('#^\s*SELECT\b.*?\bFROM\b#is', 'SELECT 1 FROM', $query, 1);

				// Remove trailing ORDER BY from the count query if present
				$count_query = preg_replace('#\s+ORDER\s+BY\s+.+$#i', '', $count_query);

				// Count total rows by wrapping the simplified query
				$num_rows = database::query(
					"SELECT COUNT(*) AS num_rows FROM (\n\t\t\t\t" . $count_query . "\n\t\t\t) AS _subquery;",
					$this->_link
				)->fetch('num_rows');

				$num_pages = ($items_per_page > 0) ? ceil($num_rows / $items_per_page) : 0;

				$offset = ($page - 1) * $items_per_page;

				database::$stats['duration'] += microtime(true) - $timestamp;

				if ($offset < $num_rows) {
					$paged_query = $query . ' LIMIT ' . (int)$offset . ', ' . (int)$items_per_page;
					$rows = database::query($paged_query, $this->_link)->fetch_all($filter, $index_column);
				}

				return $rows;
			}

			if (!isset($this->_result)) {
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

		public function each(callable $function): void {
			while ($row = $this->fetch()) {
				call_user_func($function, $row);
			}
		}

		public function seek(int $offset): self {

			if (!isset($this->_result)) {
				$this->execute();
			}

			mysqli_data_seek($this->_result, $offset);

			return $this;
		}

		public function num_rows(): int {

			if (!isset($this->_result)) {
				$this->execute();
			}

			return mysqli_num_rows($this->_result);
		}

		public function free(): bool {

			if ($this->_result) {
				return mysqli_free_result($this->_result);
			}

			return true;
		}
	}

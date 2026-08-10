<?php

	class ent_database_table {
		public $data;
		public $previous;

		public function __construct(string $table_name = '') {

			if ($table_name) {
				$this->load($table_name);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [
				'name' => '',
				'columns' => [],
				'indexes' => [],
				'auto_increment' => '',
				'collation' => '',
				'engine' => '',
				'comment' => '',
				'created_at' => null,
			];

			$this->previous = $this->data;
		}

		public function load(string $table_name): void {

			$this->reset();

			$table = database::query(
				"select table_name as name, auto_increment, engine, table_collation as collation, create_time as created_at, table_comment as comment
				from information_schema.tables
				where TABLE_SCHEMA = '". database::input(DB_DATABASE) ."'
				and TABLE_NAME = '". database::input($table_name) ."'
				limit 1;"
			)->fetch();

			if (!$table) {
				throw new Exception('Could not find table (Name: '. $table_name .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($table, $this->data));

			// Columns
			$this->data['columns'] = database::query(
				"show full columns from `". database::input($table_name) ."`;"
			)->fetch_all(function($column) {
				return [
					'name' => $column['Field'],
					'type' => strtolower(strtok($column['Type'], '(')),
					'length' => preg_match('#\((.*?)\)#', $column['Type'], $matches) ? $matches[1] : '',
					'null' => preg_match('#^yes$#i', $column['Null']) ? true : false,
					'unsigned' => preg_match('#unsigned$#i', $column['Type']) ? true : false,
					'zerofill' => preg_match('#zerofill$#i', $column['Type']) ? true : false,
					'primary' => preg_match('#^pri$#i', $column['Key']) ? true : false,
					'key' => strtr($column['Key'], ['PRI' => 'primary', 'UNI' => 'unique', 'MUL' => 'multiple']),
					'default' => $column['Default'],
					'auto_increment' => preg_match('#auto_increment#i', $column['Extra']) ? true : false,
					'collation' => $column['Collation'],
					'comment' => $column['Comment'],
				];
			}, 'Field');

			// Indexes
			$this->data['indexes'] = [];

			database::query(
				"show index from `". database::input($table_name) ."`;"
			)->each(function($index) {

				if (!empty($this->data['indexes'][$index['Key_name']])) {
					$this->data['indexes'][$index['Key_name']]['columns'][] = $index['Column_name'];
					return;
				}

				$kind = ($index['Key_name'] == 'PRIMARY') ? 'primary'
					: ($index['Index_type'] == 'FULLTEXT' ? 'fulltext'
					: (!$index['Non_unique'] ? 'unique' : 'key'));
				$this->data['indexes'][$index['Key_name']] = [
						'name' => $index['Key_name'],
						'kind' => $kind,
						'type' => $index['Index_type'],
						'columns' => [$index['Column_name']],
						'cardinality' => $index['Cardinality'],
						'comment' => $index['Index_comment'],
				];
		});

			$this->previous = $this->data;
		}

		public function save(): void {

			$alterings = [];

			foreach ($this->previous['columns'] as $column) {
				if (!in_array($column['name'], array_keys($this->data['columns']))) {
					$alterings[] = "drop column `". $column['name'] ."`";
				}
			}

			foreach ($this->previous['indexes'] as $index) {
				$alterings[] = "drop key `". $index['name'] ."`";
			}

			foreach ($this->data['columns'] as $old_name => $column) {

				$alterings[] = implode(' ', array_filter([
					(!empty($this->previous['name']) ? (is_numeric($old_name) ? 'add column ' : 'change column `'. database::input($old_name) .'` ') : '') . '`'. database::input($column['name']) .'`',
					database::input($column['type']).'('. (isset($column['length']) ? database::input($column['length']) : '') .')',
					!empty($column['unsigned']) ? 'unsigned' : '',
					!empty($column['null']) ? 'null' : 'not null',
					!empty($column['auto_increment']) ? 'auto_increment' : '',
					(isset($column['default']) || !empty($column['null'])) ? 'default ' . (isset($column['default']) ? "'". database::input($column['default']) ."'" : 'null') : '',
					!empty($column['collate']) ? 'collate '. database::input($column['collate']) : '',
					!empty($last_column) ? 'after `'. database::input($last_column) .'`' : 'first',
				]));

				$last_column = $column['name'];
			}

			foreach ($this->data['indexes'] as $index) {
				$alterings[] = implode(' ', array_filter([
					!empty($column['primary']) ? 'primary key' : (!empty($column['unique']) ? 'unique key' : 'key'),
					'`'. database::input($index['name']) .'`',
					"(`". implode("`, `", $index['columns']) ."`)"
				]));
			}

			if (!$this->previous['name']) {
				database::query(
					"create table `". database::input($this->data['name']) ."` (
					". implode(','.PHP_EOL, $alterings) ."
					) engine=". $this->data['engine'] ." default character set ". database::input(strtok($this->data['collation'], '_')) ." collate ". database::input($this->data['collation']) .";"
				);
			} else {
				if ($this->previous['name'] != $this->data['name']) {
					database::query(
						"rename table `". database::input($this->previous['name']) ."`
						to `". database::input($this->data['name']) ."`;"
					);
				}
				database::query(
					"alter table `". database::input($this->data['name']) ."` (
					". implode(','.PHP_EOL, $alterings) ."
					) engine=". $this->data['engine'] ." convert to character set ". database::input(strtok($this->data['collation'], '_')) ." collate ". database::input($this->data['collation']) .";"
				);
			}

			$this->previous = $this->data;
		}

		public function delete(): void {

			if (!$this->previous['name']) return;

			database::query(
				"drop table `". database::input($this->previous['name']) ."`;"
			);

			$this->reset();
		}
	}

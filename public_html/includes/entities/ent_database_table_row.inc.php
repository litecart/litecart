<?php

	class ent_database_table_row {
		public $data;
		public $previous;
		public $_table;
		public $_primary_column;

		public function __construct($table, $id='') {

			if (!database::query(
				"select table from information_schema.tables
				where TABLE_SCHEMA = '". database::input(database::$selected['database']) ."'
				and table = '". database::input($table) ."'
				limit 1;"
			)->num_rows) {
				throw new Exception('Could not find table (Name: '. $table .') in database.');
			}

			$this->_table = $table;

			if ($id) {
				$this->_primary_column = $id;
			} else {
				$this->_primary_column = reference::database_table($table)->primary_key;
			}

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = database::query(
				"show fields from `". database::input($this->_table) ."`;"
			)->each(function($field) {
				return database::create_variable($field);
			});

			$this->previous = $this->data;
		}

		public function load($id) {

			$this->reset();

			$row = database::query(
				"select * from `". database::input($this->_table) ."`
				where `". database::input($this->_primary_column) ."` = '". database::input($id) ."'
				limit 1;"
			)->fetch();

			if (!$row) {
				throw new Exception('Could not find row ('. $this->_primary_column .': '. $id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($row, $this->data));

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data[$this->_primary_column]) {
				database::query(
					"insert into `". database::input($this->_table) ."`
					(`". implode("`, `", database::input(array_keys($this->data))) ."`)
					values ('". implode("', '", database::input($this->data)) ."');"
				);
			} else {
				$set = array_map(function($value, $key) {
					return "`". database::input($key) ."` = '". database::input($value) ."'";
				}, $this->data, array_keys($this->data));

				database::query(
					"update `". database::input($this->_table) ."`
					set ". implode(", ", $set) ."
					where `". database::input($this->_primary_column) ."` = '". database::input($this->data[$this->_primary_column]) ."'
					limit 1;"
				);
			}

			$this->previous = $this->data;

			cache::clear_cache();
		}

		public function delete() {

			if (empty($this->data['id'])) return;

			database::query(
				"delete from `". database::input($this->_table) ."`
				where `". database::input($this->_primary_column) ."` = '". database::input($this->data[$this->_primary_column]) ."'
				limit 1;"
			);

			$this->reset();

			cache::clear_cache();
		}
	}

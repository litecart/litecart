<?php

	class ent_administrator {
		public $data;
		public $previous;

		public function __construct($administrator_id=null) {

			if ($administrator_id) {
				$this->load($administrator_id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."administrators;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['apps'] = [];
			$this->data['widgets'] = [];

			$this->previous = $this->data;
		}

		public function load($administrator_id) {

			if (!preg_match('#(^[0-9]+$|^[0-9a-zA-Z_]$|@)#', $administrator_id)){
				throw new Exception('Invalid administrator (ID: '. $administrator_id .')');
			}

			$this->reset();

			$administrator = database::query(
				"select * from ". DB_TABLE_PREFIX ."administrators
				". (preg_match('#^[0-9]+$#', $administrator_id) ? "where id = ". (int)$administrator_id ."" : "") ."
				". (!preg_match('#^[0-9]+$#', $administrator_id) ? "where lower(username) = '". database::input(strtolower($administrator_id)) ."'" : "") ."
				". (preg_match('#@#', $administrator_id) ? "where lower(email) = '". database::input(strtolower($administrator_id)) ."'" : "") ."
				limit 1;"
			)->fetch();

			if ($administrator) {
				$this->data = array_replace($this->data, array_intersect_key($administrator, $this->data));
			} else {
				throw new Exception('Could not find administrator (ID: '. (int)$administrator_id .') in database.');
			}

			$this->data['apps'] = !empty($this->data['apps']) ? json_decode($this->data['apps'], true) : [];
			$this->data['widgets'] = !empty($this->data['widgets']) ? json_decode($this->data['widgets'], true) : [];

			$this->previous = $this->data;
		}

		public function save() {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."administrators
				where (
					lower(username) = '". database::input(strtolower($this->data['username'])) ."'
					". (!empty($this->data['email']) ? "or lower(email) = '". database::input(strtolower($this->data['email'])) ."'" : "") ."
				)
				". (!empty($this->data['id']) ? "and id != ". (int)$this->data['id'] : "") ."
				limit 1;"
			)->num_rows) {
				throw new Exception(language::translate('error_administrator_conflict', 'The administrator conflicts another administrator in the database'));
			}

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."administrators
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."administrators
				set status = '". (empty($this->data['status']) ? 0 : 1) ."',
					username = '". database::input(strtolower($this->data['username'])) ."',
					email = '". database::input(strtolower($this->data['email'])) ."',
					apps = '". database::input(json_encode($this->data['apps'], JSON_UNESCAPED_SLASHES)) ."',
					widgets = '". database::input(json_encode($this->data['widgets'], JSON_UNESCAPED_SLASHES)) ."',
					date_valid_from = ". (empty($this->data['date_valid_from']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
					date_valid_to = ". (empty($this->data['date_valid_to']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('administrators');
		}

		public function set_password($password) {

			if (!$this->data['id']) {
				$this->save();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."administrators
				set password_hash = '". database::input($this->data['password_hash'] = password_hash($password, PASSWORD_DEFAULT)) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous['password_hash'] = $this->data['password_hash'];
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."administrators
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('administrators');
		}
	}

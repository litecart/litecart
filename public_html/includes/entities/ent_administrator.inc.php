<?php

	class ent_administrator {
		public $data;
		public $previous;

		public function __construct(int|string|null $id = null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."administrators;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});
			$this->data['permissions'] = [];
			$this->data['known_ips'] = [];
			$this->data['known_fingerprints'] = [];

			$this->previous = $this->data;
		}

		public function load(int|string $id): void {

			if (!preg_match('#(^\d+$|^[0-9a-zA-Z_]$|@)#', $id)){
				throw new Exception('Invalid administrator (ID: '. $id .')');
			}

			$this->reset();

			$administrator = database::query(
				"select * from ". DB_TABLE_PREFIX ."administrators
				". (preg_match('#^\d+$#', $id) ? "where id = ". (int)$id : "") ."
				". (!preg_match('#^\d+$#', $id) ? "where username = '". database::input(strtolower($id)) ."'" : "") ."
				". (preg_match('#@#', $id) ? "where email = '". database::input(strtolower($id)) ."'" : "") ."
				limit 1;"
			)->fetch();

			if (!$administrator) {
				throw new Exception('Could not find administrator (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($administrator, $this->data));

			$this->data['permissions'] = $this->data['permissions'] ? json_decode($this->data['permissions'], true) : [];
			$this->data['known_ips'] = f::string_split($this->data['known_ips']);
			$this->data['known_fingerprints'] = f::string_split($this->data['known_fingerprints']);

			$this->previous = $this->data;
		}

		public function save(): void {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."administrators
				where (
					username = '". database::input(strtolower($this->data['username'])) ."'
					". (!empty($this->data['email']) ? "or email = '". database::input(strtolower($this->data['email'])) ."'" : "") ."
				)
				". (!empty($this->data['id']) ? "and id != ". (int)$this->data['id'] : "") ."
				limit 1;"
			)->num_rows) {
				throw new Exception(t('error_administrator_conflict', 'The administrator conflicts another administrator in the database'));
			}

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."administrators
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			$this->data['permissions'] = f::array_filter_recursive($this->data['permissions']);

			database::query(
				"update ". DB_TABLE_PREFIX ."administrators
				set status = ". (!empty($this->data['status']) ? 1 : 0) .",
					username = '". database::input(strtolower($this->data['username'])) ."',
					firstname = '". database::input($this->data['firstname']) ."',
					lastname = '". database::input($this->data['lastname']) ."',
					email = '". database::input(strtolower($this->data['email'])) ."',
					permissions = '". database::input(f::format_json($this->data['permissions'] ?: [])) ."',
					two_factor_auth = ". (!empty($this->data['two_factor_auth']) ? 1 : 0) .",
					valid_from = ". (empty($this->data['valid_from']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['valid_from'])) ."'") .",
					valid_to = ". (empty($this->data['valid_to']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['valid_to'])) ."'") .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('administrators');
		}

		public function set_password(string $password): void {

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

		public function delete(): void {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."administrators
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('administrators');
		}
	}

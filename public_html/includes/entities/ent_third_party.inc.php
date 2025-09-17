<?php

	class ent_third_party {
		public $data;
		public $previous;

		public function __construct($third_party_id=null) {

			if ($third_party_id !== null) {
				$this->load($third_party_id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."third_parties;"
			)->each(function($field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			});

			foreach ([
				'description',
				'collected_data',
				'purposes',
			] as $column) {
				$this->data[$column] = array_fill_keys(array_keys(language::$languages), '');
			}

			$this->data['privacy_classes'] = [];

			$this->previous = $this->data;
		}

		public function load($third_party_id) {

			if (!preg_match('#^[0-9]+$#', $third_party_id)) {
				throw new Exception('Invalid third party (ID: '. $third_party_id .')');
			}

			$this->reset();

			$third_party = database::query(
				"select * from ". DB_TABLE_PREFIX ."third_parties
				where id = ". (int)$third_party_id ."
				limit 1;"
			)->fetch();

			if (!$third_party) {
				throw new Exception('Could not find third party (ID: '. (int)$third_party_id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($third_party, $this->data));

			foreach ([
				'description',
				'collected_data',
				'purposes',
			] as $column) {
				$this->data[$column] = json_decode($this->data[$column], true) ?: [];
				$this->data[$column] += array_fill_keys(array_keys(language::$languages), '');
			}

			$this->data['privacy_classes'] = functions::string_split($this->data['privacy_classes']);

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."third_parties
					(name, created_at)
					values ('". database::input($this->data['name']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."third_parties
				set status = ". (int)$this->data['status'] .",
					privacy_classes = '". implode(',', database::input($this->data['privacy_classes'])) ."',
					name = '". database::input($this->data['name']) ."',
					description = '". database::input(functions::format_json($this->data['description'])) ."',
					collected_data = '". database::input(functions::format_json($this->data['collected_data'])) ."',
					purposes = '". database::input(functions::format_json($this->data['purposes'])) ."',
					country_code = '". database::input($this->data['country_code']) ."',
					homepage = '". database::input($this->data['homepage']) ."',
					cookie_policy_url = '". database::input($this->data['cookie_policy_url']) ."',
					privacy_policy_url = '". database::input($this->data['privacy_policy_url']) ."',
					opt_out_url = '". database::input($this->data['opt_out_url']) ."',
					do_not_sell_url = '". database::input($this->data['opt_out_url']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('third_parties');
		}

		public function delete() {

			database::query(
				"delete tp
				from ". DB_TABLE_PREFIX ."third_parties tp
				where tp.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('third_parties');
		}
	}

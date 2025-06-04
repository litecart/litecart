<?php

	class ent_redirect {
		public $data;
		public $previous;

		public function __construct($redirect_id=null) {

			if ($redirect_id) {
				$this->load((int)$redirect_id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."redirects;"
			)->each(function($field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			});

			$this->previous = $this->data;
		}

		public function load($redirect_id) {

			if (!preg_match('#^[0-9]+$#', $redirect_id)) {
				throw new Exception('Invalid redirect (ID: '. $redirect_id .')');
			}

			$this->reset();

			$redirect = database::query(
				"select * from ". DB_TABLE_PREFIX ."redirects
				where id = ". (int)$redirect_id ."
				limit 1;"
			)->fetch();

			if (!$redirect) {
				throw new Exception('Could not find redirect (ID: '. (int)$redirect_id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($redirect, $this->data));

			$this->previous = $this->data;
		}

		public function save() {

			if (empty($this->data['id'])) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."redirects
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."redirects
				set status = ". (int)$this->data['status'] .",
					immediate = ". (int)$this->data['immediate'] .",
					pattern = '". database::input($this->data['pattern']) ."',
					destination = '". database::input($this->data['destination']) ."',
					http_response_code = '". database::input($this->data['http_response_code']) ."',
					valid_from = ". (!empty($this->data['valid_from']) ? "'". database::input($this->data['valid_from']) ."'" : "null") .",
					valid_to = ". (!empty($this->data['valid_to']) ? "'". database::input($this->data['valid_to']) ."'" : "null") .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			cache::clear_cache('redirects');

			$this->previous = $this->data;
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."redirects
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('redirects');
		}
	}

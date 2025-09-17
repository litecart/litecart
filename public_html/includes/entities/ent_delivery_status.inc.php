<?php

	class ent_delivery_status {
		public $data;
		public $previous;

		public function __construct($id=null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."delivery_statuses;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			foreach ([
				'name',
				'description',
			] as $column) {
				$this->data[$column] = array_fill_keys(array_keys(language::$languages), '');
			}

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid delivery status (ID: '. $id .')');
			}

			$this->reset();

			$delivery_status = database::query(
				"select * from ". DB_TABLE_PREFIX ."delivery_statuses
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if (!$delivery_status) {
				throw new Exception('Could not find delivery status (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($delivery_status, $this->data));

			foreach ([
				'name',
				'description',
			] as $column) {
				$this->data[$column] = json_decode($this->data[$column], true) ?: [];
				$this->data[$column] += array_fill_keys(array_keys(language::$languages), '');
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."delivery_statuses
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."delivery_statuses
				set name = '". database::input(functions::json_format($this->data['name'])) ."',
					description = '". database::input(functions::json_format($this->data['description'])) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);


			$this->previous = $this->data;

			cache::clear_cache('delivery_statuses');
		}

		public function delete() {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."products
				where delivery_status_id = ". (int)$this->data['id'] ."
				limit 1;"
			)->num_rows) {
				throw new Exception('Cannot delete the delivery status because there are products using it');
			}

			database::query(
				"delete ds
				from ". DB_TABLE_PREFIX ."delivery_statuses ds
				where ds.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('delivery_statuses');
		}
	}

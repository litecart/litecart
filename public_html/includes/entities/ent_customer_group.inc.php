<?php

	class ent_customer_group {
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
				"show fields from ". DB_TABLE_PREFIX ."customer_groups;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid customer group (ID: '. $id .')');
			}
			$this->reset();

			$customer_group = database::query(
				"select * from ". DB_TABLE_PREFIX ."customer_groups
				where id = '". database::input($id) ."'
				limit 1;"
			)->fetch();

			if ($customer_group) {
				$this->data = array_replace($this->data, array_intersect_key($customer_group, $this->data));
			} else {
				throw new Exception('Could not find customer group (ID: '. (int)$id .') in database.');
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."customer_groups
					(created_at)
					values ('". database::input(date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customer_groups
				set type = '". database::input($this->data['type']) ."',
					name = '". database::input($this->data['name']) ."',
					description = '". database::input($this->data['description']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = '". (int)$this->data['id'] ."'
				limit 1;"
			);

			cache::clear_cache('customer');

			$this->previous = $this->data;
		}

		public function delete() {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."customers
				where group_id = ". (int)$this->data['id'] ."
				limit 1;"
			)->num_rows) {
				throw new Error('Cannot delete customer group as there are customers linked to it');
			}

			database::query(
				"delete from ". DB_TABLE_PREFIX ."customer_groups
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('customer');
		}
	}

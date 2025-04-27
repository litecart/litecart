<?php

	class ent_tax_class {
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
				"show fields from ". DB_TABLE_PREFIX ."tax_classes;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid tax class (ID: '. $id .')');
			}

			$this->reset();

			$tax_class = database::query(
				"select * from ". DB_TABLE_PREFIX ."tax_classes
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if ($tax_class) {
				$this->data = array_replace($this->data, array_intersect_key($tax_class, $this->data));
			} else {
				throw new Exception('Could not find tax class (ID: '. (int)$id .') in database.');
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."tax_classes
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."tax_classes
				set code = '". database::input($this->data['code']) ."',
					name = '". database::input($this->data['name']) ."',
					description = '". database::input($this->data['description']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('tax_classes');
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."tax_classes
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('tax_classes');
		}
	}

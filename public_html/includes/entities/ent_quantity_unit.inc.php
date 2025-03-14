<?php

	class ent_quantity_unit {
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
				"show fields from ". DB_TABLE_PREFIX ."quantity_units;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."quantity_units_info;"
			)->each(function($field){
				if (in_array($field['Field'], ['id', 'quantity_unit_id', 'language_code'])) return;
				$this->data[$field['Field']] = array_fill_keys(array_keys(language::$languages), database::create_variable($field));
			});

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid quantity unit (ID: '. $id .')');
			}

			$this->reset();

			$quantity_unit = database::query(
				"select * from ". DB_TABLE_PREFIX ."quantity_units
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if ($quantity_unit) {
				$this->data = array_replace($this->data, array_intersect_key($quantity_unit, $this->data));
			} else {
				throw new Exception('Could not find quantity unit (ID: '. (int)$id .') in database.');
			}

			database::query(
				"select * from ". DB_TABLE_PREFIX ."quantity_units_info
				where quantity_unit_id = ". (int)$this->data['id'] .";"
			)->each(function($info){
				foreach ($info as $key => $value) {
					if (in_array($key, ['id', 'quantity_unit_id', 'language_code'])) continue;
					$this->data[$key][$info['language_code']] = $value;
				}
			});

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."quantity_units
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);
				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."quantity_units
				set decimals = ". (int)$this->data['decimals'] .",
					separate = ". (int)$this->data['separate'] .",
					priority = ". (int)$this->data['priority'] .",
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			foreach (array_keys(language::$languages) as $language_code) {

				$info = database::query(
					"select * from ". DB_TABLE_PREFIX ."quantity_units_info
					where quantity_unit_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				)->fetch();

				if (!$info) {

					database::query(
						"insert into ". DB_TABLE_PREFIX ."quantity_units_info
						(quantity_unit_id, language_code)
						values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
					);

					$info['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."quantity_units_info
					set name = '". database::input($this->data['name'][$language_code]) ."',
						description = '". database::input($this->data['description'][$language_code]) ."'
					where id = ". (int)$info['id'] ."
					and quantity_unit_id = ". (int)$this->data['id'] ."
					and language_code = '". database::input($language_code) ."'
					limit 1;"
				);
			}

			$this->previous = $this->data;

			cache::clear_cache('quantity_units');
		}

		public function delete() {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."products
				where quantity_unit_id = ". (int)$this->data['id'] ."
				limit 1;"
			)->num_rows) {
				throw new Exception('Cannot delete the quantity unit because there are products using it');
			}

			database::query(
				"delete qu, qui
				from ". DB_TABLE_PREFIX ."quantity_units qu
				left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qui.quantity_unit_id = qu.id)
				where qu.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('quantity_units');
		}
	}

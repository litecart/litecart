<?php

	class ent_supplier {
		public $data;
		public $previous;

		public function __construct($supplier_id=null) {

			if (!empty($supplier_id)) {
				$this->load($supplier_id);
			} else {
				$this->reset();
			}
		}

		public function reset() {

			$this->data = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."suppliers;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->previous = $this->data;
		}

		public function load($supplier_id) {

			if (!preg_match('#^[0-9]+$#', $supplier_id)) {
				throw new Exception('Invalid supplier (ID: '. $supplier_id .')');
			}

			$this->reset();

			$supplier = database::query(
				"select * from ". DB_TABLE_PREFIX ."suppliers
				where id=". (int)$supplier_id ."
				limit 1;"
			)->fetch();

			if ($supplier) {
				$this->data = array_replace($this->data, array_intersect_key($supplier, $this->data));
			} else {
				throw new Exception('Could not find supplier (ID: '. (int)$supplier_id .') in database.');
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."suppliers
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);
				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."suppliers
				set code = '". database::input($this->data['code']) ."',
					name = '". database::input($this->data['name']) ."',
					description = '". database::input($this->data['description'], true) ."',
					email = '". database::input($this->data['email']) ."',
					phone = '". database::input($this->data['phone']) ."',
					link = '". database::input($this->data['link']) ."',
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('suppliers');
		}

		public function delete() {

			if (!$this->data['id']) return;

			$products_query = database::query(
				"select id from ". DB_TABLE_PREFIX ."products
				where supplier_id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			if (database::num_rows($products_query)) {
				throw new Exception(language::translate('error_cannot_delete_supplier_while_used_by_products', 'The supplier could not be deleted because there are products linked to it.'));
			}

			database::query(
				"delete from ". DB_TABLE_PREFIX ."suppliers
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('suppliers');
		}
	}

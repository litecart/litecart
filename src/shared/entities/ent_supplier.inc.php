<?php

	class ent_supplier {
		public $data;
		public $previous;

		public function __construct(int|null $id = null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			database::query(
				"show fields from ". DB_PREFIX ."suppliers;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->previous = $this->data;
		}

		public function load(int $id): void {

			if (!preg_match('#^\d+$#', $id)) {
				throw new Exception('Invalid supplier (ID: '. $id .')');
			}

			$this->reset();

			$supplier = database::query(
				"select * from ". DB_PREFIX ."suppliers
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if (!$supplier) {
				throw new Exception('Could not find supplier (ID: '. (int)$id .') in database.');
			}

			$this->data = f::array_update($this->data, $supplier);

			$this->previous = $this->data;
		}

		public function save(): void {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_PREFIX ."suppliers
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);
				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_PREFIX ."suppliers
				set code = '". database::input($this->data['code']) ."',
					name = '". database::input($this->data['name']) ."',
					description = '". database::input($this->data['description'], true) ."',
					email = '". database::input($this->data['email']) ."',
					phone = '". database::input($this->data['phone']) ."',
					link = '". database::input($this->data['link']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('suppliers');
		}

		public function delete(): void {

			if (!$this->data['id']) return;

			if (database::query(
				"select id from ". DB_PREFIX ."products
				where supplier_id = ". (int)$this->data['id'] ."
				limit 1;"
			)->num_rows) {
				throw new Exception(t('error_cannot_delete_supplier_while_used_by_products', 'The supplier could not be deleted because there are products linked to it.'));
			}

			database::query(
				"delete from ". DB_PREFIX ."suppliers
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('suppliers');
		}
	}

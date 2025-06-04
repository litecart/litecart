<?php

	class ent_sold_out_status {
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
				"show fields from ". DB_TABLE_PREFIX ."sold_out_statuses;"
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

			if (!preg_match('#^[0-9]+$#', $id))  {
				throw new Exception('Invalid sold out status (ID: '. $id .')');
			}

			$this->reset();

			$sold_out_status = database::query(
				"select * from ". DB_TABLE_PREFIX ."sold_out_statuses
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if (!$sold_out_status) {
				throw new Exception('Could not find sold out status (ID: '. (int)$id .') in database.');
			}

			$this->data = array_replace($this->data, array_intersect_key($sold_out_status, $this->data));

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
					"insert into ". DB_TABLE_PREFIX ."sold_out_statuses
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);
				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."sold_out_statuses
				set orderable = ". (int)$this->data['orderable'] .",
					hidden = ". (int)$this->data['hidden'] .",
					name = '". database::input(json_encode($this->data['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					description = '". database::input(json_encode($this->data['description'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('sold_out_statuses');
		}

		public function delete() {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."products
				where sold_out_status_id = ". (int)$this->data['id'] ."
				limit 1;"
			)->num_rows) {
				throw new Exception('Cannot delete the sold out status because there are products using it');
			}

			database::query(
				"delete ss
				from ". DB_TABLE_PREFIX ."sold_out_statuses ss
				where ss.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('sold_out_statuses');
		}
	}

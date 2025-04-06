<?php

	class ent_attribute_group {
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
				"show fields from ". DB_TABLE_PREFIX ."attribute_groups;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->data['values'] = [];

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid attribute (ID: '. $id .')');
			}

			$this->reset();

			$group = database::query(
				"select * from ". DB_TABLE_PREFIX ."attribute_groups
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if ($group) {
				$this->data = array_replace($this->data, array_intersect_key($group, $this->data));
			} else {
				throw new Exception('Could not find attribute (ID: '. (int)$id .') in database.');
			}

			$this->data['name'] = json_decode($this->data['name'], true) ?: [];

			database::query(
				"select * from ". DB_TABLE_PREFIX ."attribute_values
				where group_id = ". (int)$id ."
				order by priority;"
			)->each(function($value) {

				$value['name'] = json_decode($value['name'], true) ?: [];

				$value['in_use'] = database::query(
					"select id from ". DB_TABLE_PREFIX ."products_attributes
					where value_id = ". (int)$value['id'] ."
					limit 1;"
				)->num_rows ? true : false;

				$this->data['values'][] = $value;
			});

			$this->previous = $this->data;
		}

		public function save() {

			// Group
			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."attribute_groups
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."attribute_groups
				set code = '". database::input($this->data['code']) ."',
					name = '". database::input(json_encode($this->data['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
					sort = '". database::input($this->data['sort']) ."',
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Delete values
			database::query(
				"select id from ". DB_TABLE_PREFIX ."attribute_values
				where group_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['values'], 'id')) ."');"
			)->each(function($value) {

				$has_products_attributes = database::query(
					"select id from ". DB_TABLE_PREFIX ."products_attributes
					where value_id = ". (int)$value['id'] ."
					limit 1;"
				)->num_rows ? true : false;

				if ($has_products_attributes) {
					throw new Exception('Cannot delete value linked to product attributes');
				}

				database::query(
					"delete from ". DB_TABLE_PREFIX ."attribute_values
					where group_id = ". (int)$this->data['id'] ."
					and id = ". (int)$value['id'] ."
					limit 1;"
				);
			});

			// Update/Insert values
			$i = 0;
			foreach ($this->data['values'] as $key => $value) {

				if (empty($value['id'])) {

					database::query(
						"insert into ". DB_TABLE_PREFIX ."attribute_values
						(group_id, date_created)
						values (". (int)$this->data['id'] .", '". ($this->data['values'][$key]['date_created'] = date('Y-m-d H:i:s')) ."');"
					);

					$value['id'] = $this->data['values'][$key]['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."attribute_values
					set name = '". database::input(json_encode($value['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ."',
						priority = ". (int)$i++ .",
						date_updated = '". ($this->data['values'][$key]['date_updated'] = date('Y-m-d H:i:s')) ."'
					where id = ". (int)$value['id'] ."
					limit 1;"
				);
			}

			$this->previous = $this->data;

			cache::clear_cache('attributes');
		}

		public function delete() {

			if (!$this->data['id']) return;

			// Check category filters for attribute
			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."categories_filters
				where attribute_group_id = ". (int)$this->data['id'] .";"
			)->num_rows) {
				throw new Exception('Cannot delete group linked to products');
			}

			// Check products for attribute
			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."products_attributes
				where group_id = ". (int)$this->data['id'] .";"
			)->num_rows) {
				throw new Exception('Cannot delete group linked to products');
			}

			$this->data['values'] = [];
			$this->save();

			// Delete attribute
			database::query(
				"delete ag, av
				from ". DB_TABLE_PREFIX ."attribute_groups ag
				left join ". DB_TABLE_PREFIX ."attribute_values av on (av.group_id = ag.id)
				where ag.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('attributes');
		}
	}

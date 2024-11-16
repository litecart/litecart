<?php

	class ent_tax_rate {
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
				"show fields from ". DB_TABLE_PREFIX ."tax_rates;"
			)->each(function($field){
				$this->data[$field['Field']] = database::create_variable($field);
			});

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid tax rate (ID: '. $id .')');
			}

			$this->reset();

			$tax_rate = database::query(
				"select * from ". DB_TABLE_PREFIX ."tax_rates
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if ($tax_rate) {
				$this->data = array_replace($this->data, array_intersect_key($tax_rate, $this->data));
			} else {
				throw new Exception('Could not find tax rate (ID: '. (int)$id .') in database.');
			}

			$this->previous = $this->data;
		}

		public function save() {

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."tax_rates
					(date_created)
					values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."tax_rates
				set tax_class_id = ". (int)$this->data['tax_class_id'] .",
					geo_zone_id = ". (int)$this->data['geo_zone_id'] .",
					code = '". database::input($this->data['code']) ."',
					name = '". database::input($this->data['name']) ."',
					description = '". database::input($this->data['description']) ."',
					rate = ". (float)$this->data['rate'] .",
					address_type = '". database::input($this->data['address_type']) ."',
					rule_companies_with_tax_id = ". (int)$this->data['rule_companies_with_tax_id'] .",
					rule_companies_without_tax_id = ". (int)$this->data['rule_companies_without_tax_id'] .",
					rule_individuals_with_tax_id = ". (int)$this->data['rule_individuals_with_tax_id'] .",
					rule_individuals_without_tax_id = ". (int)$this->data['rule_individuals_without_tax_id'] .",
					date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->previous = $this->data;

			cache::clear_cache('tax_rates');
		}

		public function delete() {

			database::query(
				"delete from ". DB_TABLE_PREFIX ."tax_rates
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$this->reset();

			cache::clear_cache('tax_rates');
		}
	}

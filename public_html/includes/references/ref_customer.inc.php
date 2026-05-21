<?php

	class ref_customer extends abs_reference_entity {

		protected $_data = [];

		public function __construct(int $customer_id) {
			$this->_data['id'] = $customer_id;
		}

		protected function _load(string $field): void {

			switch($field) {

				case 'group':

					$this->_data['group'] = [];

					if (!$this->group_id) break;

					$this->_data['group'] = database::query(
						"select * from ". DB_TABLE_PREFIX ."customer_groups
						where id = ". (int)$this->group_id .";"
					)->fetch();

					break;

				default:

					$customer = database::query(
						"select * from ". DB_TABLE_PREFIX ."customers
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch();

					if (!$customer) return;

					foreach ($customer as $key => $value) {
						$this->_data[$key] = $customer[$key];
					}

					break;
			}
		}
	}

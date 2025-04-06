<?php

	class ref_order_status extends abs_reference_entity {

		protected $_language_codes;

		function __construct($order_status_id, $language_code=null) {

			if (empty($language_code)) $language_code = language::$selected['code'];

			$this->_data['id'] = (int)$order_status_id;
			$this->_language_codes = array_unique([
				$language_code,
				settings::get('default_language_code'),
				settings::get('store_language_code'),
			]);
		}

		protected function _load($field) {

			switch ($field) {

				default:

					$order_status = database::query(
						"select * from ". DB_TABLE_PREFIX ."order_statuses
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch(function($staus) {

						foreach ([
							'name',
							'description',
							'email_subject',
							'email_message',
						] as $field) {

							$status[$field] = json_decode($status[$field], true) ?: [];

							foreach ($this->_language_codes as $language_code) {
								if (!empty($status[$field][$language_code])) {
									$status[$field] = $status[$field][$language_code];
								}
							}
						}

						return $status;
					});

					if (!$order_status) {
						$order_status = database::query(
							"show fields from ". DB_TABLE_PREFIX ."order_statuses;"
						)->each(function($field) use ($order_status) {
							$order_status[$field['Field']] = database::create_variable($field);
						})->fetch();
					}

					foreach ($order_status as $key => $value) {
						$this->_data[$key] = $value;
					}

					break;
			}
		}
	}

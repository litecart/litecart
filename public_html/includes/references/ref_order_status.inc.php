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

				case 'name':
				case 'description':
				case 'email_subject':
				case 'email_message':

					$query = database::query(
						"select * from ". DB_TABLE_PREFIX ."order_statuses_info
						where order_status_id = ". (int)$this->_data['id'] ."
						and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
						order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
					);

					while ($row = database::fetch($query)) {
						foreach ($row as $key => $value) {
							if (in_array($key, ['id', 'order_status_id', 'language_code'])) continue;
							if (empty($this->_data[$key])) $this->_data[$key] = $value;
						}
					}

					break;

				default:

					$order_status = database::query(
						"select * from ". DB_TABLE_PREFIX ."order_statuses
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch();

					if (!$order_status) return;

					foreach ($order_status as $key => $value) {
						$this->_data[$key] = $value;
					}

					break;
			}
		}
	}

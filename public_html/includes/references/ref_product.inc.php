<?php

	class ref_product extends abs_reference_entity {

		protected $_language_codes;
		protected $_currency_codes;
		protected $_customer_id;

		function __construct($product_id, $language_code=null, $currency_code=null, $customer_id=null) {

			if (empty($language_code)) {
				$language_code = language::$selected['code'];
			}

			if (empty($currency_code)) {
				$currency_code = currency::$selected['code'];
			}

			if (empty($customer_id)) {
				$customer_id = customer::$data['id'];
			}

			$this->_data['id'] = (int)$product_id;

			$this->_customer_id = $customer_id;

			$this->_language_codes = array_unique([
				$language_code,
				settings::get('default_language_code'),
				settings::get('store_language_code'),
			]);

			$this->_currency_codes = array_unique([
				$currency_code,
				settings::get('store_currency_code'),
			]);
		}

		protected function _load($field) {

			switch($field) {

				case 'also_purchased_products':

					$this->_data['also_purchased_products'] = database::query(
						"select oi.product_id, sum(oi.quantity) as num_purchases from ". DB_TABLE_PREFIX ."orders_items oi
						left join ". DB_TABLE_PREFIX ."products p on (p.id = oi.product_id)
						where p.status
						and (oi.product_id != 0 and oi.product_id != ". (int)$this->_data['id'] .")
						and order_id in (
							select distinct order_id as id from ". DB_TABLE_PREFIX ."orders_items
							where product_id = ". (int)$this->_data['id'] ."
						)
						group by oi.product_id
						order by num_purchases desc;"
					)->fetch_all(function($product) {
						return reference::product($product['product_id'], $this->_language_codes[0]);
					});

					break;

				case 'attributes':

					$this->_data['attributes'] = database::query(
						"select pa.id, ag.code, pa.group_id, pa.value_id, pa.custom_value, agi.name as group_name, avi.name as value_name, pa.custom_value from ". DB_TABLE_PREFIX ."products_attributes pa
						left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = pa.group_id)
						left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = pa.group_id and agi.language_code = '". database::input($this->_language_codes[0]) ."')
						left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = pa.value_id and avi.language_code = '". database::input($this->_language_codes[0]) ."')
						where product_id = ". (int)$this->_data['id'] ."
						order by priority, group_name, value_name, custom_value;"
					)->fetch_all();

					break;

				case 'brand':

					$this->_data['brand'] = [];

					if (empty($this->_data['brand_id'])) return;

					$this->_data['brand'] = reference::brand($this->brand_id, $this->_language_codes[0]);

				case 'campaign':

					$this->_data['campaign'] = database::query(
						"select *, min(
							coalesce(
								". implode(', ', array_map(function($currency_code){
									return "if(`". database::input($currency_code) ."` != 0, `". database::input($currency_code) ."` * ". currency::$currencies[$currency_code]['value'] .", null)";
								}, $this->_currency_codes)) ."
							)
						) as price
						from ". DB_TABLE_PREFIX ."campaigns_products
						where product_id = ". (int)$this->_data['id'] ."
						and campaign_id in (
							select id from ". DB_TABLE_PREFIX ."campaigns
							where status
							and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
							and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."')
						)
						group by product_id
						limit 1;"
					)->fetch();

					break;

				case 'categories':

					$this->_data['categories'] = [];

					database::query(
						"select * from ". DB_TABLE_PREFIX ."products_to_categories
						where product_id = ". (int)$this->_data['id'] .";"
					)->each(function($product_to_category) {

						database::query(
							"select * from ". DB_TABLE_PREFIX ."categories_info
							where category_id = ". (int)$product_to_category['category_id'] ."
							and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
							order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
						)->each(function($info){
							foreach ($info as $key => $value) {
								if (in_array($key, ['id', 'category_id', 'language_code'])) continue;
								if (empty($this->_data['categories'][$info['category_id']])) {
									$this->_data['categories'][$info['category_id']] = $value;
								}
							}
						});
					});

					break;

				case 'default_category':

					$this->_data['default_category'] = 0;

					if (empty($this->default_category_id)) return;

					$this->_data['default_category'] = reference::category($this->default_category_id, $this->_language_codes[0]);

					break;

				case 'delivery_status':

					$this->_data['delivery_status'] = [];

					database::query(
						"select * from ". DB_TABLE_PREFIX ."delivery_statuses_info
						where delivery_status_id = ". (int)$this->_data['delivery_status_id'] ."
						and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
						order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
					)->each(function($info){

						foreach ($info as $key => $value) {
							if (in_array($key, ['id', 'delivery_status_id', 'language_code'])) continue;
							if (empty($this->_data['delivery_status'][$key])) {
								$this->_data['delivery_status'][$key] = $value;
							}
						}

					});

					break;

				case 'final_price':

					$this->_data['final_price'] = $this->price;

					if (isset($this->campaign['price']) && $this->campaign['price'] > 0 && $this->campaign['price'] < $this->_data['final_price']) {
						$this->_data['final_price'] = $this->campaign['price'];
					}

					break;

				case 'images':

					$this->_data['images'] = database::query(
						"select * from ". DB_TABLE_PREFIX ."products_images
						where product_id = ". (int)$this->_data['id'] ."
						order by priority asc, id asc;"
					)->fetch_all('filename');

					break;

				case 'name':
				case 'short_description':
				case 'description':
				case 'technical_data':
				case 'head_title':
				case 'meta_description':
				case 'synonyms':

					database::query(
						"select * from ". DB_TABLE_PREFIX ."products_info
						where product_id = ". (int)$this->_data['id'] ."
						and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
						order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
					)->each(function($info){

						foreach ($info as $key => $value) {
							if (in_array($key, ['id', 'product_id', 'language_code'])) continue;
							if (empty($this->_data[$key])) $this->_data[$key] = $value;
						}

					});

					if ($this->autofill_technical_data) {
						$this->_data['technical_data'] = '';
						foreach ($this->attributes as $attribute) {
							$this->_data['technical_data'] = $attribute['group_name'] .': '. $attribute['value_name'] . PHP_EOL;
						}
						$this->_data['technical_data'] = rtrim($this->_data['technical_data']);
					}

					$this->_data['synonyms'] = preg_split('#\s*,\s*#', (string)$this->_data['synonyms'], -1, PREG_SPLIT_NO_EMPTY);

					break;

				case 'customizations':

					$this->_data['customizations'] = [];

					$products_customizations_query = database::query(
						"select * from ". DB_TABLE_PREFIX ."products_customizations
						where product_id = ". (int)$this->_data['id'] ."
						order by priority;"
					)->fetch(function($customization){

						// Group
						database::query(
							"select * from ". DB_TABLE_PREFIX ."attribute_groups_info pcgi
							where group_id = ". (int)$customization['group_id'] ."
							and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
							order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
						)->each(function($info) use ($customization) {

							foreach ($info as $key => $value) {
								if (in_array($k, ['id', 'group_id', 'language_code'])) continue;
								if (empty($customization[$key])) $customization[$key] = $value;
							}

						});

						// Values
						$customization['values'] = [];

						database::query(
							"select * from ". DB_TABLE_PREFIX ."products_customizations_values
							where product_id = ". (int)$this->_data['id'] ."
							and group_id = ". (int)$customization['group_id'] ."
							order by priority;"
						)->each(function($value) use ($customization) {

							if (!empty($value['value_id'])) {

								database::query(
									"select * from ". DB_TABLE_PREFIX ."attribute_values_info pcvi
									where value_id = ". (int)$value['value_id'] ."
									and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
									order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
								)->each(function($info){

									foreach ($info as $key => $v) {
										if (in_array($key, ['id', 'value_id', 'language_code'])) continue;
										if (empty($value[$key])) $value[$key] = $v;
									}

								});

							} else {
								$value['name'] = $value['custom_value'];
							}

							// Price Adjust
							$value['price_adjust'] = 0;

							if ((!empty($value[$this->_currency_code]) && (float)$value[$this->_currency_code] != 0) || (!empty($value[settings::get('store_currency_code')]) && (float)$value[settings::get('store_currency_code')] != 0)) {

								switch ($value['price_operator']) {

									case '+':
										if ((float)$value[$this->_currency_code] != 0) {
											$value['price_adjust'] = currency::convert($value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
										} else {
											$value['price_adjust'] = (float)$value[settings::get('store_currency_code')];
										}
										break;

									case '%':
										if ((float)$value[$this->_currency_code] != 0) {
											$value['price_adjust'] = $this->price * currency::convert((float)$value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code')) / 100;
										} else {
											$value['price_adjust'] = $this->price * (float)$value[settings::get('store_currency_code')] / 100;
										}
										break;

									case '*':
										if ((float)$value[$this->_currency_code] != 0) {
											$value['price_adjust'] = $this->price * currency::convert($value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code'));
										} else {
											$value['price_adjust'] = $this->price * $value[settings::get('store_currency_code')];
										}
										break;

									case '=':
										if ((float)$value[$this->_currency_code] != 0) {
											$value['price_adjust'] = currency::convert($value[$this->_currency_code], $this->_currency_code, settings::get('store_currency_code')) - $this->price;
										} else {
											$value['price_adjust'] = $value[settings::get('store_currency_code')] - $this->price;
										}
										break;

									default:
										trigger_error('Unknown price operator for customization', E_USER_WARNING);
										break;
								}
							}

							if ($value['price_adjust'] && !empty($this->campaign['price'])) {
								$value['price_adjust'] = $value['price_adjust'] * $this->campaign['price'] / $this->price;
							}

							if (!empty($value['value_id'])) {
								$customization['values'][$value['value_id']] = $value;
							} else {
								$customization['values'][uniqid()] = $value;
							}
						});

						if ($customization['sort'] == 'alphabetically') {
							uasort($customization['values'], function($a, $b){
								if ($a['name'] == $b['name']) return 0;
								return ($a['name'] < $b['name']) ? -1 : 1;
							});
						}

						$this->_data['customizations'][$customization['group_id']] = $customization;
					});

					break;

				case 'parents':

					$this->_data['parents'] = database::query(
						"select category_id from ". DB_TABLE_PREFIX ."products_to_categories
						where product_id = ". (int)$this->_data['id'] .";"
					)->fetch_all(function($row) {
						return reference::category($row['category_id'], $this->_language_codes[0]);
					});

					break;

				case 'price':

					$this->_data['price'] = (float)database::query(
						"select coalesce(
							". implode(", ", array_map(function($currency){ return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)"; }, currency::$currencies)) ."
						) price
						from ". DB_TABLE_PREFIX ."products_prices
						where product_id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch('price');

					break;

				case 'quantity':
				case 'num_stock_options':

					$this->_data['quantity'] = null;
					$this->_data['num_stock_options'] = null;

					$stock_options = database::query(
						"select count(id) as num_stock_options, sum(quantity) as total_quantity
						from ". DB_TABLE_PREFIX ."products_stock_options
						where product_id = ". (int)$this->_data['id'] ."
						group by product_id;"
					)->fetch();

					$this->_data['num_stock_options'] = $stock_options['num_stock_options'];

					if ($stock_options['num_stock_options']) {
						$this->_data['quantity'] = $stock_options['total_quantity'];
					}

					break;

				case 'quantity_available':
				case 'quantity_reserved':

					$this->_data['quantity_available'] = null;

					if (!database::query(
						"select id from ". DB_TABLE_PREFIX ."products_stock_options
						where product_id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->num_rows) {
						break;
					}

					$this->_data['quantity_reserved'] = database::query(
						"select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
						left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
						where oi.product_id = ". (int)$this->_data['id'] ."
						and o.order_status_id in (
							select id from ". DB_TABLE_PREFIX ."order_statuses
							where stock_action = 'reserve'
						);"
					)->fetch('total_reserved');

					$this->_data['quantity_available'] = $this->quantity - $this->_data['quantity_reserved'];

					break;

				case 'quantity_available':
				case 'quantity_reserved':

					$this->_data['quantity_reserved'] = database::query(
						"select sum(quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
						left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
						where oi.product_id = ". (int)$this->_data['id'] ."
						and o.order_status_id in (
							select id from ". DB_TABLE_PREFIX ."order_statuses
							where stock_action = 'reserve'
						);"
					)->fetch('total_reserved');

					$this->_data['quantity_available'] = $this->quantity - $this->quantity_reserved;

					break;

				case 'quantity_unit':

					$this->_data['quantity_unit'] = database::query(
						"select id, decimals, separate from ". DB_TABLE_PREFIX ."quantity_units
						where id = ". (int)$this->quantity_unit_id ."
						limit 1;"
					)->fetch();

					if (!$this->_data['quantity_unit']) return;

					database::query(
						"select * from ". DB_TABLE_PREFIX ."quantity_units_info
						where quantity_unit_id = ". (int)$this->quantity_unit_id ."
						and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
						order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
					)->each(function($info){
						foreach ($info as $key => $value) {
							if (in_array($key, ['id', 'quantity_unit_id', 'language_code'])) continue;
							if (empty($this->_data['quantity_unit'][$key])) {
								$this->_data['quantity_unit'][$key] = $value;
							}
						}
					});

					break;

				case 'stock_options':

					$this->_data['stock_options'] = database::query(
						"select si.*, pso.*, sii.name, ifnull(oi.quantity_reserved, 0) as quantity_reserved, si.quantity - ifnull(oi.quantity_reserved, 0) as quantity_available
						from ". DB_TABLE_PREFIX ."products_stock_options pso
						left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
						left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = pso.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
						left join (
							select product_id, stock_item_id, sum(quantity) as quantity_reserved
							from ". DB_TABLE_PREFIX ."orders_items
							where order_id in (
								select id from ". DB_TABLE_PREFIX ."orders
								where order_status_id in (
									select id from ". DB_TABLE_PREFIX ."order_statuses
									where stock_action = 'reserve'
								)
							)
							group by stock_item_id
						) oi on (oi.product_id = pso.product_id and oi.stock_item_id = pso.id)
						where pso.product_id = ". (int)$this->_data['id'] ."
						order by pso.priority asc;"
					)->fetch_all();

					break;

				case 'supplier':

					$this->_data['supplier'] = null;

					if (!empty($this->supplier_id)) {
						$this->_data['supplier'] = reference::supplier($this->supplier_id);
					}

					break;

				case 'sold_out_status':

					$this->_data['sold_out_status'] = database::query(
						"select id, orderable from ". DB_TABLE_PREFIX ."sold_out_statuses
						where id = ". (int)$this->sold_out_status_id ."
						limit 1;"
					)->fetch();

					if (!$this->_data['sold_out_status']) return;

					database::query(
						"select * from ". DB_TABLE_PREFIX ."sold_out_statuses_info
						where sold_out_status_id = ". (int)$this->_data['sold_out_status_id'] ."
						and language_code in ('". implode("', '", database::input($this->_language_codes)) ."')
						order by field(language_code, '". implode("', '", database::input($this->_language_codes)) ."');"
					)->each(function($info){
						foreach ($info as $key => $value) {
							if (in_array($key, ['id', 'sold_out_status_id', 'language_code'])) continue;
							if (empty($this->_data['sold_out_status'][$key])) {
								$this->_data['sold_out_status'][$key] = $value;
							}
						}
					});

					break;

				case 'tax':

					$this->_data['tax'] = tax::get_tax($this->final_price, $this->tax_class_id);

					break;

				default:

					$result = database::query(
						"select * from ". DB_TABLE_PREFIX ."products
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					);

					if ($result->num_rows) {
						$row = $result->fetch();
					} else {
						$row = array_fill_keys($result->fields(), null);
					}

					foreach ($row as $key => $value) {
						$this->_data[$key] = $value;
					}

					$this->_data['keywords'] = preg_split('#\s*,\s*#', (string)$this->_data['keywords'], -1, PREG_SPLIT_NO_EMPTY);

					break;
			}
		}
	}

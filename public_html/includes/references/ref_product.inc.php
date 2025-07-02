<?php

	class ref_product extends abs_reference_entity {

		protected $_language_codes;
		protected $_currency_codes;
		protected $_customer_group_id;

		function __construct($product_id, $language_code=null, $currency_code=null, $customer_group_id=null) {

			if (!$language_code) {
				$language_code = language::$selected['code'];
			}

			if (!$currency_code) {
				$currency_code = currency::$selected['code'];
			}

			if (!$customer_group_id) {
				$customer_group_id = customer::$data['group_id'];
			}

			$this->_data['id'] = (int)$product_id;

			$this->_customer_group_id = $customer_group_id;

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
						"select pa.id, ag.code, pa.group_id, pa.value_id, pa.custom_value, ag.name as group_name, av.name as value_name, pa.custom_value
						from ". DB_TABLE_PREFIX ."products_attributes pa
						left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = pa.group_id)
						left join ". DB_TABLE_PREFIX ."attribute_values av on (av.id = pa.value_id)
						where product_id = ". (int)$this->_data['id'] ."
						order by pa.priority, group_name, value_name, custom_value;"
					)->fetch_all(function($attribute){

						$attribute['group_name'] = json_decode($attribute['group_name'], true) ?: [];
						$attribute['value_name'] = json_decode($attribute['value_name'], true) ?: [];

						foreach ($this->_language_codes as $language_code) {
							if (!empty($attribute['group_name'][$language_code])) {
								$attribute['group_name'] = $attribute['group_name'][$language_code];
								break;
							}
						}
						foreach ($this->_language_codes as $language_code) {
							if (!empty($attribute['value_name'][$language_code])) {
								$attribute['value_name'] = $attribute['value_name'][$language_code];
								break;
							}
						}
					});

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
									return "if(json_value(price, '$.". database::input($currency_code) ."') != 0, json_value(price, '$.". database::input($currency_code) ."') * ". currency::$currencies[$currency_code]['value'] .", null)";
								}, $this->_currency_codes)) ."
							)
						) as price
						from ". DB_TABLE_PREFIX ."campaigns_products
						where product_id = ". (int)$this->_data['id'] ."
						and campaign_id in (
							select id from ". DB_TABLE_PREFIX ."campaigns
							where status
							and (valid_from is null or valid_from <= '". date('Y-m-d H:i:s') ."')
							and (valid_to is null or valid_to >= '". date('Y-m-d H:i:s') ."')
						)
						group by product_id
						limit 1;"
					)->fetch();

					break;

				case 'categories':

					$this->_data['categories'] = [];

					database::query(
						"select id, name
						from ". DB_TABLE_PREFIX ."categories
						where id in (
							select category_id from ". DB_TABLE_PREFIX ."products_to_categories
							where product_id = ". (int)$this->_data['id'] ."
						);"
					)->each(function($category) {

						foreach ($this->_language_codes as $language_code) {
							$category['name'] = json_decode($category['name'], true) ?: [];
							if (!empty($category['name'][$language_code])) {
								$category['name'] = $category['name'][$language_code];
								break;
							}
						}

						return $this->_data['categories'][$category['id']] = $category['name'];
					});

					break;

				case 'default_category':

					$this->_data['default_category'] = 0;

					if (empty($this->default_category_id)) return;

					$this->_data['default_category'] = reference::category($this->default_category_id, $this->_language_codes[0]);

					break;

				case 'delivery_status':

					$this->_data['delivery_status'] = database::query(
						"select * from ". DB_TABLE_PREFIX ."delivery_statuses
						where id = ". (int)$this->_data['delivery_status_id'] ."
						limit 1;"
					)->fetch(function($status){

						foreach ([
							'name',
							'description',
						] as $field) {
							$status[$field] = json_decode($status[$field], true) ?: [];
							foreach ($this->_language_codes as $language_code) {
								if (!empty($status[$field][$language_code])) {
									$status[$field] = $status[$field][$language_code];
									break;
								}
							}
						}

						return $status;
					});

					break;

				case 'final_price':

					// Regular Price
					$this->_data['final_price'] = $this->price;

					// Campaign Price
					if (isset($this->campaign['price']) && $this->campaign['price'] > 0 && $this->campaign['price'] < $this->_data['final_price']) {
						$this->_data['final_price'] = $this->campaign['price'];
					}

					// Customer Group Price
					if ($this->_customer_group_id) {

						$customer_price = (float)database::query(
							"select coalesce(
								". implode(", ", array_map(function($currency){
									return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
								}, currency::$currencies)) ."
							) / min_quantity as customer_price
							from ". DB_TABLE_PREFIX ."products_prices
							where product_id = ". (int)$this->_data['id'] ."
							and customer_group_id = ". (int)$this->_customer_group_id ."
							order by min_quantity asc
							limit 1;"
						)->fetch('customer_price');

						if ($customer_price && $customer_price < $this->_data['final_price']) {
							$this->_data['final_price'] = $customer_price;
						}
					}

					break;

				case 'images':

					$this->_data['images'] = database::query(
						"select * from ". DB_TABLE_PREFIX ."products_images
						where product_id = ". (int)$this->_data['id'] ."
						order by priority asc, id asc;"
					)->fetch_all('filename');

					break;

				case 'customizations':

					$this->_data['customizations'] = [];

					database::query(
						"select pc.*, ag.name
						from ". DB_TABLE_PREFIX ."products_customizations pc
						left join ". DB_TABLE_PREFIX ."attribute_groups ag on (ag.id = pc.group_id)
						where product_id = ". (int)$this->_data['id'] ."
						order by priority;"
					)->fetch(function($customization){

						// Group
						$customization['group_name'] = json_decode($customization['group_name'], true) ?: [];
						foreach ($this->_language_codes as $language_code) {
							if (!empty($customization['group_name'][$language_code])) {
								$customization['group_name'] = $customization['group_name'][$language_code];
								break;
							}
						}

						// Values
						$customization['values'] = [];

						database::query(
							"select pcv.*, av.name
							from ". DB_TABLE_PREFIX ."products_customizations_values pcv
							left join ". DB_TABLE_PREFIX ."attribute_values av on (av.value_id = pcv.value_id)
							where pcv.product_id = ". (int)$this->_data['id'] ."
							and pcv.group_id = ". (int)$customization['group_id'] ."
							order by pcv.priority;"
						)->each(function($value) use ($customization) {

							if (!empty($value['value_id'])) {

								$value['name'] = json_decode($value['name'], true) ?: [];

								foreach ($this->_language_codes as $language_code) {
									if (!empty($value['name'][$language_code])) {
										$value['name'] = $value['name'][$language_code];
										break;
									}
								}

							} else {
								$value['name'] = $value['custom_value'];
							}

							// Price Adjust
							$price_adjustment = 0;
							$value['price_adjustment'] = json_decode($value['price_adjustment'], true);

							foreach ($this->_currency_codes as $currency_code) {

								if (!isset($value['price_adjustment'][$currency_code]) || $value['price_adjustment'][$currency_code] == 0) {
									continue;
								}

								switch ($value['price_modifier']) {
									case '+':
										$price_adjustment = currency::convert($value['price_adjustment'][$currency_code], $currency_code, settings::get('store_currency_code'));
										break;

									case '%':
										$price_adjustment = $this->price * currency::convert($value['price_adjustment'][$currency_code], $currency_code, settings::get('store_currency_code')) / 100;
										break;

									case '*':
										$price_adjustment = $this->price * currency::convert($value['price_adjustment'][$currency_code], $currency_code, settings::get('store_currency_code'));
										break;

									case '=':
										$price_adjustment = currency::convert($value['price_adjustment'][$currency_code], $currency_code, settings::get('store_currency_code')) - $this->price;
										break;

									default:
										trigger_error('Unknown price modifier for customization', E_USER_WARNING);
										break 2;
								}

								if ($price_adjustment) {
									break;
								}
							}

							if ($price_adjustment && !empty($this->campaign['price'])) {
								$price_adjustment = $price_adjustment * $this->campaign['price'] / $this->price;
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
							". implode(", ", array_map(function($currency){
								return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
							}, currency::$currencies)) ."
						) / min_quantity as regular_price
						from ". DB_TABLE_PREFIX ."products_prices
						where product_id = ". (int)$this->_data['id'] ."
						and customer_group_id is null
						order by min_quantity asc
						limit 1;"
					)->fetch('regular_price');

					break;

				case 'total_quantity':
				case 'num_stock_options':

					$this->_data['quantity'] = null;
					$this->_data['num_stock_options'] = null;

					$stock_options = database::query(
						"select count(pso.id) as num_stock_options, sum(si.quantity) as total_quantity
						from ". DB_TABLE_PREFIX ."products_stock_options pso
						left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
						where pso.product_id = ". (int)$this->_data['id'] ."
						group by pso.product_id;"
					)->fetch();

					$this->_data['num_stock_options'] = $stock_options['num_stock_options'];

					if ($stock_options['num_stock_options']) {
						$this->_data['total_quantity'] = $stock_options['total_quantity'];
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

					$this->_data['quantity_available'] = $this->total_quantity - $this->_data['quantity_reserved'];

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
						"select id, decimals, separate,
							json_value(name, '$.".database::input(language::$selected['code'])."') as name,
							json_value(description, '$.".database::input(language::$selected['code'])."') as description
						from ". DB_TABLE_PREFIX ."quantity_units
						where id = ". (int)$this->quantity_unit_id ."
						limit 1;"
					)->fetch();

					break;

				case 'stock_options':

					$this->_data['stock_options'] = [];

					if ($this->stock_option_type != 'variant') {
						break;
					}

					$this->_data['stock_options'] = database::query(
						"select si.*, pso.*,
							json_value(si.name, '$.".database::input(language::$selected['code'])."') as name,
							ifnull(oi.quantity_reserved, 0) as quantity_reserved,
							si.quantity - ifnull(oi.quantity_reserved, 0) as quantity_available

						from ". DB_TABLE_PREFIX ."products_stock_options pso

						left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)

						left join (
							select product_id, stock_option_id, sum(quantity) as quantity_reserved
							from ". DB_TABLE_PREFIX ."orders_items
							where order_id in (
								select id from ". DB_TABLE_PREFIX ."orders
								where order_status_id in (
									select id from ". DB_TABLE_PREFIX ."order_statuses
									where stock_action = 'reserve'
								)
							)
							group by stock_option_id
						) oi on (oi.product_id = pso.product_id and oi.stock_option_id = pso.id)
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
						"select id, orderable,
							json_value(name, '$.".database::input(language::$selected['code'])."') as name,
							json_value(description, '$.".database::input(language::$selected['code'])."') as description
						from ". DB_TABLE_PREFIX ."sold_out_statuses
						where id = ". (int)$this->sold_out_status_id ."
						limit 1;"
					)->fetch();

					break;

				case 'tax':

					$this->_data['tax'] = tax::get_tax($this->final_price, $this->tax_class_id);

					break;

				default:

					$product = database::query(
						"select * from ". DB_TABLE_PREFIX ."products
						where id = ". (int)$this->_data['id'] ."
						limit 1;"
					)->fetch(function($product){

						foreach ([
							'name',
							'short_description',
							'description',
							'technical_data',
							'head_title',
							'meta_description',
							'keywords',
							'synonyms',
						] as $field) {

							$product[$field] = json_decode($product[$field], true) ?: [];

							foreach ($this->_language_codes as $language_code) {
								if (!empty($product[$field][$language_code])) {
									$product[$field] = $product[$field][$language_code];
									continue 2;
								}
							}

							$product[$field] = '';
						}

						if ($product['autofill_technical_data']) {

							$product['technical_data'] = '';

							foreach ($this->attributes as $attribute) {
								$product['technical_data'] = $attribute['group_name'] .': '. $attribute['value_name'] . PHP_EOL;
							}

							$product['technical_data'] = rtrim($product['technical_data']);
						}

						$product['synonyms'] = preg_split('#\s*,\s*#', (string)$product['synonyms'], -1, PREG_SPLIT_NO_EMPTY);
						$product['keywords'] = preg_split('#\s*,\s*#', (string)$product['keywords'], -1, PREG_SPLIT_NO_EMPTY);

						return $product;
					});

					if (!$product) {
						$product = database::query(
							"show fields from ". DB_TABLE_PREFIX ."products;"
						)->fetch(function($field){
							return database::create_variable($field);
						});
					}

					foreach ($product as $key => $value) {
						$this->_data[$key] = $value;
					}

					break;
			}
		}

		public function calculate_price($parameters=[]) {

			try {

				$price_extras = 0;

				if (empty($parameters['quantity'])) {
					$parameters['quantity'] = 1;
				}

				// Cleanup userdata
				if (!empty($parameters['userdata'])) {

					if (!is_array($parameters['userdata'])) {
						throw new Exception('Invalid userdata');
					}

					$array_filter_recursive = function($array) use (&$array_filter_recursive) {

						foreach ($array as $i => $value) {
							if (is_array($value)) $array[$i] = $array_filter_recursive($value);
						}

						return array_filter($array, function($v) {
							if (is_array($v)) return !empty($v);
							return strlen(trim($v));
						});
					};

					$parameters['userdata'] = $array_filter_recursive($parameters['userdata']);
				}

				// Validate userdata
				if (!empty($parameters['userdata'])) {

					// Build customizations structure
					foreach ($this->customizations as $customization) {

						// Check group
						$possible_groups = array_filter(array_unique(reference::attribute_group($customization['group_id'])->name));
						$matched_groups = array_intersect(array_keys($parameters['userdata']), $possible_groups);
						$matched_group = array_shift($matched_groups);

						if (empty($matched_group) && empty($customization['required'])) {
							continue;
						}

						if (empty($parameters['userdata'][$matched_group]) && !empty($option['required'])) {
							throw new Exception(t('error_set_product_customizations', 'Please set your product customizations'));
						}

						// Check values
						switch ($customization['function']) {

							case 'checkbox':

								$selected_values = preg_split('#\s*,\s*#', $parameters['userdata'][$matched_group], -1, PREG_SPLIT_NO_EMPTY);

								$matched_values = [];
								foreach ($customization['values'] as $value) {

									$possible_values = array_unique(
										array_merge(
											[$value['name']],
											!empty(reference::attribute_group($customization['group_id'])->values[$value['value_id']]) ? array_filter(array_values(reference::attribute_group($customization['group_id'])->values[$value['value_id']]['name']), 'strlen') : []
										)
									);

									if (empty($customization['required'])) {
										array_unshift($possible_values, '');
									}

									if ($matched_value = array_intersect($selected_values, $possible_values)) {
										$matched_values[] = $matched_value;
										$price_extras += $value['price_adjustment'];
										$found_match = true;
									}
								}

								if (empty($found_match)) {
									throw new Exception(strtr(t('error_must_select_valid_customization_for_group', 'You must select a valid customization for %group'), ['%group' => $matched_group]));
								}

								break;

							case 'radio':
							case 'select':

								foreach ($customization['values'] as $value) {

									$possible_values = array_unique(
										array_merge(
											[$value['name']],
											!empty(reference::attribute_group($customization['group_id'])->values[$value['value_id']]) ? array_filter(array_values(reference::attribute_group($customization['group_id'])->values[$value['value_id']]['name']), 'strlen') : []
										)
									);

									if (empty($customization['required'])) {
										array_unshift($possible_values, '');
									}

									if ($matched_value = array_intersect([$parameters['userdata'][$matched_group]], $possible_values)) {
										$matched_value = array_shift($matched_value);
										$price_extras += $value['price_adjustment'];
										$found_match = true;
										break;
									}
								}

								if (empty($found_match)) {
									throw new Exception(strtr(t('error_must_select_valid_customization_for_group', 'You must select a valid customization for %group'), ['%group' => $matched_group]));
								}

								break;

							case 'text':
							case 'textarea':

								$matched_value = $parameters['userdata'][$matched_group];

								if (empty($matched_value) && !empty($customization['required'])) {
									throw new Exception(strtr(t('error_must_provide_valid_input_for_group', 'You must provide a valid input for %group'), ['%group' => $matched_group]));
								}

								break;
						}
					}
				}

				$price = $this->final_price + $price_extras;

				return $price;

			} catch (Exception $e) {
				return false;
			}
		}
	}

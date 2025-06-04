<?php

	class ent_shopping_cart {
		public $data;
		public $previous;
		public $shipping;
		public $payment;

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
				"show fields from ". DB_TABLE_PREFIX ."shopping_carts;"
			)->each(function($field) {
				switch (true) {

					case (preg_match('#^customer_#', $field['Field'])):
						$this->data['customer'][preg_replace('#^(customer_)#', '', $field['Field'])] = database::create_variable($field);
						break;

					case (preg_match('#^shipping_(?!option)#', $field['Field'])):
						$this->data['shipping_address'][preg_replace('#^shipping_#', '', $field['Field'])] = database::create_variable($field);
						break;

					case (preg_match('#^payment_option#', $field['Field'])):
						$this->data['payment_option'][preg_replace('#^payment_option_#', '', $field['Field'])] = database::create_variable($field);
						break;

					case (preg_match('#^shipping_option#', $field['Field'])):
						$this->data['shipping_option'][preg_replace('#^shipping_option_#', '', $field['Field'])] = database::create_variable($field);
						break;

					default:
						$this->data[$field['Field']] = database::create_variable($field);
						break;
				}
			});

				// Default values
			$this->data = array_merge($this->data, [
				'uid' => uniqid(),
				'weight_unit' => settings::get('store_weight_unit'),
				'currency_code' => currency::$selected['code'],
				'currency_value' => currency::$selected['value'],
				'language_code' => language::$selected['code'],
				'incoterm' => settings::get('default_incoterm'),
				'items' => [],
				'num_items' => 0,
				'subtotal' => 0,
				'subtotal_tax' => 0,
				'total' => 0,
				'total_tax' => 0,
				'display_prices_including_tax' => settings::get('default_display_prices_including_tax'),
			]);

			$this->data['shipping_option']['userdata'] = [];
			$this->data['payment_option']['userdata'] = [];

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^([0-9]+|[a-f0-9]{13})$#', $id)) {
				throw new Exception('Invalid shopping cart (ID: '. $id .')');
			}

			$this->reset();

			$shopping_cart = database::query(
				"select * from ". DB_TABLE_PREFIX ."shopping_carts
				where ". (preg_match('#^[a-f0-9]{13}$#', $id) ? "uid = '". database::input($id) ."'" : "id = '". database::input($id) ."'") ."
				limit 1;"
			)->fetch();

			if (!$shopping_cart) {
				throw new Exception('Could not find shopping cart in database (ID: '. $id .')');
			}

			$this->data = array_replace($this->data, array_intersect_key($shopping_cart, $this->data));

			foreach ($shopping_cart as $field => $value) {
				switch (true) {

					case (preg_match('#^customer_#', $field)):
						$this->data['customer'][preg_replace('#^(customer_)#', '', $field)] = $value;
						break;

					case (preg_match('#^shipping_(?!option)#', $field)):
						$this->data['shipping_address'][preg_replace('#^(shipping_)#', '', $field)] = $value;
						break;

					case (preg_match('#^payment_option#', $field)):
						$this->data['payment_option'][preg_replace('#^(payment_option_)#', '', $field)] = $value;
						break;

					case (preg_match('#^shipping_option#', $field)):
						$this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field)] = $value;
						break;
				}
			}

			database::query(
				"select sci.*, p.quantity_min, p.quantity_max, p.quantity_step,
					json_value(qu.name, '$.". database::input(language::$selected['code']) ."' ) as quantity_unit_name
				from ". DB_TABLE_PREFIX ."shopping_carts_items sci
				left join ". DB_TABLE_PREFIX ."products p on (p.id = sci.product_id)
				where sci.cart_id = ". (int)$this->data['id'] ."
				order by sci.priority, sci.id;"
			)->each(function($item) {
				$item['userdata'] = $item['userdata'] ? json_decode($item['userdata'], true) : '';

				try {
					$this->validate_item($item);
				} catch (Exception $e) {
					$item['error'] = $e->getMessage();
				}

				$this->data['items'][] = $item;
			});

			$this->_refresh_total();

			$this->previous = $this->data;
		}

		public function save() {

			if (empty($this->data['public_key'])) {
				$this->data['public_key'] = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', mt_rand(5, 10))), 0, 32);
			}

			if (!$this->data['id']) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."shopping_carts
					(uid, created_at)
					values ('". database::input($this->data['uid']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."shopping_carts
				set uid = '". database::input($this->data['uid']) ."',
					customer_id = ". (int)$this->data['customer']['id'] .",
					customer_email = '". database::input($this->data['customer_address']['email']) ."',
					customer_tax_id = '". database::input($this->data['customer_address']['tax_id']) ."',
					customer_company = '". database::input($this->data['customer_address']['company']) ."',
					customer_firstname = '". database::input($this->data['customer_address']['firstname']) ."',
					customer_lastname = '". database::input($this->data['customer_address']['lastname']) ."',
					customer_address1 = '". database::input($this->data['customer_address']['address1']) ."',
					customer_address2 = '". database::input($this->data['customer_address']['address2']) ."',
					customer_city = '". database::input($this->data['customer_address']['city']) ."',
					customer_postcode = '". database::input($this->data['customer_address']['postcode']) ."',
					customer_country_code = '". database::input($this->data['customer_address']['country_code']) ."',
					customer_zone_code = '". database::input($this->data['customer_address']['zone_code']) ."',
					customer_phone = '". database::input($this->data['customer_address']['phone']) ."',
					shipping_company = '". database::input($this->data['shipping_address']['company']) ."',
					shipping_firstname = '". database::input($this->data['shipping_address']['firstname']) ."',
					shipping_lastname = '". database::input($this->data['shipping_address']['lastname']) ."',
					shipping_address1 = '". database::input($this->data['shipping_address']['address1']) ."',
					shipping_address2 = '". database::input($this->data['shipping_address']['address2']) ."',
					shipping_city = '". database::input($this->data['shipping_address']['city']) ."',
					shipping_postcode = '". database::input($this->data['shipping_address']['postcode']) ."',
					shipping_country_code = '". database::input($this->data['shipping_address']['country_code']) ."',
					shipping_zone_code = '". database::input($this->data['shipping_address']['zone_code']) ."',
					shipping_phone = '". database::input($this->data['shipping_address']['phone']) ."',
					shipping_option_id = '". (!empty($this->data['shipping_option']['id']) ? database::input($this->data['shipping_option']['id']) : '') ."',
					shipping_option_name = '". (!empty($this->data['shipping_option']['name']) ? database::input($this->data['shipping_option']['name']) : '') ."',
					shipping_option_userdata = '". (!empty($this->data['shipping_option']['userdata']) ? database::input(json_encode($this->data['shipping_option']['userdata'], JSON_UNESCAPED_SLASHES)) : '') ."',
					payment_option_id = '". (!empty($this->data['payment_option']['id']) ? database::input($this->data['payment_option']['id']) : '') ."',
					payment_option_name = '". (!empty($this->data['payment_option']['name']) ? database::input($this->data['payment_option']['name']) : '') ."',
					payment_option_userdata = '". (!empty($this->data['payment_option']['userdata']) ? database::input(json_encode($this->data['payment_option']['userdata'], JSON_UNESCAPED_SLASHES)) : '') ."',
					payment_terms = '". database::input($this->data['payment_terms']) ."',
					incoterm = '". database::input($this->data['incoterm']) ."',
					language_code = '". database::input($this->data['language_code']) ."',
					currency_code = '". database::input($this->data['currency_code']) ."',
					weight_total = ". (float)$this->data['weight_total'] .",
					weight_unit = '". database::input($this->data['weight_unit']) ."',
					display_prices_including_tax = ". (int)$this->data['display_prices_including_tax'] .",
					subtotal = ". (float)$this->data['subtotal'] .",
					subtotal_tax = ". (float)$this->data['subtotal_tax'] .",
					public_key = '". database::input($this->data['public_key']) ."',
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			$i = 1;
			foreach ($this->data['items'] as $key => $item) {

				if (empty($item['id'])) {
					database::query(
						"insert into ". DB_TABLE_PREFIX ."shopping_carts_items
						(cart_id)
						values (". (int)$this->data['id'] .");"
					);

					$this->data['items'][$key]['id'] = $item['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."shopping_carts_items
					set `key` = '". database::input($item['key']) ."',
						product_id = ". (int)$item['product_id'] .",
						stock_option_id = ". (int)$item['stock_option_id'] .",
						name = '". database::input($item['name']) ."',
						userdata = '". (!empty($item['userdata']) ? database::input(json_encode($item['userdata'], JSON_UNESCAPED_SLASHES)) : '') ."',
						code = '". database::input($item['code']) ."',
						sku = '". database::input($item['sku']) ."',
						gtin = '". database::input($item['gtin']) ."',
						taric = '". database::input($item['taric']) ."',
						image = '". database::input($item['image']) ."',
						quantity = ". (float)$item['quantity'] .",
						quantity_unit_id = ". (int)$item['quantity_unit_id'] .",
						price = ". (float)$item['price'] .",
						final_price = ". (float)$item['final_price'] .",
						tax = ". (float)$item['tax'] .",
						tax_class_id = ". (int)$item['tax_class_id'] .",
						discount = ". (float)$this->data['discount'] .",
						discount_tax = ". (float)$this->data['discount_tax'] .",
						sum = ". (float)$item['sum'] .",
						sum_tax = ". (float)$item['sum_tax'] .",
						weight = ". (float)$item['weight'] .",
						weight_unit = '". database::input($item['weight_unit']) ."',
						length = ". (float)$item['length'] .",
						width = ". (float)$item['width'] .",
						height = ". (float)$item['height'] .",
						length_unit = '". database::input($item['length_unit']) ."',
						priority = ". (int)$i++ ."
					where id = ". (int)$item['id'] ."
					and cart_id = ". (int)$this->data['id'] ."
					limit 1;"
				);
			}

			$this->previous = $this->data;
		}

		public function add_product($product_id, $stock_option_id='', $quantity=1, $halt_on_error=false) {

			$product = reference::product($product_id);
			$quantity = round($quantity, $product->quantity_unit ? (int)$product->quantity_unit['decimals'] : 0, PHP_ROUND_HALF_UP);

			// Set item key
			if (!empty($product->quantity_unit['separate'])) {
				$item_key = uniqid();
			} else {
				$item_key = crc32(json_encode([$product->id, $stock_option_id]));
			}

			$item = [
				'id' => null,
				'product_id' => (int)$product->id,
				'stock_option_id' => $stock_option_id,
				'key' => $item_key,
				'image' => $product->image,
				'name' => $product->name,
				'code' => $product->code,
				'sku' => $product->sku,
				'gtin' => $product->gtin,
				'taric' => $product->taric,
				'price' => $product->final_price,
				'tax' => tax::get_tax($product->final_price, $product->tax_class_id),
				'discount' => $product->price - $product->final_price,
				'discount_tax' => tax::get_tax($product->price - $product->final_price, $product->tax_class_id),
				'sum' => 0,
				'sum_tax' => 0,
				'tax_class_id' => $product->tax_class_id,
				'quantity' => round($quantity, $product->quantity_unit['decimals'], PHP_ROUND_HALF_UP),
				'quantity_unit_id' => $product->quantity_unit['id'],
				'quantity_min' => $product->quantity_min,
				'quantity_max' => $product->quantity_max,
				'quantity_step' => $product->quantity_step,
				'weight' => $product->weight,
				'weight_unit' => $product->weight_unit,
				'length' => $product->length,
				'width' => $product->width,
				'height' => $product->height,
				'length_unit' => $product->length_unit,
				'error' => '',
			];

			if (($stock_option_key = array_search($item['stock_option_id'], array_column($product->stock_options, 'stock_option_id', 'id'))) !== false) {
				$stock_option = &$product->stock_options[$stock_option_key];

				$item['sku'] = fallback($stock_option['sku'], $item['sku']);
				$item['weight'] = fallback($stock_option['weight'], $item['weight']);
				$item['weight_unit'] = fallback($stock_option['weight_unit'], $item['weight_unit']);
				$item['length'] = fallback($stock_option['length'], $item['length']);
				$item['width'] = fallback($stock_option['width'], $item['width']);
				$item['height'] = fallback($stock_option['height'], $item['height']);
				$item['length_unit'] = fallback($stock_option['length_unit'], $item['length_unit']);
			}

			$item['sum'] = $item['quantity'] * ($item['price'] - $item['discount']);
			$item['sum_tax'] = $item['quantity'] * ($item['tax'] - $item['discount_tax']);

			$this->validate_item($item);

			// Round currency amount (Gets rid of hidden decimals)
			$item['price'] = currency::round($item['price'], currency::$selected['code']);
			$item['tax'] = currency::round($item['tax'], currency::$selected['code']);

			// Add item to cart or increase quantity of an existing item
			if (!empty($this->data['items'][$item_key])) {
				$this->data['items'][$item_key]['quantity'] += $quantity;
			} else {
				$this->data['items'][$item_key] = $item;
			}

			$this->_refresh_total();
		}

		public function validate_item($item) {

			$product = reference::product($item['product_id']);

			if (!$product->id) {
				throw new Exception(language::translate('error_item_not_a_valid_product', 'The item is not a valid product'));
			}

			if (!$product->status) {
				throw new Exception(language::translate('error_product_currently_not_available_for_purchase', 'The product is currently not available for purchase'));
			}

			if (!empty($product->valid_from) && $product->valid_from > date('Y-m-d H:i:s')) {
				throw new Exception(strtr(language::translate('error_product_cannot_be_purchased_until_date', 'The product cannot be purchased until %date'), ['%date' => functions::datetime_format('date', $product->valid_from)]));
			}

			if (!empty($product->valid_to) && $product->valid_to < date('Y-m-d H:i:s')) {
				throw new Exception(strtr(language::translate('error_product_can_no_longer_be_purchased', 'The product can no longer be purchased as of %date'), ['%date' => functions::datetime_format('date', $product->valid_to)]));
			}

			if ($item['quantity'] <= 0) {
				throw new Exception(language::translate('error_invalid_item_quantity', 'Invalid item quantity'));
			}

			if (empty($item['stock_option_id']) && $product->stock_options) {
				throw new Exception(language::translate('error_muset_select_stock_option', 'You must select a stock option'));
			}

			if (!empty($item['stock_option_id']) && array_search($item['stock_option_id'], array_column($product->stock_options, 'stock_option_id')) === false) {
				throw new Exception(language::translate('error_invalid_stock_option', 'Invalid stock option'));
			}

			if (!empty($item['stock_option_id'])) {
				if (($stock_option_key = array_search($item['stock_option_id'], array_column($product->stock_options, 'stock_option_id', 'id'))) !== false) {
					$stock_option = &$product->stock_options[$stock_option_key];

					if (!empty($product->sold_out_status) && empty($product->sold_out_status['orderable'])) {
						$available_quantity_after_purchase = $stock_option['quantity_available'] - $item['quantity'] + (isset($this->data['items'][$item['key']]) ? $this->data['items'][$item['key']]['quantity'] : 0);
						if ($available_quantity_after_purchase < 0) {
							throw new Exception(language::translate('error_not_enough_products_in_stock_for_option', 'Not enough products in stock for the selected option') .' ('. $stock_option['sku'] .')');
						}
					}
				}
			}
		}

		public function update_item($item_key, $quantity) {

			if (!isset($this->data['items'][$item_key])) {
				notices::add('errors', 'The item does not exist in cart.');
				return;
			}

			if ($this->data['items'][$item_key]['quantity'] == $quantity) {
				return;
			}

			if ($quantity <= 0) {
				self::remove($item_key, true);
				return;
			}

			// Re-add quantity for validation
			$item = &$this->data['items'][$item_key];
			$item['quantity'] = 0;

			$this->add($item['product_id'], $quantity, true, $item_key);

			self::_refresh_total();
		}

		public function remove_item($item_key) {

			if (!isset($this->data['items'][$item_key])) return;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."shopping_carts_items
				where cart_id = '". database::input($this->data['id']) ."'
				and `key` = '". database::input($this->data['items'][$item_key]['key']) ."'
				limit 1;"
			);

			unset($this->data['items'][$item_key]);

			$this->_refresh_total();
		}

		private function _refresh_total() {

			$this->data['num_items'] = 0;

			$this->data['subtotal'] = 0;
			$this->data['subtotal_tax'] = 0;

			$this->data['total'] = 0;
			$this->data['total_tax'] = 0;

			foreach ($this->data['items'] as $item) {
				$num_items = $item['quantity'];

				if (!empty($item['quantity_unit_id']) && reference::quantity_unit($item['quantity_unit_id'])->separate) {
					$num_items = 1;
				}

				$this->data['num_items'] += $num_items;
				$this->data['subtotal'] += $item['price'] * $item['quantity'];
				$this->data['subtotal_tax'] += $item['tax'] * $item['quantity'];
				$this->data['total'] += $item['price'] * $item['quantity'];
				$this->data['total_tax'] += $item['tax'] * $item['quantity'];
			}
		}

		public function validate() {

			// Validate items
			if (empty($this->data['items'])) {
				return language::translate('error_order_missing_items', 'The order does not contain any items');
			}

			foreach ($this->data['items'] as $item) {
				if (!empty($item['error'])) {
					return language::translate('error_cart_contains_errors', 'Your cart contains errors');
				}
			}

			// Validate billing details
			try {

				if (empty($this->data['customer']['firstname'])) {
					throw new Exception(language::translate('error_must_provide_firstname', 'You must provide a first name'));
				}

				if (empty($this->data['customer']['lastname'])) {
					throw new Exception(language::translate('error_must_provide_lastname', 'You must provide a last name'));
				}

				if (empty($this->data['customer']['address1'])) {
					throw new Exception(language::translate('error_must_provide_address1', 'You must provide an address'));
				}

				if (empty($this->data['customer']['city'])) {
					throw new Exception(language::translate('error_must_provide_city', 'You must provide a city'));
				}

				if (empty($this->data['customer']['country_code'])) {
					throw new Exception(language::translate('error_must_select_country', 'You must select a country'));
				}

				if (empty($this->data['customer']['email'])) {
					throw new Exception(language::translate('error_must_provide_email', 'You must provide an email address'));
				}

				if (empty($this->data['customer']['phone'])) {
					throw new Exception(language::translate('error_must_provide_phone', 'You must provide a phone number'));
				}

				if (!functions::validate_email($this->data['customer']['email'])){
					throw new Exception(language::translate('error_invalid_email_address', 'Invalid email address'));
				}

				if (reference::country($this->data['customer']['country_code'])->tax_id_format) {
					if (!empty($this->data['customer']['tax_id'])) {
						if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->tax_id_format .'#i', $this->data['customer']['tax_id'])) {
							throw new Exception(language::translate('error_invalid_tax_id_format', 'Invalid tax ID format'));
						}
					}
				}

				if (reference::country($this->data['customer']['country_code'])->postcode_format) {
					if (!empty($this->data['customer']['postcode'])) {
						if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->postcode_format .'#i', $this->data['customer']['postcode'])) {
							throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format'));
						}
					} else {
						throw new Exception(language::translate('error_must_provide_postcode', 'You must provide a postcode'));
					}
				}

				if (settings::get('customer_field_zone') && reference::country($this->data['customer']['country_code'])->zones) {
					if (empty($this->data['customer']['zone_code']) && reference::country($this->data['customer']['country_code'])->zones) throw new Exception(language::translate('error_must_select_zone', 'You must select a zone.'));
				}

				if (empty($this->data['customer']['id'])) {
					if (database::query(
						"select id from ". DB_TABLE_PREFIX ."customers
						where email = '". database::input($this->data['customer']['email']) ."'
						and status = 0
						limit 1;"
					)->num_rows) {
						throw new Exception(language::translate('error_customer_account_is_disabled', 'The customer account is disabled'));
					}
				}

			} catch (Exception $e) {
				return language::translate('title_customer_details', 'Customer Details') .': '. $e->getMessage();
			}

			try {
				if (!empty($this->data['different_shipping_address'])) {

					if (empty($this->data['shipping_address']['firstname'])){
						throw new Exception(language::translate('error_must_provide_firstname', 'You must provide a first name'));
					}

					if (empty($this->data['shipping_address']['lastname'])) {
						throw new Exception(language::translate('error_must_provide_lastname', 'You must provide a last name'));
					}

					if (empty($this->data['shipping_address']['address1'])){
						throw new Exception(language::translate('error_must_provide_address1', 'You must provide an address'));
					}

					if (empty($this->data['shipping_address']['city'])){
						throw new Exception(language::translate('error_must_provide_city', 'You must provide a city'));
					}

					if (empty($this->data['shipping_address']['country_code'])){
						throw new Exception(language::translate('error_must_select_country', 'You must select a country'));
					}

					if (reference::country($this->data['shipping_address']['country_code'])->postcode_format) {
						if (!empty($this->data['shipping_address']['postcode'])) {
							if (!preg_match('#'. reference::country($this->data['shipping_address']['country_code'])->postcode_format .'#i', $this->data['shipping_address']['postcode'])) {
								throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format.'));
							}
						} else {
							throw new Exception(language::translate('error_must_provide_postcode', 'You must provide a postcode'));
						}
					}

					if (settings::get('customer_field_zone') && reference::country($this->data['shipping_address']['country_code'])->zones) {
						if (empty($this->data['shipping_address']['zone_code']) && reference::country($this->data['shipping_address']['country_code'])->zones){
							return language::translate('error_must_select_zone', 'You must select a zone.');
						}
					}
				}

			} catch (Exception $e) {
				return language::translate('title_shipping_address', 'Shipping Address') .': '. $e->getMessage();
			}

			// Additional customer validation
			$mod_customer = new mod_customer();
			$result = $mod_customer->validate($this->data['customer']);

			if (!empty($result['error'])) {
				return $result['error'];
			}

			// Validate shipping option
			$shipping = new mod_shipping($this->data['shipping_option']);
			$shipping_options = $shipping->options();
			if (!empty($shipping->modules) && count($shipping_options)) {
				if (empty($shipping->selected)) {
					return language::translate('error_no_shipping_method_selected', 'No shipping method selected');
				} else {
					if (($key = array_search($shipping->selected['id'], array_combine(array_keys($shipping_options), array_column($shipping_options, 'id')))) === false) {
						return language::translate('error_invalid_shipping_method_selected', 'Invalid shipping method selected');
					} else if (!empty($shipping_options[$key]['error'])) {
						return language::translate('error_shipping_method_contains_error', 'The selected shipping method contains errors');
					}
				}
			}

			// Validate payment option
			$payment = new mod_payment($this->data['payment_option']);
			$payment_options = $payment->options();
			if (!empty($payment->modules) && count($payment_options)) {
				if (empty($payment->selected)) {
					return language::translate('error_no_payment_method_selected', 'No payment method selected');
				} else {
					if (($key = array_search($payment->selected['id'], array_combine(array_keys($payment_options), array_column($payment_options, 'id')))) === false) {
						return language::translate('error_invalid_payment_method_selected', 'Invalid payment method selected');
					} else if (!empty($payment_options[$key]['error'])) {
						return language::translate('error_payment_method_contains_error', 'The selected payment method contains errors');
					}
				}
			}

			// Additional order validation
			$mod_order = new mod_order();
			$result = $mod_order->validate($this);

			if (!empty($result['error'])) {
				return $result['error'];
			}

			return false;
		}

		public function delete() {

			if (!$this->data['id']) return;

			database::query(
				"delete sc, sci
				from ". DB_TABLE_PREFIX ."shopping_carts sc
				left join ". DB_TABLE_PREFIX ."shopping_carts_items sci on (sci.cart_id = sc.id)
				where sc.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();
		}
	}

<?php

	class ent_order {
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
				"show fields from ". DB_TABLE_PREFIX ."orders;"
			)->each(function($field) {
				switch (true) {

					case (preg_match('#^customer_#', $field['Field'])):
						$this->data['customer'][preg_replace('#^(customer_)#', '', $field['Field'])] = database::create_variable($field);
						break;

					case (preg_match('#^shipping_(?!option|tracking|purchase)#', $field['Field'])):
						$this->data['customer']['shipping_address'][preg_replace('#^(shipping_)#', '', $field['Field'])] = database::create_variable($field);
						break;

					case (preg_match('#^payment_option#', $field['Field'])):
						$this->data['payment_option'][preg_replace('#^(payment_option_)#', '', $field['Field'])] = database::create_variable($field);
						break;

					case (preg_match('#^shipping_option#', $field['Field'])):
						$this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field['Field'])] = database::create_variable($field);
						break;

					default:
						$this->data[$field['Field']] = database::create_variable($field);
						break;
				}
			});

			$this->data = array_merge($this->data, [
				'order_status_id' => settings::get('default_order_status_id'),
				'weight_unit' => settings::get('store_weight_unit'),
				'currency_code' => currency::$selected['code'],
				'currency_value' => currency::$selected['value'],
				'language_code' => language::$selected['code'],
				'incoterm' => settings::get('default_incoterm'),
				'items' => [],
				'comments' => [],
				'subtotal' => 0,
				'subtotal_tax' => 0,
				'display_prices_including_tax' => settings::get('default_display_prices_including_tax'),
				'ip_address' => fallback($_SERVER['REMOTE_ADDR']),
				'hostname' => isset($_SERVER['REMOTE_ADDR']) ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : '',
				'user_agent' => fallback($_SERVER['HTTP_USER_AGENT']),
				'domain' => fallback($_SERVER['HTTP_HOST']),
			]);

			$this->data['shipping_option']['userdata'] = [];
			$this->data['payment_option']['userdata'] = [];

			$this->shipping = new mod_shipping($this, $this->data['shipping_option']);
			$this->payment = new mod_payment($this, $this->data['payment_option']);

			$this->data['payment_due'] = &$this->data['total']; // Backwards compatibility <3.0.0
			$this->data['tax_total'] = &$this->data['total_tax']; // Backwards compatibility <3.0.0

			$this->previous = $this->data;
		}

		public function load($id) {

			if (!preg_match('#^[0-9]+$#', $id)) {
				throw new Exception('Invalid order (ID: '. $id .')');
			}

			$this->reset();

			$order = database::query(
				"select * from ". DB_TABLE_PREFIX ."orders
				where id = ". (int)$id ."
				limit 1;"
			)->fetch();

			if (!$order) {
				throw new Exception('Could not find order in database (ID: '. (int)$id .')');
			}

			$this->data = array_replace($this->data, array_intersect_key($order, $this->data));

			foreach ($order as $field => $value) {

				switch (true) {
					case (preg_match('#^customer_#', $field)):
						$this->data['customer'][preg_replace('#^(customer_)#', '', $field)] = $value;
						break;

					case (preg_match('#^shipping_(?!option)#', $field)):
						$this->data['customer']['shipping_address'][preg_replace('#^(shipping_)#', '', $field)] = $value;
						break;

					case (preg_match('#^payment_option#', $field)):
						$this->data['payment_option'][preg_replace('#^(payment_option_)#', '', $field)] = $value;
						break;

					case (preg_match('#^shipping_option#', $field)):
						$this->data['shipping_option'][preg_replace('#^(shipping_option_)#', '', $field)] = $value;
						break;
				}
			}

			$this->data['items'] = database::query(
				"select oi.*, si.quantity as stock_quanity, p.sold_out_status_id
				from ". DB_TABLE_PREFIX ."orders_items oi
				left join ". DB_TABLE_PREFIX ."products p on (p.id = oi.product_id)
				left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
				where oi.order_id = ". (int)$id ."
				order by oi.id;"
			)->fetch_all(function($item) {
				$item['userdata'] = $item['userdata'] ? json_decode($item['userdata'], true) : '';
				$item['sufficient_stock'] = null;

				if (isset($item['stock_quanity'])) {
					if ($item['quantity'] >= $item['stock_quanity']) {
						$item['sufficient_stock'] = true;
					} else {
						$item['sufficient_stock'] = false;
					}
				}

				return $item;
			});

			$this->data['comments'] = database::query(
				"select oc.*, a.username as author_username from ". DB_TABLE_PREFIX ."orders_comments oc
				left join ". DB_TABLE_PREFIX ."administrators a on (a.id = oc.author_id)
				where oc.order_id = ". (int)$id ."
				order by oc.id;"
			)->fetch_all();

			$this->data['payment_due'] = &$this->data['total']; // Backwards compatibility <3.0.0
			$this->data['tax_total'] = &$this->data['total_tax']; // Backwards compatibility <3.0.0

			$this->data['shipping_option']['userdata'] = @json_decode($this->data['shipping_option']['userdata'], true);
			$this->data['payment_option']['userdata'] = @json_decode($this->data['payment_option']['userdata'], true);

			$this->shipping = new mod_shipping($this, $this->data['shipping_option']);
			$this->payment = new mod_payment($this, $this->data['payment_option']);

			$this->previous = $this->data;
		}

		public function save() {

			// Re-calculate total if there are changes
			$this->refresh_total();

			// Log order status change as comment
			if ($this->previous['id'] && $this->data['order_status_id'] != $this->previous['order_status_id']) {
				$this->data['comments'][] = [
					'author' => 'system',
					'text' => strtr(language::translate('text_user_changed_order_status_to_new_status', 'Order status changed to %new_status', settings::get('store_language_code')), [
						'%username' => fallback(administrator::$data['username'], 'system'),
						'%new_status' => reference::order_status($this->data['order_status_id'], settings::get('store_language_code'))->name,
					]),
					'hidden' => 1,
				];
			}

			// Link guests to customer profile
			if (!$this->data['customer']['id'] && $this->data['customer']['email']) {

				$customer = database::query(
					"select id from ". DB_TABLE_PREFIX ."customers
					where email = '". database::input($this->data['customer']['email']) ."'
					limit 1;"
				)->fetch();

				if ($customer) {
					$this->data['customer']['id'] = $customer['id'];
				}
			}

			if (!$this->data['public_key']) {
				$this->data['public_key'] = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', mt_rand(5, 10))), 0, 32);
			}

			if (!$this->data['date_dispatched']) {
				if ($this->data['order_status_id'] && in_array(reference::order_status($this->data['order_status_id'])->state, ['dispatched', 'delivered'])) {
					if (!$this->previous['order_status_id'] || !in_array(reference::order_status($this->previous['order_status_id'])->state, ['dispatched', 'delivered'])) {
						$this->data['date_dispatched'] = date('Y-m-d H:i:s');
					}
				}
			}

			// Insert order
			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_TABLE_PREFIX ."orders
					(created_at)
					values ('". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			// Create custom order number
			if (!$this->data['no']) {
				$this->data['no'] = $this->_generate_order_number();
			}

			// Update order
			database::query(
				"update ". DB_TABLE_PREFIX ."orders
				set no = '". database::input($this->data['no']) ."',
					starred = ". (int)$this->data['starred'] .",
					unread = ". (int)$this->data['unread'] .",
					order_status_id = ". ($this->data['order_status_id'] ? (int)$this->data['order_status_id'] : "null") .",
					customer_id = ". ($this->data['customer']['id'] ? (int)$this->data['customer']['id'] : "null") .",
					customer_tax_id = '". database::input($this->data['customer']['tax_id']) ."',
					customer_company = '". database::input($this->data['customer']['company']) ."',
					customer_firstname = '". database::input($this->data['customer']['firstname']) ."',
					customer_lastname = '". database::input($this->data['customer']['lastname']) ."',
					customer_address1 = '". database::input($this->data['customer']['address1']) ."',
					customer_address2 = '". database::input($this->data['customer']['address2']) ."',
					customer_city = '". database::input($this->data['customer']['city']) ."',
					customer_postcode = '". database::input($this->data['customer']['postcode']) ."',
					customer_country_code = '". database::input($this->data['customer']['country_code']) ."',
					customer_zone_code = '". database::input($this->data['customer']['zone_code']) ."',
					customer_phone = '". database::input($this->data['customer']['phone']) ."',
					customer_email = '". database::input($this->data['customer']['email']) ."',
					shipping_company = '". database::input($this->data['customer']['shipping_address']['company']) ."',
					shipping_firstname = '". database::input($this->data['customer']['shipping_address']['firstname']) ."',
					shipping_lastname = '". database::input($this->data['customer']['shipping_address']['lastname']) ."',
					shipping_address1 = '". database::input($this->data['customer']['shipping_address']['address1']) ."',
					shipping_address2 = '". database::input($this->data['customer']['shipping_address']['address2']) ."',
					shipping_city = '". database::input($this->data['customer']['shipping_address']['city']) ."',
					shipping_postcode = '". database::input($this->data['customer']['shipping_address']['postcode']) ."',
					shipping_country_code = '". database::input($this->data['customer']['shipping_address']['country_code']) ."',
					shipping_zone_code = '". database::input($this->data['customer']['shipping_address']['zone_code']) ."',
					shipping_phone = '". database::input($this->data['customer']['shipping_address']['phone']) ."',
					shipping_email = '". database::input($this->data['customer']['shipping_address']['email']) ."',
					shipping_option_id = '". (!empty($this->shipping->selected['id']) ? database::input($this->data['shipping_option']['id']) : '') ."',
					shipping_option_name = '". (!empty($this->shipping->selected['id']) ? database::input($this->shipping->selected['name']) : '') ."',
					shipping_option_userdata = '". (!empty($this->shipping->selected['userdata']) ? database::input(json_encode($this->data['shipping_option']['userdata'], JSON_UNESCAPED_SLASHES)) : '') ."',
					shipping_purchase_cost = ". (float)$this->data['shipping_purchase_cost'] .",
					shipping_tracking_id = '". database::input($this->data['shipping_tracking_id']) ."',
					shipping_tracking_url = '". database::input($this->data['shipping_tracking_url']) ."',
					payment_option_id = '". (!empty($this->data['payment_option']['id']) ? database::input($this->data['payment_option']['id']) : '') ."',
					payment_option_name = '". (!empty($this->data['payment_option']['name']) ? database::input($this->data['payment_option']['name']) : '') ."',
					payment_option_userdata = '". (!empty($this->data['payment_option']['userdata']) ? database::input(json_encode($this->data['payment_option']['userdata'], JSON_UNESCAPED_SLASHES)) : '') ."',
					payment_transaction_id = '". database::input($this->data['payment_transaction_id']) ."',
					payment_transaction_fee = ". (float)$this->data['payment_transaction_fee'] .",
					payment_receipt_url = '". database::input($this->data['payment_receipt_url']) ."',
					payment_terms = '". database::input($this->data['payment_terms']) ."',
					incoterm = '". database::input($this->data['incoterm']) ."',
					reference = '". database::input($this->data['reference']) ."',
					language_code = '". database::input($this->data['language_code']) ."',
					currency_code = '". database::input($this->data['currency_code']) ."',
					currency_value = ". (float)$this->data['currency_value'] .",
					weight_total = ". (float)$this->data['weight_total'] .",
					weight_unit = '". database::input($this->data['weight_unit']) ."',
					display_prices_including_tax = ". (int)$this->data['display_prices_including_tax'] .",
					subtotal = ". (float)$this->data['subtotal'] .",
					subtotal_tax = ". (float)$this->data['subtotal_tax'] .",
					discount = ". (float)$this->data['discount'] .",
					discount_tax = ". (float)$this->data['discount_tax'] .",
					total = ". (float)$this->data['total'] .",
					total_tax = ". (float)$this->data['total_tax'] .",
					notes = '". database::input($this->data['ip_address']) ."',
					ip_address = '". database::input($this->data['ip_address']) ."',
					hostname = '". database::input($this->data['hostname']) ."',
					user_agent = '". database::input($this->data['user_agent']) ."',
					domain = '". database::input($this->data['domain']) ."',
					public_key = '". database::input($this->data['public_key']) ."',
					date_paid = ". (!empty($this->data['date_paid']) ? "'". date('Y-m-d H:i:s', strtotime($this->data['date_paid'])) ."'" : "null") .",
					date_dispatched = ". (!empty($this->data['date_dispatched']) ? "'". date('Y-m-d H:i:s', strtotime($this->data['date_dispatched'])) ."'" : "null") .",
					updated_at = '". ($this->data['updated_at'] = date('Y-m-d H:i:s')) ."'
				where id = ". (int)$this->data['id'] ."
				limit 1;"
			);

			// Restock previous items
			if ($this->previous['order_status_id'] && reference::order_status($this->previous['order_status_id'])->stock_action == 'commit') {

				foreach ($this->previous['items'] as $previous_order_item) {
					if (empty($previous_order_item['product_id'])) continue;
					$this->adjust_stock_quantity($previous_order_item['product_id'], $previous_order_item['option_stock_combination'], (float)$previous_order_item['quantity']);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."products
					set quantity = quantity + ". (float)$previous_order_item['quantity'] ."
					where product_id = ". (int)$previous_order_item['product_id'] ."
					limit 1;"
				);
			}

			// Delete order items
			database::query(
				"delete from ". DB_TABLE_PREFIX ."orders_items
				where order_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['items'], 'id')) ."');"
			);

			// Insert/update order items
			$i = 0;
			foreach ($this->data['items'] as $key => $item) {

				if (empty($item['id'])) {

					database::query(
						"insert into ". DB_TABLE_PREFIX ."orders_items
						(order_id)
						values (". (int)$this->data['id'] .");"
					);

					$this->data['items'][$key]['id'] = $item['id'] = database::insert_id();

					// Update purchase count
					if (!empty($item['product_id'])) {
						database::query(
							"update ". DB_TABLE_PREFIX ."products
							set purchases = purchases + ". (float)$item['quantity'] ."
							where id = ". (int)$item['product_id'] ."
							limit 1;"
						);
					}
				}

				// Withdraw stock
				if ($this->data['order_status_id'] && !empty(reference::order_status($this->data['order_status_id'])->is_sale) && !empty($item['product_id'])) {
					$this->adjust_stock_quantity($item['product_id'], $item['option_stock_combination'], -(float)$item['quantity']);
				}

				database::query(
					"update ". DB_TABLE_PREFIX ."orders_items
					set product_id = ". (int)$item['product_id'] .",
						stock_item_id = ". (int)$item['stock_item_id'] .",
						name = '". database::input($item['name']) ."',
						userdata = '". (!empty($item['userdata']) ? database::input(json_encode($item['userdata'], JSON_UNESCAPED_SLASHES)) : '') ."',
						serial_number = '". database::input($item['serial_number']) ."',
						sku = '". database::input($item['sku']) ."',
						gtin = '". database::input($item['gtin']) ."',
						taric = '". database::input($item['taric']) ."',
						quantity = ". (float)$item['quantity'] .",
						price = ". (float)$item['price'] .",
						tax = ". (float)$item['tax'] .",
						tax_class_id = ". (int)$item['tax_class_id'] .",
						tax_rate = ". ($item['tax_rate'] ? (float)$item['tax_rate'] : "null") .",
						discount = ". (float)$item['discount'] .",
						discount_tax = ". (float)$item['discount_tax'] .",
						sum = ". (float)$item['sum'] .",
						sum_tax = ". (float)$item['sum_tax'] .",
						weight = ". (float)$item['weight'] .",
						weight_unit = '". database::input($item['weight_unit']) ."',
						length = ". (float)$item['length'] .",
						width = ". (float)$item['width'] .",
						height = ". (float)$item['height'] .",
						length_unit = '". database::input($item['length_unit']) ."',
						priority = ". ++$i ."
					where id = ". (int)$item['id'] ."
					and order_id = ". (int)$this->data['id'] ."
					limit 1;"
				);

				// Withdraw stock
				if ($this->data['order_status_id'] && reference::order_status($this->data['order_status_id'])->stock_action == 'commit') {

					if (!empty($item['stock_item_id'])) {
						database::query(
							"update ". DB_TABLE_PREFIX ."stock_items
							set quantity = quantity - ". (float)$item['quantity'] ."
							where id = ". (int)$item['stock_item_id'] ."
							and product_id = ". (int)$item['product_id'] ."
							limit 1;"
						);
					}

					database::query(
						"update ". DB_TABLE_PREFIX ."products
						set quantity = quantity - ". (float)$item['quantity'] ."
						where id = ". (int)$item['product_id'] ."
						limit 1;"
					);
				}
			};

			// Delete comments
			database::query(
				"delete from ". DB_TABLE_PREFIX ."orders_comments
				where order_id = ". (int)$this->data['id'] ."
				and id not in ('". implode("', '", array_column($this->data['comments'], 'id')) ."');"
			);

			// Insert/update comments
			if (!empty($this->data['comments'])) {

				$notify_comments = [];

				foreach ($this->data['comments'] as $key => $comment) {

					if (empty($comment['author'])) {
						$comment['author'] = 'system';
					}

					if (empty($comment['author_id'])) {
						$comment['author_id'] = ($comment['author'] == 'customer') ? -1 : 0;
					}

					if (empty($comment['id'])) {
						database::query(
							"insert into ". DB_TABLE_PREFIX ."orders_comments
							(order_id, created_at)
							values (". (int)$this->data['id'] .", '". ($this->data['comments'][$key]['created_at'] = date('Y-m-d H:i:s')) ."');"
						);

						$comment['id'] = $this->data['comments'][$key]['id'] = database::insert_id();

						if ($this->data['comments'][$key]['author'] == 'staff' && !empty($this->data['comments'][$key]['notify']) && empty($this->data['comments'][$key]['hidden'])) {
							$notify_comments[] = $this->data['comments'][$key];
						}
					}

					database::query(
						"update ". DB_TABLE_PREFIX ."orders_comments
						set author = '". (!empty($comment['author']) ? database::input($comment['author']) : 'system') ."',
							author_id = ". (int)$comment['author_id'] .",
							text = '". database::input($comment['text']) ."',
							hidden = '". (!empty($comment['hidden']) ? 1 : 0) ."'
						where id = ". (int)$comment['id'] ."
						and order_id = ". (int)$this->data['id'] ."
						limit 1;"
					);
				}

				if (!empty($notify_comments)) {

					$subject = '['. language::translate('title_order', 'Order') .' '. $this->data['no'] .'] ' . language::translate('title_new_comments_added', 'New Comments Added', $this->data['language_code']);

					$message = language::translate('text_new_comments_added_to_your_order', 'New comments added to your order', $this->data['language_code']) . ":\r\n\r\n";
					foreach ($notify_comments as $comment) {
						$message .= functions::datetime_format('datetime', $comment['created_at']) ." – ". trim($comment['text']) . "\r\n\r\n";
					}

					$email = new ent_email();
					$email->add_recipient($this->data['customer']['email'], $this->data['customer']['firstname'] .' '. $this->data['customer']['lastname'])
								->set_subject($subject)
								->add_body($message)
								->send();
				}
			}

			// Send order status email notification
			if ($this->previous['order_status_id'] && $this->data['order_status_id'] != $this->previous['order_status_id']) {
				if (!empty(reference::order_status($this->data['order_status_id'])->notify)) {
					$this->send_email_notification();
				}
			}

			list($module_id, $option_id) = preg_split('#:#', fallback($this->data['payment_option']['id'], ':'), 2);
			$payment_modules = new mod_payment();
			$payment_modules->run('after_save', $module_id, $this);

			$order_modules = new mod_order();
			$order_modules->update($this);

			$this->previous = $this->data;

			cache::clear_cache('order');
			cache::clear_cache('category');
			cache::clear_cache('brand');
			cache::clear_cache('products');
		}

		public function refresh_total() {

			$this->data['subtotal'] = 0;
			$this->data['subtotal_tax'] = 0;
			$this->data['discount'] = 0;
			$this->data['discount_tax'] = 0;
			$this->data['total'] = 0;
			$this->data['total_tax'] = 0;
			$this->data['weight_total'] = 0;

			foreach ($this->data['items'] as $item) {
				$this->data['subtotal'] += (float)$item['price'] * (float)$item['quantity'];
				$this->data['subtotal_tax'] += (float)$item['tax'] * (float)$item['quantity'];
				$this->data['discount'] += (float)$item['discount'] * (float)$item['quantity'];
				$this->data['discount_tax'] += (float)$item['discount_tax'] * (float)$item['quantity'];
				$this->data['total'] += ($item['price'] - (float)$item['discount']) * (float)$item['quantity'];
				$this->data['total_tax'] += ((float)$item['tax'] - (float)$item['discount_tax']) * (float)$item['quantity'];
				$this->data['weight_total'] += (float)weight::convert($item['weight'], $item['weight_unit'], $this->data['weight_unit']) * abs($item['quantity']);
			}
		}

		private function _generate_order_number() {

			$order_no = strtr(settings::get('order_no_format'), [
				'{yy}' => date('y'),
				'{yyyy}' => date('Y'),
				'{mm}' => date('m'),
				'{q}' => ceil(date('m')/3),
				'{id}' => $this->data['id'],
			]);

			// Append length digit
			if (strpos(settings::get('order_no_format'), '{l}') !== false) {
				$length = strlen(preg_replace('#[^\d]#', '', $order_no)) + preg_match('#\{c\}#', settings::get('order_no_format')) ? 1 : 0;
				$order_no = str_replace('{l}', $length, $order_no);
			}

			// Append checksum digit
			if (strpos(settings::get('order_no_format'), '{c}') !== false) {

				$digits = preg_replace('#[^\d]#', '', $order_no);

				$sum = 0;
				foreach (str_split(strrev($digits)) as $i => $digit) {
					$sum += ($i % 2 == 0) ? array_sum(str_split($digit * 2)) : $digit;
				}

				$order_no = str_replace('{c}', strval($stack), $order_no);
			}

			$this->data['no'] = preg_replace('#\{.*?\}#', '', $order_no);
		}

		public function add_item($item) {

			$structure = [];

			database::query(
				"show fields from ". DB_TABLE_PREFIX ."orders_items;"
			)->each(function($field) use (&$structure) {
				$structure[$field['Field']] = database::create_variable($field);
			});

			// Stripe some fields
			$item = array_diff_assoc($item, ['id', 'order_id']);

			// Merge with structure
			$item = array_replace($structure, array_intersect_key($item, $structure));

			$item['sum'] = ($item['price'] - $item['discount']) * $item['quantity'];
			$item['sum_tax'] = ($item['tax'] - $item['discount_tax']) * $item['quantity'];

			$this->data['items'][] = $item;

			$this->data['subtotal'] += $item['price'] * $item['quantity'];
			$this->data['subtotal_tax'] += $item['tax'] * $item['quantity'];
			$this->data['discount'] += $item['discount'] * $item['quantity'];
			$this->data['discount_tax'] += $item['discount_tax'] * $item['quantity'];
			$this->data['total'] += ($item['price'] + $item['tax']) * $item['quantity'];
			$this->data['total_tax'] += $item['tax'] * $item['quantity'];
			$this->data['weight_total'] += weight::convert($item['weight'], $item['weight_unit'], $this->data['weight_unit']) * $item['quantity'];
		}

		public function validate($filters=[], $shipping = null, $payment = null) {

			if (!is_array($filters)) {
				$filters = [];
			}

			// Items

			if (empty($filters) || in_array('customer', $filters)) {

				if (empty($this->data['items'])) {
					return language::translate('error_order_missing_items', 'The order does not contain any items');
				}

				foreach ($this->data['items'] as $item) {
					if (!empty($item['error'])) {
						return language::translate('error_cart_contains_errors', 'Your cart contains errors');
					}
				}

				if ($this->data['total'] < 0) {
					return language::translate('error_total_cannot_be_a_negative_amount', 'The total cannot be a negative amount');
				}
			}

			// Customer Details

			if (empty($filters) || in_array('customer', $filters)) {

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

					if (!functions::validate_email($this->data['customer']['email'])) {
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
						if (empty($this->data['customer']['zone_code']) && reference::country($this->data['customer']['country_code'])->zones) {
							throw new Exception(language::translate('error_must_select_zone', 'You must select a zone.'));
						}
					}

					if (empty($this->data['customer']['id'])) {

						if (!database::query(
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

					if (!empty($this->data['customer']['different_shipping_address'])) {

						if (empty($this->data['customer']['shipping_address']['firstname'])) {
							throw new Exception(language::translate('error_must_provide_firstname', 'You must provide a first name'));
						}

						if (empty($this->data['customer']['shipping_address']['lastname'])) {
							throw new Exception(language::translate('error_must_provide_lastname', 'You must provide a last name'));
						}

						if (empty($this->data['customer']['shipping_address']['address1'])) {
							throw new Exception(language::translate('error_must_provide_address1', 'You must provide an address'));
						}

						if (empty($this->data['customer']['shipping_address']['city'])) {
							throw new Exception(language::translate('error_must_provide_city', 'You must provide a city'));
						}

						if (empty($this->data['customer']['shipping_address']['country_code'])) {
							throw new Exception(language::translate('error_must_select_country', 'You must select a country'));
						}

						if (reference::country($this->data['customer']['shipping_address']['country_code'])->postcode_format) {
							if (!empty($this->data['customer']['shipping_address']['postcode'])) {
								if (!preg_match('#'. reference::country($this->data['customer']['shipping_address']['country_code'])->postcode_format .'#i', $this->data['customer']['shipping_address']['postcode'])) {
									throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format.'));
								}
							} else {
								throw new Exception(language::translate('error_must_provide_postcode', 'You must provide a postcode'));
							}
						}

						if (settings::get('customer_field_zone') && reference::country($this->data['customer']['shipping_address']['country_code'])->zones) {
							if (empty($this->data['customer']['shipping_address']['zone_code']) && reference::country($this->data['customer']['shipping_address']['country_code'])->zones) {
								return language::translate('error_must_select_zone', 'You must select a zone');
							}
						}
					}

				} catch (Exception $e) {
					return language::translate('title_shipping_address', 'Shipping Address') .': '. $e->getMessage();
				}

				// Additional Customer Validation

				$mod_customer = new mod_customer();
				$result = $mod_customer->validate($this->data['customer']);

				if (!empty($result['error'])) {
					return $result['error'];
				}
		}
			// Shipping Option Validation
			if (empty($filters) || in_array('customer', $filters)) {

				if (!empty($shipping->modules) && count($shipping->options($this->data['items'], $this->data['currency_code'], $this->data['customer']))) {

					if (!empty($this->data['shipping_option']['id'])) {

						list($module_id, $option_id) = $this->data['shipping_option']['id'] ? preg_split('#:#', $this->data['shipping_option']['id'], 2) : ['', ''];

						if (empty($shipping->data['options'][$module_id]['options'][$option_id])) {
							return language::translate('error_invalid_shipping_method_selected', 'Invalid shipping method selected');
						}

						if (!empty($shipping->data['options'][$module_id]['options'][$option_id]['error'])) {
							return language::translate('error_shipping_method_contains_error', 'The selected shipping method contains errors');
						}

						if ($error = $shipping->run('validate', $module_id, $this)) {
							return $error;
						}

					} else {
						return language::translate('error_no_shipping_method_selected', 'No shipping method selected');
					}
				}
			}

			// Payment Option Validation
			if (empty($filters) || in_array('customer', $filters)) {

				if (!empty($payment->modules) && count($payment->options($this->data['items'], $this->data['currency_code'], $this->data['customer']))) {

					if (!empty($this->data['payment_option']['id'])) {

						list($module_id, $option_id) = $this->data['payment_option']['id'] ? preg_split('#:#', $this->data['payment_option']['id'], 2) : ['', ''];

						if (empty($payment->options[$module_id]['options'][$option_id])) {
							return language::translate('error_invalid_payment_method_selected', 'Invalid payment method selected');
						}

						if (!empty($payment->options[$module_id]['options'][$option_id]['error'])) {
							return language::translate('error_payment_method_contains_error', 'The selected payment method contains errors');
						}

						if ($error = $payment->run('validate', $module_id, $this)) {
							return $error;
						}

					} else {
						return language::translate('error_no_payment_method_selected', 'No payment method selected');
					}
				}
			}

			// Additional Order Validation
			if (empty($filters) || in_array('customer', $filters)) {

				$mod_order = new mod_order();
				$result = $mod_order->validate($this);

				if (!empty($result['error'])) {
					return $result['error'];
				}
			}

			return false;
		}

		public function send_order_copy($recipient, $ccs=[], $bccs=[], $language_code='') {

			if (!$recipient) return;

			if (!$language_code) {
				$language_code = $this->data['language_code'];
			}

			$order_status = $this->data['order_status_id'] ? reference::order_status($this->data['order_status_id'], $language_code) : '';

			$aliases = [
				'%order_id' => $this->data['no'], // Backwards compatibility
				'%order_no' => $this->data['no'],
				'%firstname' => $this->data['customer']['firstname'],
				'%lastname' => $this->data['customer']['lastname'],
				'%billing_address' => functions::format_address($this->data['customer']),
				'%payment_transaction_id' => $this->data['payment_transaction_id'] ?: '-',
				'%shipping_address' => functions::format_address($this->data['customer']['shipping_address']),
				'%shipping_tracking_id' => $this->data['shipping_tracking_id'] ?: '-',
				'%shipping_tracking_url' => $this->data['shipping_tracking_url'] ?: '',
				'%order_items' => null,
				'%total' => currency::format($this->data['total'], true, $this->data['currency_code'], $this->data['currency_value']),
				'%order_copy_url' => document::ilink('order', ['order_no' => $this->data['no'], 'public_key' => $this->data['public_key']], false, [], $language_code),
				'%order_status' => $order_status ? $order_status->name : null,
				'%store_name' => settings::get('store_name'),
				'%store_url' => document::ilink('', [], false, [], $language_code),
			];

			foreach ($this->data['items'] as $item) {

				if (!empty($item['product_id'])) {
					$product = reference::product($item['product_id'], $language_code);

					$userdata = [];
					if (!empty($item['userdata'])) {
						foreach ($item['userdata'] as $k => $v) {
							$userdata[] = $k .': '. $v;
						}
					}

					$aliases['%order_items'] .= (float)$item['quantity'] .' x '. $product->name . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "\r\n";

				} else {
					$aliases['%order_items'] .= (float)$item['quantity'] .' x '. $item['name'] . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "\r\n";
				}
			}

			$aliases['%order_items'] = trim($aliases['%order_items']);

			$subject = '['. language::translate('title_order', 'Order', $language_code) .' '. $this->data['no'] .'] '. language::translate('title_order_confirmation', 'Order Confirmation', $language_code);

			$message = implode("\r\n", [
				'Thank you for your purchase!',
				'',
				'Your order #%order_no has successfully been created with a total of %total for the following ordered items:',
				'',
				'. %order_items',
				'',
				'A printable order copy is available here:',
				'%order_copy_url',
				'',
				'Regards,',
				'%store_name',
				'%store_url',
			]);

			$message = strtr(language::translate('email_order_confirmation', $message, $language_code), $aliases);

			if (!empty(language::$languages[$this->data['language_code']]) && language::$languages[$this->data['language_code']]['direction'] == 'rtl') {
				$message = "\xe2\x80\x8f" . $message;
			}

			$email = new ent_email();

			if (!empty($ccs)) {
				foreach ($ccs as $cc) {
					$email->add_cc($cc);
				}
			}

			if (!empty($bccs)) {
				foreach ($bccs as $bcc) {
					$email->add_bcc($bcc);
				}
			}

			$email->add_recipient($recipient)
						->set_subject($subject)
						->add_body($message)
						->send();
		}

		public function send_email_notification() {

			if (!$this->data['order_status_id']) return;

			$order_status = reference::order_status($this->data['order_status_id'], $this->data['language_code']);

			$aliases = [
				'%order_id' => $this->data['no'], // Backwards compatibility
				'%order_no' => $this->data['no'],
				'%new_status' => $order_status->name,
				'%firstname' => $this->data['customer']['firstname'],
				'%lastname' => $this->data['customer']['lastname'],
				'%billing_address' => nl2br(functions::format_address($this->data['customer']), false),
				'%payment_transaction_id' => $this->data['payment_transaction_id'] ?: '-',
				'%shipping_address' => nl2br(functions::format_address($this->data['customer']['shipping_address']), false),
				'%shipping_tracking_id' => $this->data['shipping_tracking_id'] ?: '-',
				'%shipping_tracking_url' => $this->data['shipping_tracking_url'],
				'%shipping_current_status' => $this->data['shipping_current_status'],
				'%shipping_current_location' => $this->data['shipping_current_location'],
				'%order_items' => null,
				'%total' => currency::format($this->data['total'], true, $this->data['currency_code'], $this->data['currency_value']),
				'%order_copy_url' => document::ilink('order', ['order_no' => $this->data['no'], 'public_key' => $this->data['public_key']], false, [], $this->data['language_code']),
				'%order_status' => $order_status->name,
				'%store_name' => settings::get('store_name'),
				'%store_url' => document::ilink('', [], false, [], $this->data['language_code']),
			];

			foreach ($this->data['items'] as $item) {

				if (!empty($item['product_id'])) {
					$product = reference::product($item['product_id'], $this->data['language_code']);

					$userdata = [];
					if (!empty($item['userdata'])) {
						foreach ($item['userdata'] as $k => $v) {
							$userdata[] = $k .': '. $v;
						}
					}

					$aliases['%order_items'] .= (float)$item['quantity'] .' x '. $product->name . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "<br>\r\n";

				} else {
					$aliases['%order_items'] .= (float)$item['quantity'] .' x '. $item['name'] . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "<br>\r\n";
				}
			}

			$subject = strtr($order_status->email_subject, $aliases);
			$message = strtr($order_status->email_message, $aliases);

			if (!$subject) {
				$subject = '['. language::translate('title_order', 'Order', $this->data['language_code']) .' #'. $this->data['no'] .'] '. $order_status->name;
			}

			if (!$message) {
				$message = strtr(language::translate('text_order_status_changed_to_new_status', 'Order status changed to %new_status', $this->data['language_code']), $aliases);
			}

			if (!empty(language::$languages[$this->data['language_code']]) && language::$languages[$this->data['language_code']]['direction'] == 'rtl') {
				$message = implode(PHP_EOL, [
					'<div dir="rtl">',
					$message,
					'</div>',
				]);
			}

			$email = new ent_email();
			$email->add_recipient($this->data['customer']['email'], $this->data['customer']['firstname'] .' '. $this->data['customer']['lastname'])
						->set_subject($subject)
						->add_body($message, true)
						->send();
		}

		public function adjust_stock_quantity($product_id, $combination, $quantity_adjustment) {
			if ($quantity_adjustment == 0) return;
			$product = new ent_product($product_id);
			$product->adjust_quantity((float)$quantity_adjustment, $combination);
		}

		public function delete() {

			if (!$this->data['id']) return;

			$order_modules = new mod_order();
			$order_modules->delete($this->previous);

			database::query(
				"delete o, oi, oc
				from ". DB_TABLE_PREFIX ."orders o
				left join ". DB_TABLE_PREFIX ."orders_items oi on (oi.order_id = o.id)
				left join ". DB_TABLE_PREFIX ."orders_comments oc on (oc.order_id = o.id)
				where o.id = ". (int)$this->data['id'] .";"
			);

			$this->reset();

			cache::clear_cache('order');
			cache::clear_cache('category');
			cache::clear_cache('brand');
			cache::clear_cache('products');
		}
	}

<?php

	class ent_order {

		public $data;
		public $previous;
		public $shipping;
		public $payment;

		public function __construct(int|null $id = null) {

			if ($id) {
				$this->load($id);
			} else {
				$this->reset();
			}
		}

		public function reset(): void {

			$this->data = [];

			foreach (database::schema(DB_PREFIX .'orders') as $field) {
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
			}

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
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
				'hostname' => isset($_SERVER['REMOTE_ADDR']) ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : '',
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
				'fingerprint' => session::$data['fingerprint'] ?? null,
				'domain' => $_SERVER['HTTP_HOST'] ?? null,
			]);

			$this->data['customer']['country_code'] = settings::get('default_country_code');
			$this->data['customer']['shipping_address']['country_code'] = settings::get('default_country_code');

			$this->data['shipping_option']['userdata'] = [];
			$this->data['payment_option']['userdata'] = [];

			$this->data['payment_due'] = &$this->data['total']; // Backwards compatibility <3.0.0
			$this->data['tax_total'] = &$this->data['total_tax']; // Backwards compatibility <3.0.0
			$this->data['items'] = [];

			$this->previous = $this->data;
		}

		public function load(int $id): void {

			if (!preg_match('#^\d+$#', $id)) {
				throw new Exception('Invalid order (ID: '. $id .')');
			}

			$this->reset();

			$order = database::query(
				"select * from ". DB_PREFIX ."orders
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

			$this->data['conversions'] = $this->data['conversions'] ? json_decode($this->data['conversions'], true) : [];

			$this->data['items'] = database::query(
				"select *	from ". DB_PREFIX ."orders_items
				where order_id = ". (int)$id ."
				order by priority;"
			)->fetch_all(function(&$item) {

				$item['userdata'] = $item['userdata'] ? json_decode($item['userdata'], true) : '';

				$item['stock_items'] = database::query(
					"select osi.*,
						coalesce(si.quantity, 0) as stock_quantity,
						coalesce(o.quantity_reserved, 0) as quantity_reserved,
						(coalesce(si.quantity, 0) - coalesce(o.quantity_reserved, 0)) as quantity_available
					from ". DB_PREFIX ."orders_stock_items osi
					left join ". DB_PREFIX ."stock_items si on (si.id = osi.stock_item_id)
					left join (
						select osi.stock_item_id, sum(osi.quantity * oi.quantity) as quantity_reserved
						from ". DB_PREFIX ."orders_stock_items osi
						left join ". DB_PREFIX ."orders_items oi on (oi.id = osi.item_id and oi.order_id = osi.order_id)
						where osi.order_id in (
							select id from ". DB_PREFIX ."orders
							where order_status_id in (
								select id from ". DB_PREFIX ."order_statuses
								where stock_action = 'reserve'
							)
						)
						group by stock_item_id
					) o on (o.stock_item_id = si.id)
					where osi.item_id = ". (int)$item['id'] ."
					and osi.order_id = ". (int)$item['order_id'] ."
					order by priority;"
				)->fetch_all(function(&$item) {
					$item['sufficient_stock'] = $item['quantity_available'] >= 0;
				});
			});

			$this->data['comments'] = database::query(
				"select oc.*, a.username as author_username from ". DB_PREFIX ."orders_comments oc
				left join ". DB_PREFIX ."administrators a on (a.id = oc.author_id)
				where oc.order_id = ". (int)$id ."
				order by oc.id;"
			)->fetch_all();

			$this->data['payment_due'] = &$this->data['total']; // Backwards compatibility <3.0.0
			$this->data['tax_total'] = &$this->data['total_tax']; // Backwards compatibility <3.0.0

			$this->data['shipping_option']['userdata'] = @json_decode($this->data['shipping_option']['userdata'], true);
			$this->data['payment_option']['userdata'] = @json_decode($this->data['payment_option']['userdata'], true);

			$this->previous = $this->data;
		}

		public function save(): void {

			database::begin_transaction();

			try {

			// Re-calculate total if there are changes
			$this->refresh_total();

			// Log order status change as comment
			if ($this->previous['id'] && $this->data['order_status_id'] != $this->previous['order_status_id']) {
				$this->data['comments'][] = [
					'author' => 'system',
					'text' => strtr(t('text_user_changed_order_status_to_new_status', 'Order status changed to {new_status}', settings::get('store_language_code')), [
						'{username}' => administrator::$data['username'] ?? 'system',
						'{new_status}' => reference::order_status($this->data['order_status_id'], settings::get('store_language_code'))->name,
					]),
					'hidden' => 1,
				];
			}

			// Link guests to customer profile
			if (!$this->data['customer']['id'] && $this->data['customer']['email']) {

				$customer = database::query(
					"select id from ". DB_PREFIX ."customers
					where email = '". database::input($this->data['customer']['email']) ."'
					limit 1;"
				)->fetch();

				if ($customer) {
					$this->data['customer']['id'] = $customer['id'];
				}
			}

			if (!$this->data['public_key']) {
				$this->data['public_key'] = bin2hex(random_bytes(16));
			}

			if (!$this->data['dispatched_at']) {
				if ($this->data['order_status_id'] && in_array(reference::order_status($this->data['order_status_id'])->state, ['dispatched', 'delivered'])) {
					if (!$this->previous['order_status_id'] || !in_array(reference::order_status($this->previous['order_status_id'])->state, ['dispatched', 'delivered'])) {
						$this->data['dispatched_at'] = date('Y-m-d H:i:s');
					}
				}
			}

			// Insert order
			if (!$this->data['id']) {

				database::query(
					"insert into ". DB_PREFIX ."orders
					(public_key, created_at)
					values ('". database::input($this->data['public_key']) ."', '". ($this->data['created_at'] = date('Y-m-d H:i:s')) ."');"
				);

				$this->data['id'] = database::insert_id();
			}

			// Create custom order number
			if (!$this->data['no']) {
				$this->data['no'] = $this->_generate_order_number();
			}

			// Update order
			database::query(
				"update ". DB_PREFIX ."orders
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
					shipping_option_id = '". (!empty($this->data['shipping_option']['id']) ? database::input($this->data['shipping_option']['id']) : '') ."',
					shipping_option_name = '". (!empty($this->data['shipping_option']['name']) ? database::input($this->data['shipping_option']['name']) : '') ."',
					shipping_option_fee = ". (float)(!empty($this->data['shipping_option']['fee']) ? $this->data['shipping_option']['fee'] : 0) .",
					shipping_option_userdata = '". (!empty($this->data['shipping_option']['userdata']) ? database::input(f::format_json($this->data['shipping_option']['userdata'])) : '{}') ."',
					shipping_purchase_cost = ". (float)$this->data['shipping_purchase_cost'] .",
					shipping_tracking_id = '". database::input($this->data['shipping_tracking_id']) ."',
					shipping_tracking_url = '". database::input($this->data['shipping_tracking_url']) ."',
					payment_option_id = '". (!empty($this->data['payment_option']['id']) ? database::input($this->data['payment_option']['id']) : '') ."',
					payment_option_name = '". (!empty($this->data['payment_option']['name']) ? database::input($this->data['payment_option']['name']) : '') ."',
					payment_option_fee = ". (float)(!empty($this->data['payment_option']['fee']) ? $this->data['payment_option']['fee'] : 0) .",
					payment_option_userdata = '". (!empty($this->data['payment_option']['userdata']) ? database::input(f::format_json($this->data['payment_option']['userdata'])) : '{}') ."',
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
					discount = ". (float)$this->data['discount'] .",
					total = ". (float)$this->data['total'] .",
					total_tax = ". (float)$this->data['total_tax'] .",
					notes = '". database::input($this->data['notes']) ."',
					conversions = '". database::input(f::format_json($this->data['conversions'])) ."',
					ip_address = '". database::input($this->data['ip_address']) ."',
					hostname = '". database::input($this->data['hostname']) ."',
					user_agent = '". database::input($this->data['user_agent']) ."',
					fingerprint = '". database::input($this->data['fingerprint']) ."',
					domain = '". database::input($this->data['domain']) ."',
					public_key = '". database::input($this->data['public_key']) ."',
					paid_at = ". (!empty($this->data['paid_at']) ? "'". date('Y-m-d H:i:s', strtotime($this->data['paid_at'])) ."'" : "null") .",
					dispatched_at = ". (!empty($this->data['dispatched_at']) ? "'". date('Y-m-d H:i:s', strtotime($this->data['dispatched_at'])) ."'" : "null") .",
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
			}

			// Delete order items not in current data
			$item_ids = array_map('intval', array_filter(array_column($this->data['items'], 'id')));
			database::query(
				"delete from ". DB_PREFIX ."orders_items
				where order_id = ". (int)$this->data['id'] ."
				". ($item_ids ? "and id not in (". implode(", ", $item_ids) .")" : "") .";"
			);

			// Insert/update order items
			$i = 0;
			foreach ($this->data['items'] as $key => $item) {

				if (isset($item['order_id']) && $item['order_id'] != $this->data['id']) {
					$item['id'] = null;
				}

				if (empty($item['id'])) {

					database::query(
						"insert into ". DB_PREFIX ."orders_items
						(order_id)
						values (". (int)$this->data['id'] .");"
					);

					$this->data['items'][$key]['id'] = $item['id'] = database::insert_id();
				}

				database::query(
					"update ". DB_PREFIX ."orders_items
					set product_id = ". (int)$item['product_id'] .",
						stock_option_id = ". (!empty($item['stock_option_id']) ? (int)$item['stock_option_id'] : "null") .",
						code = '". database::input($item['code']) ."',
						name = '". database::input($item['name']) ."',
						image = '". database::input($item['image']) ."',
						userdata = '". (!empty($item['userdata']) ? database::input(f::format_json($item['userdata'])) : '{}') ."',
						quantity = ". (float)$item['quantity'] .",
						regular_price = ". (float)$item['regular_price'] .",
						final_price = ". (float)$item['final_price'] .",
						discount = ". (float)$item['discount'] .",
						tax_class_id = ". ($item['tax_class_id'] ? (int)$item['tax_class_id'] : "null") .",
						tax_rate = ". ($item['tax_rate'] ? (float)$item['tax_rate'] : "null") .",
						sum = ". (float)$item['sum'] .",
						sum_tax = ". (float)$item['sum_tax'] .",
						priority = ". ($this->data['items'][$key]['priority'] = $item['priority'] = ++$i) ."
					where id = ". (int)$item['id'] ."
					and order_id = ". (int)$this->data['id'] ."
					limit 1;"
				);
			}

			// Insert/update order stock items
			$i = 0;
			foreach ($this->data['items'] as $item_key => $item) {
				foreach (($item['stock_items'] ?? []) as $stock_item_key => $stock_item) {

					if (isset($item['order_id']) && $item['order_id'] != $this->data['id']) {
						$item['id'] = null;
					}

					if (empty($stock_item['id'])) {

						database::query(
							"insert into ". DB_PREFIX ."orders_stock_items
							(order_id, item_id)
							values (". (int)$this->data['id'] .", ". (int)$item['id'] .");"
						);

						$this->data['items'][$item_key]['stock_items'][$stock_item_key]['id'] = $stock_item['id'] = database::insert_id();
					}

					// Withdraw stock
					if ($this->data['order_status_id'] && !empty(reference::order_status($this->data['order_status_id'])->is_sale) && !empty($stock_item['stock_stock_item_id'])) {
						database::query(
							"update ". DB_PREFIX ."stock_stock_items
							set quantity = quantity + ". ($item['quantity'] * (float)$stock_item['quantity']) ."
							where id = ". (int)$stock_item['stock_stock_item_id'] ."
							limit 1;"
						);
					}

					database::query(
						"update ". DB_PREFIX ."orders_stock_items
						set item_id = ". (int)$item['id'] .",
							stock_stock_item_id = ". (int)$stock_item['stock_stock_item_id'] .",
							name = '". database::input($stock_item['name']) ."',
							serial_number = '". database::input($stock_item['serial_number']) ."',
							sku = '". database::input($stock_item['sku']) ."',
							gtin = '". database::input($stock_item['gtin']) ."',
							taric = '". database::input($stock_item['taric']) ."',
							quantity = ". (float)$stock_item['quantity'] .",
							weight = ". (float)$stock_item['weight'] .",
							weight_unit = '". database::input($stock_item['weight_unit']) ."',
							length = ". (float)$stock_item['length'] .",
							width = ". (float)$stock_item['width'] .",
							height = ". (float)$stock_item['height'] .",
							length_unit = '". database::input($stock_item['length_unit']) ."',
							priority = ". ($this->data['items'][$item_key]['stock_items'][$stock_item_key]['priority'] = $stock_item['priority'] = ++$i) ."
						where id = ". (int)$stock_item['id'] ."
						and item_id = ". (int)$item['id'] ."
						and order_id = ". (int)$this->data['id'] ."
						limit 1;"
					);

					// Withdraw stock
					if ($this->data['order_status_id'] && reference::order_status($this->data['order_status_id'])->stock_action == 'commit') {
						database::query(
							"update ". DB_PREFIX ."stock_stock_items
							set quantity = quantity - ". ($item['quantity'] * (float)$stock_item['quantity']) ."
							where id = ". (int)$stock_item['stock_stock_item_id'] ."
							limit 1;"
						);
					}
				}
			};

			// Delete comments not in current data
			$comment_ids = array_filter(array_column($this->data['comments'], 'id'));
			database::query(
				"delete from ". DB_PREFIX ."orders_comments
				where order_id = ". (int)$this->data['id'] ."
				". ($comment_ids ? "and id not in (". implode(",", array_map('intval', $comment_ids)) .")" : "") .";"
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

					if (isset($item['order_id']) && $item['order_id'] != $this->data['id']) {
						$comment['id'] = null;
					}

					if (empty($comment['id'])) {

						database::query(
							"insert into ". DB_PREFIX ."orders_comments
							(order_id, created_at)
							values (". (int)$this->data['id'] .", '". ($this->data['comments'][$key]['created_at'] = date('Y-m-d H:i:s')) ."');"
						);

						$comment['id'] = $this->data['comments'][$key]['id'] = database::insert_id();

						if ($this->data['comments'][$key]['author'] == 'staff' && !empty($this->data['comments'][$key]['notify']) && empty($this->data['comments'][$key]['hidden'])) {
							$notify_comments[] = $this->data['comments'][$key];
						}
					}

					database::query(
						"update ". DB_PREFIX ."orders_comments
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

					$subject = '['. t('title_order', 'Order') .' '. $this->data['no'] .'] ' . t('title_new_comments_added', 'New Comments Added', $this->data['language_code']);

					$message = t('text_new_comments_added_to_your_order', 'New comments added to your order', $this->data['language_code']) . ":\r\n\r\n";
					foreach ($notify_comments as $comment) {
						$message .= f::datetime_format('datetime', $comment['created_at']) ." – ". trim($comment['text']) . "\r\n\r\n";
					}

					(new ent_email())
						->add_recipient($this->data['customer']['email'], $this->data['customer']['firstname'] .' '. $this->data['customer']['lastname'])
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

			[$module_id, $option_id] = preg_split('#:#', ($this->data['payment_option']['id'] ?? '') ?: ':', 2);
			$payment_modules = new mod_payment($this, $this->data['payment_option']);
			$payment_modules->run('after_save', $module_id, $this);

			$order_modules = new mod_order();
			$order_modules->update($this);

			database::commit();

			if (!$this->previous['id']) {
				functions::webhook_send('order:created', $this->data);
			} else {
				functions::webhook_send('order:updated', $this->data);
			}

			$this->previous = $this->data;

			cache::clear_cache('order');
			cache::clear_cache('category');
			cache::clear_cache('brand');
			cache::clear_cache('products');

			} catch (Throwable $e) {
				database::rollback();
				throw $e;
			}
		}

		public function refresh_total(): void {

			$this->data['subtotal'] = 0;
			$this->data['subtotal_tax'] = 0;
			$this->data['discount'] = 0;
			$this->data['total'] = 0;
			$this->data['total_tax'] = 0;
			$this->data['weight_total'] = 0;

			foreach ($this->data['items'] as $item) {
				$this->data['subtotal'] += (float)$item['sum'];
				$this->data['subtotal_tax'] += (float)$item['sum_tax'];
				$this->data['discount'] += (float)$item['discount'] * (float)$item['quantity'];
				$this->data['total'] += $item['final_price'] * (float)$item['quantity'];
				$this->data['total_tax'] += tax::get_tax($item['final_price'] * (float)$item['quantity'], $item['tax_class_id'], $this->data['customer']);
				foreach ($item['stock_items'] as $stock_item) {
					$this->data['weight_total'] += f::convert_weight($stock_item['weight'] * $stock_item['quantity'], $item['weight_unit'], $this->data['weight_unit']) * $item['quantity'];
				}
			}

			// Add shipping fee
			if (!empty($this->data['shipping_option']['fee'])) {
				$this->data['total'] += (float)$this->data['shipping_option']['fee'];
			}

			// Add payment fee
			if (!empty($this->data['payment_option']['fee'])) {
				$this->data['total'] += (float)$this->data['payment_option']['fee'];
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
			if (str_contains(settings::get('order_no_format'), '{l}')) {
				$length = strlen(preg_replace('#[^\d]#', '', $order_no)) + (preg_match('#\{c\}#', settings::get('order_no_format')) ? 1 : 0);
				$order_no = str_replace('{l}', $length, $order_no);
			}

			// Append checksum digit
			if (str_contains(settings::get('order_no_format'), '{c}')) {

				$digits = preg_replace('#[^\d]#', '', $order_no);

				$sum = 0;
				foreach (str_split(strrev($digits)) as $i => $digit) {
					$sum += ($i % 2 == 0) ? array_sum(str_split($digit * 2)) : $digit;
				}

				$checkdigit = (10 - ($sum % 10)) % 10;
				$order_no = str_replace('{c}', $checkdigit, $order_no);
			}

			$this->data['no'] = preg_replace('#\{.*?\}#', '', $order_no);

			return $this->data['no'];
		}

		public function add_item(array $cart_item): void {

			$tax_rates = tax::get_rates($cart_item['tax_class_id'] ?? null, $this->data['customer']);
			$average_rate = !empty($tax_rates) ? array_sum($tax_rates) / count($tax_rates) : 0;
			$regular_price = (float)($cart_item['regular_price']['value'] ?? $cart_item['price'] ?? 0);
			$final_price = (float)($cart_item['final_price']['value'] ?? $cart_item['price'] ?? 0);

			$item = [
				'id' => null,
				'order_id' => null,
				'product_id' => (int)($cart_item['product_id'] ?? 0),
				'stock_option_id' => !empty($cart_item['stock_option_id']) ? (int)$cart_item['stock_option_id'] : null,
				'code' => $cart_item['code'] ?? '',
				'name' => $cart_item['name'] ?? '',
				'image' => $cart_item['image'] ?? '',
				'userdata' => $cart_item['userdata'] ?? '',
				'quantity' => $cart_item['quantity'] ?? 1,
				'regular_price' => $regular_price,
				'final_price' => $final_price,
				'discount' => $cart_item['discount']['value'] ?? $cart_item['discount'] ?? 0,
				'tax_rate' => $cart_item['tax_rate'] ?? $average_rate,
				'tax_class_id' => $cart_item['tax_class_id'] ?? null,
				'sum' => 0,
				'sum_tax' => 0,
				'stock_items' => [],
			];

			$item['sum'] = ($item['final_price'] || $item['discount']) ? ($item['final_price'] - $item['discount']) * $item['quantity'] : 0;
			if (isset($cart_item['tax'])) {
				$item['sum_tax'] = (float)$cart_item['tax'] * (float)$item['quantity'];
			} else {
				$item['sum_tax'] = tax::get_tax($item['sum'], $item['tax_class_id'] ?? null, $this->data['customer']);
			}

			foreach (($cart_item['stock_items'] ?? []) as $stock_item) {

				$item['stock_items'][] = [
					'stock_stock_item_id' => (int)($stock_item['stock_stock_item_id'] ?? $stock_item['stock_item_id'] ?? $stock_item['id'] ?? 0),
					'stock_item_id' => (int)($stock_item['stock_item_id'] ?? $stock_item['id'] ?? 0),
					'quantity' => $stock_item['quantity'],
					'name' => $stock_item['name'],
					'serial_number' => $stock_item['serial_number'],
					'sku' => $stock_item['sku'],
					'gtin' => $stock_item['gtin'],
					'taric' => $stock_item['taric'],
					'length' => $stock_item['length'],
					'width' => $stock_item['width'],
					'height' => $stock_item['height'],
					'length_unit' => $stock_item['length_unit'],
					'weight' => $stock_item['weight'],
					'weight_unit' => $stock_item['weight_unit'],
				];

				$this->data['weight_total'] += f::convert_weight($stock_item['weight'], $stock_item['weight_unit'], $this->data['weight_unit']) * $item['quantity'];
			}

			$this->data['items'][] = $item;

			$this->data['subtotal'] += $item['sum'];
			$this->data['subtotal_tax'] += $item['sum_tax'];
			$this->data['discount'] += $item['discount'] * $item['quantity'];
			$this->data['total'] += ($item['final_price'] - $item['discount']) * $item['quantity'] + $item['sum_tax'];
			$this->data['total_tax'] += $item['sum_tax'];
		}

		public function validate(string|array $filters = [], mixed $shipping = null, mixed $payment = null): string|false {

			if (is_string($filters)) {
				$filters = [$filters];
			}

			// Items

			if (!$filters || in_array('customer', $filters)) {

				if (empty($this->data['items'])) {
					return t('error_order_missing_items', 'The order does not contain any items');
				}

				foreach ($this->data['items'] as $item) {
					if (!empty($item['error'])) {
						return t('error_cart_contains_errors', 'Your cart contains errors');
					}
				}

				if ($this->data['total'] < 0) {
					return t('error_total_cannot_be_a_negative_amount', 'The total cannot be a negative amount');
				}
			}

			// Customer Details

			if (!$filters || in_array('customer', $filters)) {

				try {

					if (empty($this->data['customer']['firstname'])) {
						throw new Exception(t('error_must_provide_firstname', 'You must provide a first name'));
					}

					if (empty($this->data['customer']['lastname'])) {
						throw new Exception(t('error_must_provide_lastname', 'You must provide a last name'));
					}

					if (empty($this->data['customer']['address1'])) {
						throw new Exception(t('error_must_provide_address1', 'You must provide an address'));
					}

					if (empty($this->data['customer']['city'])) {
						throw new Exception(t('error_must_provide_city', 'You must provide a city'));
					}

					if (empty($this->data['customer']['country_code'])) {
						throw new Exception(t('error_must_select_country', 'You must select a country'));
					}

					if (empty($this->data['customer']['email'])) {
						throw new Exception(t('error_must_provide_email', 'You must provide an email address'));
					}

					if (empty($this->data['customer']['phone'])) {
						throw new Exception(t('error_must_provide_phone', 'You must provide a phone number'));
					}

					if (!f::validate_email($this->data['customer']['email'])) {
						throw new Exception(t('error_invalid_email_address', 'Invalid email address'));
					}

					if (reference::country($this->data['customer']['country_code'])->tax_id_format) {
						if (!empty($this->data['customer']['tax_id'])) {
							if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->tax_id_format .'#i', $this->data['customer']['tax_id'])) {
								throw new Exception(t('error_invalid_tax_id_format', 'Invalid tax ID format'));
							}
						}
					}

					if (reference::country($this->data['customer']['country_code'])->postcode_format) {
						if (!empty($this->data['customer']['postcode'])) {
							if (!preg_match('#'. reference::country($this->data['customer']['country_code'])->postcode_format .'#i', $this->data['customer']['postcode'])) {
								throw new Exception(t('error_invalid_postcode_format', 'Invalid postcode format'));
							}
						} else {
							throw new Exception(t('error_must_provide_postcode', 'You must provide a postcode'));
						}
					}

					if (settings::get('customer_field_zone') && reference::country($this->data['customer']['country_code'])->zones) {
						if (empty($this->data['customer']['zone_code']) && reference::country($this->data['customer']['country_code'])->zones) {
							throw new Exception(t('error_must_select_zone', 'You must select a zone.'));
						}
					}

					if (empty($this->data['customer']['id'])) {

						if (database::query(
							"select id from ". DB_PREFIX ."customers
							where email = '". database::input($this->data['customer']['email']) ."'
							and status = 0
							limit 1;"
						)->num_rows) {
							throw new Exception(t('error_customer_account_is_disabled', 'The customer account is disabled'));
						}
					}

				} catch (Exception $e) {
					return t('title_customer_details', 'Customer Details') .': '. $e->getMessage();
				}

				try {

					if (!empty($this->data['customer']['different_shipping_address'])) {

						if (empty($this->data['customer']['shipping_address']['firstname'])) {
							throw new Exception(t('error_must_provide_firstname', 'You must provide a first name'));
						}

						if (empty($this->data['customer']['shipping_address']['lastname'])) {
							throw new Exception(t('error_must_provide_lastname', 'You must provide a last name'));
						}

						if (empty($this->data['customer']['shipping_address']['address1'])) {
							throw new Exception(t('error_must_provide_address1', 'You must provide an address'));
						}

						if (empty($this->data['customer']['shipping_address']['city'])) {
							throw new Exception(t('error_must_provide_city', 'You must provide a city'));
						}

						if (empty($this->data['customer']['shipping_address']['country_code'])) {
							throw new Exception(t('error_must_select_country', 'You must select a country'));
						}

						if (reference::country($this->data['customer']['shipping_address']['country_code'])->postcode_format) {
							if (!empty($this->data['customer']['shipping_address']['postcode'])) {
								if (!preg_match('#'. reference::country($this->data['customer']['shipping_address']['country_code'])->postcode_format .'#i', $this->data['customer']['shipping_address']['postcode'])) {
									throw new Exception(t('error_invalid_postcode_format', 'Invalid postcode format.'));
								}
							} else {
								throw new Exception(t('error_must_provide_postcode', 'You must provide a postcode'));
							}
						}

						if (settings::get('customer_field_zone') && reference::country($this->data['customer']['shipping_address']['country_code'])->zones) {
							if (empty($this->data['customer']['shipping_address']['zone_code']) && reference::country($this->data['customer']['shipping_address']['country_code'])->zones) {
								return t('error_must_select_zone', 'You must select a zone');
							}
						}
					}

				} catch (Exception $e) {
					return t('title_shipping_address', 'Shipping Address') .': '. $e->getMessage();
				}

				// Additional Customer Validation
				if ($result = (new mod_customer)->validate($this->data['customer'])) {
					if (!empty($result['error'])) {
						return $result['error'];
					}
				}
			}

			// Shipping Option Validation
			if (!$filters || in_array('customer', $filters)) {

				if (!empty($shipping->modules) && count($shipping->options($this->data['items'], $this->data['currency_code'], $this->data['customer']))) {

					if (!empty($this->data['shipping_option']['id'])) {

						[$module_id, $option_id] = preg_split('#:#', ($this->data['shipping_option']['id'] ?? '') ?: ':', 2);

						if (empty($shipping->data['options'][$module_id]['options'][$option_id])) {
							return t('error_invalid_shipping_method_selected', 'Invalid shipping method selected');
						}

						if (!empty($shipping->data['options'][$module_id]['options'][$option_id]['error'])) {
							return t('error_shipping_method_contains_error', 'The selected shipping method contains errors');
						}

						if ($error = $shipping->run('validate', $module_id, $this)) {
							return $error;
						}

					} else {
						return t('error_no_shipping_method_selected', 'No shipping method selected');
					}
				}
			}

			// Payment Option Validation
			if (!$filters || in_array('customer', $filters)) {

				if (!empty($payment->modules) && count($payment->options($this->data['items'], $this->data['currency_code'], $this->data['customer']))) {

					if (!empty($this->data['payment_option']['id'])) {

						[$module_id, $option_id] = preg_split('#:#', ($this->data['payment_option']['id'] ?? '') ?: ':', 2);

						if (empty($payment->options[$module_id]['options'][$option_id])) {
							return t('error_invalid_payment_method_selected', 'Invalid payment method selected');
						}

						if (!empty($payment->options[$module_id]['options'][$option_id]['error'])) {
							return t('error_payment_method_contains_error', 'The selected payment method contains errors');
						}

						if ($error = $payment->run('validate', $module_id, $this)) {
							return $error;
						}

					} else {
						return t('error_no_payment_method_selected', 'No payment method selected');
					}
				}
			}

			// Additional Order Validation
			if (!$filters || in_array('customer', $filters)) {

				$mod_order = new mod_order();
				$result = $mod_order->validate($this);

				if (!empty($result['error'])) {
					return $result['error'];
				}
			}

			return false;
		}

		public function send_order_copy(string $recipient, array $ccs = [], array $bccs = [], string $language_code = ''): void {

			if (!$recipient) return;

			if (!$language_code) {
				$language_code = $this->data['language_code'];
			}

			$order_status = $this->data['order_status_id'] ? reference::order_status($this->data['order_status_id'], $language_code) : '';

			$aliases = [
				'{order_id}' => $this->data['no'], // Backwards compatibility
				'{order_no}' => $this->data['no'],
				'{firstname}' => $this->data['customer']['firstname'],
				'{lastname}' => $this->data['customer']['lastname'],
				'{billing_address}' => f::format_address($this->data['customer']),
				'{payment_transaction_id}' => $this->data['payment_transaction_id'] ?: '-',
				'{shipping_address}' => f::format_address($this->data['customer']['shipping_address']),
				'{shipping_tracking_id}' => $this->data['shipping_tracking_id'] ?: '-',
				'{shipping_tracking_url}' => $this->data['shipping_tracking_url'] ?: '',
				'{order_items}' => null,
				'{total}' => currency::format($this->data['total'], true, $this->data['currency_code'], $this->data['currency_value']),
				'{order_copy_url}' => document::ilink('order', ['order_no' => $this->data['no'], 'public_key' => $this->data['public_key']], false, [], $language_code),
				'{order_status}' => $order_status ? $order_status->name : null,
				'{store_name}' => settings::get('store_name'),
				'{store_url}' => document::ilink('', [], false, [], $language_code),
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

					$aliases['{order_items}'] .= (float)$item['quantity'] .' x '. $product->name . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "\r\n";

				} else {
					$aliases['{order_items}'] .= (float)$item['quantity'] .' x '. $item['name'] . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "\r\n";
				}
			}

			$aliases['{order_items}'] = trim($aliases['{order_items}']);

			$subject = '['. t('title_order', 'Order', $language_code) .' '. $this->data['no'] .'] '. t('title_order_confirmation', 'Order Confirmation', $language_code);

			$message = implode("\r\n", [
				'Thank you for your purchase!',
				'',
				'Your order #{order_no} has successfully been created with a total of {total} for the following ordered items:',
				'',
				'. {order_items}',
				'',
				'A printable order copy is available here:',
				'{order_copy_url}',
				'',
				'Regards,',
				'{store_name}',
				'{store_url}',
			]);

			$message = strtr(t('email_order_confirmation', $message, $language_code), $aliases);

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

			$email
				->add_recipient($recipient)
				->set_subject($subject)
				->add_body($message)
				->send();
		}

		public function send_email_notification(): void {

			if (!$this->data['order_status_id']) return;

			$order_status = reference::order_status($this->data['order_status_id'], $this->data['language_code']);

			$aliases = [
				'{order_id}' => $this->data['no'], // Backwards compatibility
				'{order_no}' => $this->data['no'],
				'{new_status}' => $order_status->name,
				'{firstname}' => $this->data['customer']['firstname'],
				'{lastname}' => $this->data['customer']['lastname'],
				'{billing_address}' => nl2br(f::format_address($this->data['customer']), false),
				'{payment_transaction_id}' => $this->data['payment_transaction_id'] ?: '-',
				'{shipping_address}' => nl2br(f::format_address($this->data['customer']['shipping_address']), false),
				'{shipping_tracking_id}' => $this->data['shipping_tracking_id'] ?: '-',
				'{shipping_tracking_url}' => $this->data['shipping_tracking_url'],
				'{shipping_current_status}' => $this->data['shipping_current_status'],
				'{shipping_current_location}' => $this->data['shipping_current_location'],
				'{order_items}' => null,
				'{total}' => currency::format($this->data['total'], true, $this->data['currency_code'], $this->data['currency_value']),
				'{order_copy_url}' => document::ilink('order', ['order_no' => $this->data['no'], 'public_key' => $this->data['public_key']], false, [], $this->data['language_code']),
				'{order_status}' => $order_status->name,
				'{store_name}' => settings::get('store_name'),
				'{store_url}' => document::ilink('', [], false, [], $this->data['language_code']),
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

					$aliases['{order_items}'] .= (float)$item['quantity'] .' x '. $product->name . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "<br>\r\n";

				} else {
					$aliases['{order_items}'] .= (float)$item['quantity'] .' x '. $item['name'] . (!empty($userdata) ? ' ('. implode(', ', $userdata) .')' : '') . "<br>\r\n";
				}
			}

			$subject = strtr($order_status->email_subject, $aliases);
			$message = strtr($order_status->email_message, $aliases);

			if (!$subject) {
				$subject = '['. t('title_order', 'Order', $this->data['language_code']) .' #'. $this->data['no'] .'] '. $order_status->name;
			}

			if (!$message) {
				$message = strtr(t('text_order_status_changed_to_new_status', 'Order status changed to {new_status}', $this->data['language_code']), $aliases);
			}

			if (!empty(language::$languages[$this->data['language_code']]) && language::$languages[$this->data['language_code']]['direction'] == 'rtl') {
				$message = implode(PHP_EOL, [
					'<div dir="rtl">',
					$message,
					'</div>',
				]);
			}

			(new ent_email())
				->add_recipient($this->data['customer']['email'], $this->data['customer']['firstname'] .' '. $this->data['customer']['lastname'])
				->set_subject($subject)
				->add_body($message, true)
				->send();
		}

		public function adjust_stock_quantity(int $product_id, array $combination, float|int $quantity_adjustment): void {
			if ($quantity_adjustment == 0) return;
			$product = new ent_product($product_id);
			$product->adjust_quantity((float)$quantity_adjustment, $combination);
		}

		public function delete(): void {

			if (!$this->data['id']) return;

			$order_modules = new mod_order();
			$order_modules->delete($this->previous);

			database::begin_transaction();

			try {

				database::query(
					"delete from ". DB_PREFIX ."orders_stock_items
					where order_id = ". (int)$this->data['id'] .";"
				);

				database::query(
					"delete from ". DB_PREFIX ."orders_comments
					where order_id = ". (int)$this->data['id'] .";"
				);

				database::query(
					"delete from ". DB_PREFIX ."orders_items
					where order_id = ". (int)$this->data['id'] .";"
				);

				database::query(
					"delete from ". DB_PREFIX ."orders
					where id = ". (int)$this->data['id'] ."
					limit 1;"
				);

					database::commit();

				f::webhook_send('order:deleted', $this->previous);

				$this->reset();

				cache::clear_cache('order');
				cache::clear_cache('category');
				cache::clear_cache('brand');
				cache::clear_cache('products');

			} catch (Throwable $e) {
				database::rollback();
				throw $e;
			}
		}
	}

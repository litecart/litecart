<?php

	if (!empty($_GET['order_id'])) {
		$order = new ent_order($_GET['order_id']);
	} else {
		$order = new ent_order();
		$order->data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$order->data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$order->data['created_at'] = date('Y-m-d H:i:s');
	}

	if (!$_POST) {

		$_POST = $order->data;

		// Convert to local currency
		foreach (array_keys($_POST['items']) as $key) {
			$_POST['items'][$key]['price'] = $_POST['items'][$key]['price'] ? $_POST['items'][$key]['price'] / $_POST['currency_value'] : 0;
			$_POST['items'][$key]['tax'] = $_POST['items'][$key]['tax'] ? $_POST['items'][$key]['tax'] / $_POST['currency_value'] : 0;
			$_POST['items'][$key]['discount'] = $_POST['items'][$key]['discount'] ? $_POST['items'][$key]['discount'] / $_POST['currency_value'] : 0;
			$_POST['items'][$key]['discount_tax'] = $_POST['items'][$key]['discount_tax'] ? $_POST['items'][$key]['discount_tax'] / $_POST['currency_value'] : 0;
			$_POST['items'][$key]['sum'] = $_POST['items'][$key]['sum'] ? $_POST['items'][$key]['sum'] / $_POST['currency_value'] : 0;
			$_POST['items'][$key]['sum_tax'] = $_POST['items'][$key]['sum_tax'] ? $_POST['items'][$key]['sum_tax'] / $_POST['currency_value'] : 0;
		}

		$_POST['subtotal'] = $_POST['subtotal'] ? $_POST['subtotal'] / $_POST['currency_value'] : 0;
		$_POST['subtotal_tax'] = $_POST['subtotal_tax'] ? $_POST['subtotal_tax'] / $_POST['currency_value'] : 0;

		$_POST['discount'] = $_POST['discount'] ? $_POST['discount'] / $_POST['currency_value'] : 0;
		$_POST['discount_tax'] = $_POST['discount_tax'] ? $_POST['discount_tax'] / $_POST['currency_value'] : 0;

		$_POST['total'] = $_POST['total'] ? $_POST['total'] / $_POST['currency_value'] : 0;
		$_POST['total_tax'] = $_POST['total_tax'] ? $_POST['total_tax'] / $_POST['currency_value'] : 0;

		if (empty($order->data['id'])) {
			$_POST['customer']['country_code'] = settings::get('default_country_code');
		}
	}

	document::$title[] = !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order');

	breadcrumbs::add(language::translate('title_orders', 'Orders'), document::ilink(__APP__.'/orders'));
	breadcrumbs::add(!empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['id'] : language::translate('title_create_new_order', 'Create New Order'), document::ilink());

	// Mark as read
	if (!empty($order->data['id'])) {
		database::query(
			"update ". DB_TABLE_PREFIX ."orders
			set unread = 0
			where id = ".  (int)$order->data['id'] ."
			limit 1;"
		);
	}

	// Create return order
	if (!empty($_POST['return'])) {

		try {

			if (empty($_POST['selected_items'])) {
				throw new Exception(language::translate('error_must_select_items', 'You must select items'));
			}

			$return_order = new ent_order();

			foreach ([
				'language_code',
				'currency_code',
				'currency_value',
				'display_prices_including_tax',
				'customer',
			] as $field) {
				$return_order->data[$field] = $order->data[$field];
			}

			foreach ($_POST['selected_items'] as $item_id) {
				$return_order->add_item(array_merge($order->data['items'][$item_id], ['quantity' => 0 - $order->data['items'][$item_id]['quantity']]));
			}

			$return_order->data['comments'] = [[
				'author' => 'system',
				'hidden' => true,
				'text' => 'Returned items from order '. $order->data['id'],
			]];

			$return_order->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/edit_order', ['order_id' => $return_order->data['id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Split order
	if (!empty($_POST['split'])) {

		try {

			if (empty($_POST['selected_items'])) {
				throw new Exception(language::translate('error_must_select_items', 'You must select items'));
			}

			$split_order = new ent_order();

			$split_order->previous['order_status_id'] = $order->data['order_status_id'];

			foreach ([
				'order_status_id',
				'reference',
				'language_code',
				'currency_code',
				'currency_value',
				'display_prices_including_tax',
				'customer',
				'shipping_option',
				'payment_option',
				'payment_transaction_id',
			] as $field) {
				$split_order->data[$field] = $order->data[$field];
			}

			foreach ($_POST['selected_items'] as $key) {
				$split_order->add_item($order->data['items'][$key]);
				unset($order->data['items'][$key]);
			}

			$split_order->data['shipping_option'] = $order->data['shipping_option'];
			$split_order->data['payment_option'] = $order->data['payment_option'];

			$split_order->data['comments'] = [[
				'author' => 'system',
				'hidden' => true,
				'text' => 'Splitted from order '. $order->data['id'],
			]];

			$split_order->save();
			$order->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Save data to database
	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['items'])) {
				$_POST['items'] = [];
			}

			if (empty($_POST['comments'])) {
				$_POST['comments'] = [];
			}

			if (!empty($_POST['items'])) {
				foreach (array_keys($_POST['items']) as $key) {
					$_POST['items'][$key]['price'] = !empty($_POST['items'][$key]['price']) ? (float)$_POST['items'][$key]['price'] * (float)$_POST['currency_value'] : 0;
					$_POST['items'][$key]['tax'] = !empty($_POST['items'][$key]['tax']) ? (float)$_POST['items'][$key]['tax'] * (float)$_POST['currency_value'] : 0;
					$_POST['items'][$key]['discount'] = !empty($_POST['items'][$key]['price']) ? (float)$_POST['items'][$key]['discount'] * (float)$_POST['currency_value'] : 0;
					$_POST['items'][$key]['discount_tax'] = !empty($_POST['items'][$key]['price']) ? (float)$_POST['items'][$key]['discount_tax'] * (float)$_POST['currency_value'] : 0;
					$_POST['items'][$key]['sum'] = !empty($_POST['items'][$key]['price']) ? (float)$_POST['items'][$key]['sum'] * (float)$_POST['currency_value'] : 0;
					$_POST['items'][$key]['sum_tax'] = !empty($_POST['items'][$key]['price']) ? (float)$_POST['items'][$key]['sum_tax'] * (float)$_POST['currency_value'] : 0;
				}
			}

			if (!empty($_POST['customer']['save']) || !empty($_POST['shipping_address']['save'])) {

				if (!empty($_POST['customer']['id'])) {
					$customer = new ent_customer($_POST['customer']['id']);

				} else if ($customer = database::query(
					"select id from ". DB_TABLE_PREFIX ."customers
					where email = '". database::input($_POST['customer']['email']) ."'
					limit 1;"
				)->fetch()) {
					$customer = new ent_customer($customer['id']);

				} else {
					$customer = new ent_customer();
					$customer->data['email'] = $_POST['customer']['email'];
				}

				if (!empty($_POST['customer']['save'])) {
					foreach ([
						'tax_id',
						'company',
						'firstname',
						'lastname',
						'address1',
						'address2',
						'postcode',
						'city',
						'country_code',
						'zone_code',
						'phone',
						'email',
					] as $field) {
						if (isset($_POST['customer'][$field])) {
							$customer->data['customer'][$field] = $_POST['customer'][$field];
						}
					}
				}

				if (!empty($_POST['shipping_address']['save'])) {
					foreach ([
						'tax_id',
						'company',
						'firstname',
						'lastname',
						'address1',
						'address2',
						'postcode',
						'city',
						'country_code',
						'zone_code',
						'phone',
						'email',
					] as $field) {
						if (isset($_POST['shipping_address'][$field])) {
							$customer->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
						}
					}
				}

				$customer->save();

				$order->data['customer']['id'] = $customer->data['id'];
			}

			foreach ([
				'unread',
				'language_code',
				'currency_code',
				'currency_value',
				'items',
				'order_status_id',
				'shipping_tracking_id',
				'shipping_tracking_url',
				'shipping_purchase_cost',
				'payment_transaction_id',
				'payment_transaction_fee',
				'payment_receipt_url',
				'payment_terms',
				'display_prices_including_tax',
				'reference',
				'date_paid',
				'date_dispatched',
				'comments',
			] as $field) {
				if (isset($_POST[$field])) {
					$order->data[$field] = $_POST[$field];
				}
			}

			foreach ([
				'id',
				'tax_id',
				'company',
				'firstname',
				'lastname',
				'address1',
				'address2',
				'postcode',
				'city',
				'country_code',
				'zone_code',
				'phone',
				'email',
			] as $field) {
				if (isset($_POST['customer'][$field])) {
					$order->data['customer'][$field] = $_POST['customer'][$field];
				}
			}

			foreach ([
				'tax_id',
				'company',
				'firstname',
				'lastname',
				'address1',
				'address2',
				'postcode',
				'city',
				'country_code',
				'zone_code',
				'phone',
				'email',
			] as $field) {
				if (isset($_POST['shipping_address'][$field])) {
					$order->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
				}
			}

			$order->save();

			if (!empty($_POST['email_order_copy'])) {

				$bccs = [];
				foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy'), -1, PREG_SPLIT_NO_EMPTY) as $email) {
					$bccs[] = $email;
				}

				$order->email_order_copy($order->data['customer']['email'], $bccs, $order->data['language_code']);
			}

			if (!empty($_GET['redirect_url'])) {
				$redirect_url = new ent_link($_GET['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink(__APP__.'/orders');
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect($redirect_url);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($order->data['id'])) {
				throw new Exception(language::translate('error_must_provide_order', 'You must provide an order'));
			}

			$order->delete();

			if (!empty($_GET['redirect_url'])) {
				$redirect_url = new ent_link($_GET['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink(__APP__.'/orders');
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect($redirect_url);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$account_name = '('. language::translate('title_guest', 'Guest') .')';
	if (!empty($_POST['customer']['id'])) {
		$customer = reference::customer((int)$_POST['customer']['id']);
		$account_name = $customer->company ?: $customer->firstname .' '. $customer->lastname;
	}

?>
<style>
#hostname {
	text-overflow: ellipsis;
	overflow-x: hidden;
	white-space: nowrap;
}
#order-lines tr.highlight {
	border: 1px #f00 solid;
}
#order-lines tr.extended {
	display: none;
}
#order-lines tr.highlight + tr.extended {
	display: table-row;
}

#box-comments .bubbles .private {
	position: absolute;
	top: 0.5em;
	inset-inline-end: 2.5em;
	cursor: pointer;
}
#box-comments .bubble {
	padding-top: 2em;
}
#box-comments .bubbles .notify  {
	position: absolute;
	top: 0.5em;
	inset-inline-end: 4em;
	cursor: pointer;
}
#box-comments .bubbles .private input[name$="[hidden]"],
#box-comments .bubbles .notify input[name$="[notify]"] {
	display: none;
}
#box-comments .bubbles .private input[name$="[hidden]"] + .fa,
#box-comments .bubbles .notify input[name$="[notify]"] + .fa {
	opacity: 0.25;
}
#box-comments .bubbles .private input[name$="[hidden]"]:checked + .fa,
#box-comments .bubbles .notify input[name$="[notify]"]:checked + .fa {
	opacity: 1;
}

#box-comments .bubbles .semi-transparent {
	opacity: 0.5;
}

#modal-customer-picker tbody tr {
	cursor: pointer;
}

#order-lines {
	margin-bottom: 2em;
}

#order-totals .title {
	margin-bottom: 0.5em;
}
#order-totals .amount {
	font-size: 1.5em;
}
#order-totals .summary {
	background: var(--card-background);
	padding: 1em;
	border-radius: var(--border-radius);
}
#order-totals #order-total {
	font-weight: bold;
}
</style>

<?php echo functions::form_begin('form_order', 'post'); ?>

	<div class="card">
		<div class="card-header">
			<div class="card-title">
				<?php echo $app_icon; ?> <?php echo !empty($order->data['id']) ? language::translate('title_edit_order', 'Edit Order') .' #'. $order->data['no'] : language::translate('title_create_new_order', 'Create New Order'); ?>
			</div>
		</div>

		<div class="card-action">
			<ul class="list-inline">
				<li>
					<?php echo functions::form_checkbox('send_order_copy', ['1', language::translate('text_send_order_copy_email', 'Send order copy email')], true); ?>
				</li>
				<li>
					<?php echo functions::form_checkbox('unread', ['1', language::translate('title_mark_as_unread', 'Mark as unread')], false); ?>
				</li>
				<li>
					<?php echo functions::form_button_predefined('save'); ?>
					<?php if (!empty($order->data['id'])) echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?>
					<?php echo functions::form_button_predefined('cancel'); ?>
				</li>
			</ul>
		</div>

		<div class="card-body">

			<div class="grid">
				<div class="col-md-8">
					<div class="grid">
						<div class="col-md-4">
							<div class="form-group">
								<div class="form-label"><?php echo language::translate('title_order_status', 'Order Status'); ?></div>
								<?php echo functions::form_select_order_status('order_status_id', true); ?>
							</div>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_date', 'Date'); ?></div>
								<div class="form-input" readonly><?php echo functions::datetime_when($order->data['created_at']); ?></div>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_reference', 'Reference'); ?></div>
								<?php echo functions::form_input_text('reference', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_language', 'Language'); ?></div>
								<?php echo functions::form_select_language('language_code', true); ?>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_currency', 'Currency'); ?></div>
								<?php echo functions::form_select_currency('currency_code', true); ?>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_currency_value', 'Currency Value'); ?></div>
								<?php echo functions::form_input_decimal('currency_value', true, 4); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-4">
							<div class="form-group">
								<label class="form-label"><?php echo language::translate('title_tax_display', 'Tax Display'); ?></label>
								<?php echo functions::form_toggle('display_prices_including_tax', ['1' => language::translate('title_incl_tax', 'Incl. Tax'), '0' => language::translate('title_excl_tax', 'Excl. Tax')], true); ?>
							</div>
						</div>

						<div class="col-md-8">
							<div class="form-group">
								<label class="form-label"><?php echo language::translate('title_ip_address', 'IP Address'); ?> (<?php echo language::translate('title_hostname', 'Hostname'); ?>)</label>
								<div id="hostname" class="input-group">
									<div class="form-input" style="overflow: hidden; text-overflow: ellipsis;">
										<?php echo $order->data['ip_address']; ?> <?php echo !empty($order->data['hostname']) ? '('. $order->data['hostname'] .')' : ''; ?>
										<?php if (!empty($order->data['ip_address'])) { ?>
										<a class="btn btn-default btn-sm" href="https://ip-api.com/#<?php echo $order->data['ip_address']; ?>" target="_blank" style="margin: -.5em 0; margin-inline-start: 1em;">
											<?php echo functions::draw_fonticon('icon-square-out', ''); ?>
										</a>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_notes', 'Notes'); ?></div>
						<?php echo functions::form_textarea('notes', true, 'style="height: 191px;"'); ?>
					</label>
				</div>
			</div>

		</div>
	</div>

	<div class="grid grid-condensable">
		<div id="customer-details" class="col-lg-9">

			<div class="card">
				<div class="card-body">
					<div class="grid">
						<div id="billing-address" class="col-md-6">
							<h2><?php echo language::translate('title_billing_address', 'Billing Address'); ?></h2>

							<div class="form-group">
								<div class="input-group">
									<div class="selected-account form-input"><?php echo language::translate('title_id', 'ID'); ?>: <span class="id"><?php if (isset($_POST['customer']['id'])) echo (int)$_POST['customer']['id']; ?></span> &ndash; <span class="name"><?php echo $account_name; ?></span> <a href="<?php echo document::href_ilink('customers/customer_picker'); ?>" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;"><?php echo language::translate('title_change', 'Change'); ?></a></div>
									<?php echo functions::form_input_hidden('customer[id]', true); ?>
									<?php echo functions::form_button('get_address', language::translate('title_get_address', 'Get Address'), 'button'); ?>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_company_name', 'Company Name'); ?></div>
										<?php echo functions::form_input_text('customer[company]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_tax_id', 'Tax ID / VATIN'); ?></div>
										<?php echo functions::form_input_text('customer[tax_id]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
										<?php echo functions::form_input_text('customer[firstname]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
										<?php echo functions::form_input_text('customer[lastname]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
										<?php echo functions::form_input_text('customer[address1]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
										<?php echo functions::form_input_text('customer[address2]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
										<?php echo functions::form_input_text('customer[postcode]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
										<?php echo functions::form_input_text('customer[city]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
										<?php echo functions::form_select_country('customer[country_code]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
										<?php echo form_select_zone('customer[zone_code]', fallback($_POST['customer']['country_code']), true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
										<?php echo functions::form_input_phone('customer[phone]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
										<?php echo functions::form_input_email('customer[email]', true); ?>
									</label>
								</div>
							</div>

							<div class="form-group">
								<?php echo functions::form_checkbox('customer[save]', ['1', language::translate('text_save_details_to_customer_database', 'Save details to customer database')], true); ?>
							</div>
						</div>

						<div id="shipping-address" class="col-md-6">
							<h2><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></h2>

							<div class="form-group">
								<?php echo functions::form_button('copy_billing_address', language::translate('title_copy_billing_address', 'Copy Billing Address'), 'button', 'class="btn btn-default btn-block" style="margin: 3px 0;"'); ?>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_company_name', 'Company Name'); ?></div>
										<?php echo functions::form_input_text('shipping_address[company]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_tax_id', 'Tax ID / VATIN'); ?></div>
										<?php echo functions::form_input_text('customer[tax_id]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
										<?php echo functions::form_input_text('shipping_address[firstname]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
										<?php echo functions::form_input_text('shipping_address[lastname]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
										<?php echo functions::form_input_text('shipping_address[address1]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
										<?php echo functions::form_input_text('shipping_address[address2]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
										<?php echo functions::form_input_text('shipping_address[postcode]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
										<?php echo functions::form_input_text('shipping_address[city]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
										<?php echo functions::form_select_country('shipping_address[country_code]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
										<?php echo form_select_zone('shipping_address[zone_code]', fallback($_POST['shipping_address']['country_code']), true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
										<?php echo functions::form_input_phone('shipping_address[phone]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
										<?php echo functions::form_input_email('shipping_address[email]', true, 'required'); ?>
									</label>
								</div>
							</div>

							<div class="form-group">
								<?php echo functions::form_checkbox('shipping_address[save]', ['1', language::translate('text_save_details_to_customer_database', 'Save details to customer database')], true); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-body">
					<div class="grid">
						<div class="col-md-6">
							<h2><?php echo language::translate('title_payment_details', 'Payment Details'); ?></h2>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_option_id', 'Option ID'); ?></div>
										<?php echo functions::form_input_text('payment_option[id]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
										<?php echo functions::form_input_text('payment_option[name]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-7">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_transaction_id', 'Transaction ID'); ?></div>
										<?php echo functions::form_input_text('payment_transaction_id', true); ?>
									</label>
								</div>

								<div class="col-md-5">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_transaction_fee', 'Transaction Fee'); ?></div>
										<?php echo functions::form_input_money('payment_transaction_fee', settings::get('store_currency_code'), true); ?>
									</label>
								</div>
							</div>

							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_receipt_url', 'Receipt URL'); ?></div>
								<?php echo functions::form_input_url('payment_receipt_url', true); ?>
							</label>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_payment_terms', 'Payment Terms'); ?></div>
										<?php echo functions::form_select_payment_term('payment_terms', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_date_paid', 'Date Paid'); ?></div>
										<?php echo functions::form_input_datetime('date_paid', true); ?>
									</label>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<h2><?php echo language::translate('title_shipping_details', 'Shipping Details'); ?></h2>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_option_id', 'Option ID'); ?></div>
										<?php echo functions::form_input_text('shipping_option[id]', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
										<?php echo functions::form_input_text('shipping_option[name]', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-8">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_tracking_id', 'Tracking ID'); ?></div>
										<?php echo functions::form_input_text('shipping_tracking_id', true); ?>
									</label>
								</div>

								<div class="col-md-4">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_purchase_cost', 'Purchase Cost'); ?></div>
										<?php echo functions::form_input_money('shipping_purchase_cost', settings::get('store_currency_code'), true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-7">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_tracking_url', 'Tracking URL'); ?></div>
										<?php echo functions::form_input_url('shipping_tracking_url', true); ?>
									</label>
								</div>

								<div class="col-md-5">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_total_weight', 'Total Weight'); ?></div>
										<div class="form-input"><?php echo weight::format($order->data['weight_total'], $order->data['weight_unit']) ?></div>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_incoterm', 'Incoterm'); ?></div>
										<?php echo functions::form_select_incoterm('incoterm', true); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_date_dispatched', 'Date Dispatched'); ?></div>
										<?php echo functions::form_input_datetime('date_dispatched', true); ?>
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 card flex flex-rows">
			<div class="card-body flex flex-rows">

				<h2 style="flex-grow: 0;">
					<?php echo language::translate('title_comments', 'Comments'); ?>
				</h2>

				<div id="box-comments">
					<div class="bubbles">
<?php
	foreach (array_keys($_POST['comments']) as $key) {

		switch($_POST['comments'][$key]['author']) {

			case 'customer':
				$type = 'remote';
				break;

			case 'staff':
				$type = 'local';
				break;

			default:
				$type = 'event';
				break;
		}

		if (!empty($_POST['comments'][$key]['hidden'])) {
			$type .= ' semi-transparent';
		}
?>
						<div class="bubble <?php echo $type; ?>">
							<?php echo functions::form_input_hidden('comments['.$key.'][id]', true); ?>
							<?php echo functions::form_input_hidden('comments['.$key.'][order_id]', true); ?>
							<?php echo functions::form_input_hidden('comments['.$key.'][author]', true); ?>
							<?php echo functions::form_input_hidden('comments['.$key.'][text]', true); ?>

							<?php echo nl2br($_POST['comments'][$key]['text']); ?>

							<div class="date"><?php echo functions::datetime_when($_POST['comments'][$key]['created_at']); ?></div>

							<div class="actions">
								<a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('icon-times'); ?></a>
								<label class="private" title="<?php echo functions::escape_html(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::form_checkbox('comments['.$key .'][hidden]', '1', true); ?> <?php echo functions::draw_fonticon('icon-eye-slash'); ?></label>
							</div>
						</div>
						<?php } ?>

						<div class="text-end">
							<button class="add btn btn-default" type="button" title="<?php echo language::translate('title_add', 'Add'); ?>"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_add_comment', 'Add Comment'); ?></button>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>

	<div class="card card-default">
		<div class="card-body">
			<h2><?php echo language::translate('title_order_lines', 'Order Lines'); ?></h2>
		</div>

		<table id="order-lines" class="table table-input data-table">
			<thead>
				<tr>
					<th style="width: 50px;"><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo language::translate('title_item', 'Item'); ?></th>
					<th><?php echo language::translate('title_sku', 'SKU'); ?></th>
					<th style="width: 100px;" class="text-center"><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
					<th style="width: 125px;" class="text-center"><?php echo language::translate('title_qty', 'Qty'); ?></th>
					<th style="width: 150px;" class="text-center"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
					<th style="width: 150px;" class="text-center"><?php echo language::translate('title_discount', 'Discount'); ?></th>
					<th style="width: 100px;" class="text-end"><?php echo language::translate('title_sum', 'Sum'); ?></th>
					<th style="width: 100px;" class="text-end"><?php echo language::translate('title_tax', 'Tax'); ?></th>
					<th style="width: 50px;"></th>
					<th style="width: 120px;"></th>
				</tr>
			</thead>

			<tbody>
				<?php if (!empty($_POST['items'])) foreach (array_keys($_POST['items']) as $key) { ?>
				<tr class="item" draggable="true">
					<td><?php echo functions::form_checkbox('selected_items[]', $key, true); ?></td>
					<td>
						<?php echo !empty($_POST['items'][$key]['product_id']) ? '<a class="link" href="'. document::href_ilink('f:product', ['product_id' => $_POST['items'][$key]['product_id']]) .'" target="_blank">'. $_POST['items'][$key]['name'] .'</a>' : $_POST['items'][$key]['name']; ?>
						<?php echo functions::form_input_hidden('items['.$key.'][id]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][type]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][product_id]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][stock_item_id]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][name]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][data]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][serial_number]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][sku]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][gtin]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][taric]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][weight]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][weight_unit]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][length]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][width]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][height]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][length_unit]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][tax_class_id]', true); ?>
						<?php echo functions::form_input_hidden('items['.$key.'][tax_rate]', true); ?>
					</td>
					<td class="sku"><?php echo functions::escape_html($_POST['items'][$key]['sku']); ?></td>
					<td class="text-center"><?php if (isset($_POST['items'][$key]['sufficient_stock'])) echo $item['sufficient_stock'] ? '<span style="color: #88cc44;">'. functions::draw_fonticon('icon-check') .' '. $item['stock_quantity'] .'</span>' : '<span style="color: #ff6644;">'. functions::draw_fonticon('icon-times') .' '. $item['stock_quantity'] .'</span>'; ?></td>
					<td><?php echo functions::form_input_decimal('items['.$key.'][quantity]', true, 2); ?></td>
					<td><?php echo functions::form_input_decimal('items['.$key.'][price]', true); ?></td>
					<td><?php echo functions::form_input_decimal('items['.$key.'][discount]', true); ?></td>
					<td class="text-end sum"><?php echo currency::format($_POST['items'][$key]['sum'], false, $_POST['currency_code'], $_POST['currency_value']); ?></td>
					<td class="text-end sum_tax"><?php echo currency::format($_POST['items'][$key]['sum_tax'], false, $_POST['currency_code'], $_POST['currency_value']); ?></td>
					<td class="grabbable">
						<?php echo functions::draw_fonticon('icon-arrows-v'); ?>
					</td>
					<td>
						<a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>">
							<?php echo functions::draw_fonticon('remove'); ?>
						</a>
						<a class="btn btn-default btn-sm edit" href="#" title="<?php echo language::translate('title_edit', 'Edit'); ?>">
							<?php echo functions::draw_fonticon('edit'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<button name="add_product" class="btn btn-default" href="<?php echo document::href_ilink('catalog/product_picker'); ?>" data-toggle="lightbox" data-callback="selectProduct"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_add_product', 'Add Product'); ?></button>
						<?php echo functions::form_button('add', language::translate('title_add_line_item', 'Add Line Item'), 'button', '', 'add'); ?>
						<?php echo functions::form_button('return', language::translate('title_return_items', 'Return Items'), 'submit', 'formnovalidate onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'icon-reply'); ?>
						<?php echo functions::form_button('split', language::translate('title_split_lines_from_order', 'Split Lines From Order'), 'submit', 'formnovalidate onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'icon-clone'); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-footer">
			<div id="order-totals" class="grid">

				<div class="col-md-2">
				</div>

				<div class="col-md-2">
					<div id="subtotal" class="summary">
						<div class="title"><?php echo language::translate('title_subtotal', 'Subtotal'); ?></div>
						<div class="amount"><?php echo currency::format($_POST['discount'], false, $_POST['currency_code'], $_POST['currency_value']); ?></div>
					</div>
				</div>

				<div class="col-md-2">
				<div id="total-discount" class="summary">
						<div class="title"><?php echo language::translate('title_total_discount', 'Total Discount'); ?></div>
						<div class="amount"><?php echo currency::format($_POST['discount'], false, $_POST['currency_code'], $_POST['currency_value']); ?></div>
					</div>
				</div>

				<div class="col-md-2">
					<div id="total-fees" class="summary">
						<div class="title"><?php echo language::translate('title_total_fees', 'Total Fees'); ?></div>
						<div class="amount"><?php echo currency::format(0, false, $_POST['currency_code'], $_POST['currency_value']); ?></div>
					</div>
				</div>

				<div class="col-md-2">
					<div id="total-tax" class="summary">
						<div class="title"><?php echo language::translate('title_total_tax', 'Total Tax'); ?></div>
						<div class="amount"><?php echo currency::format($_POST['total_tax'], false, $_POST['currency_code'], $_POST['currency_value']); ?></div>
					</div>
				</div>

				<div class="col-md-2">
					<div id="order-total" class="summary">
						<div class="title"><?php echo language::translate('title_grand_total', 'Grand Total'); ?></div>
						<div class="amount"><?php echo currency::format_html($_POST['total'], false, $_POST['currency_code'], $_POST['currency_value']); ?></div>
					</div>
				</div>
			</div>
	 	</div>
	</div>

<?php echo functions::form_end(); ?>

<div id="modal-edit-line-item" class="modal fade" style="max-width: 980px; display: none;">

	<h2><?php echo language::translate('title_edit_line_item', 'Edit Line Item'); ?></h2>

	<div class="modal-body">

		<div class="grid">
			<div class="col-md-8">

				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_type', 'Type'); ?></div>
					<?php echo functions::form_toggle('type', ['product' => language::translate('title_product', 'Product'), 'custom' => language::translate('title_custom', 'Custom'), 'fee' => language::translate('title_fee', 'Fee')], true); ?>
				</label>

				<div class="grid">
					<div class="col-md-8">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
							<?php echo functions::form_input_text('name', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_product', 'Product'); ?></div>
							<?php echo functions::form_select_product('product_id', ''); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_sku', 'SKU'); ?></div>
							<?php echo functions::form_input_text('sku', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_gtin', 'GTIN'); ?></div>
							<?php echo functions::form_input_text('gtin', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_taric', 'TARIC'); ?></div>
							<?php echo functions::form_input_text('taric', ''); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_weight', 'Weight'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('weight', true, 3, 'min="0"'); ?>
								<?php echo functions::form_select_weight_unit('weight_unit', true); ?>
							</div>
						</label>
					</div>

					<div class="col-md-8">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('length', true, 3, 'min="0"'); ?>
								<span class="input-group-text">x</span>
								<?php echo functions::form_input_decimal('width', true, 3, 'min="0"'); ?>
								<span class="input-group-text">x</span>
								<?php echo functions::form_input_decimal('height', true, 3, 'min="0"'); ?>
								<?php echo functions::form_select_length_unit('length_unit', true); ?>
							</div>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_quantity', 'Quantity'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('quantity', true, 2); ?>
								<?php echo functions::form_select_quantity_unit('quantity_unit_id', true); ?>
							</div>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_price', 'Price'); ?></div>
							<?php echo functions::form_input_money('price', $_POST['currency_code'], ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_discount', 'Discount'); ?></div>
							<?php echo functions::form_input_money('discount', $_POST['currency_code'], ''); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_tax_class', 'Tax Class'); ?></div>
							<?php echo functions::form_select_tax_class('tax_class_id', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_tax_rate', 'Tax Rate'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('tax_rate', true, 2, 'readonly'); ?>
								<span class="input-group-text">%</span>
							</div>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_tax', 'Tax'); ?></div>
							<?php echo functions::form_input_money('tax', $_POST['currency_code'], true, 'readonly'); ?>
						</label>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_stock_items', 'Stock Items'); ?></div>
					<?php echo functions::form_select_stock_item('stock_items', true, 'style="height: 490px;"'); ?>
				</label>
			</div>
		</div>

		<div class="card-action">
			<?php echo functions::form_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
			<?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.litebox.close();"', 'cancel'); ?>
		</div>
	</div>
</div>

<div id="modal-edit-line-product" class="modal fade" style="max-width: 980px; display: none;">

	<h2><?php echo language::translate('title_edit_line_item', 'Edit Line Item'); ?></h2>

	<div class="modal-body">

		<div class="grid">
			<div class="col-md-8">

				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_type', 'Type'); ?></div>
					<?php echo functions::form_toggle('type', ['product' => language::translate('title_product', 'Product'), 'custom' => language::translate('title_custom', 'Custom'), 'fee' => language::translate('title_fee', 'Fee')], true); ?>
				</label>

				<div class="grid">
					<div class="col-md-8">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
							<?php echo functions::form_input_text('name', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_product', 'Product'); ?></div>
							<?php echo functions::form_select_product('product_id', ''); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_sku', 'SKU'); ?></div>
							<?php echo functions::form_input_text('sku', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_gtin', 'GTIN'); ?></div>
							<?php echo functions::form_input_text('gtin', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_taric', 'TARIC'); ?></div>
							<?php echo functions::form_input_text('taric', ''); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_weight', 'Weight'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('weight', true, 3, 'min="0"'); ?>
								<?php echo functions::form_select_weight_unit('weight_unit', true); ?>
							</div>
						</label>
					</div>

					<div class="col-md-8">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_dimensions', 'Dimensions'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('length', true, 3, 'min="0"'); ?>
								<span class="input-group-text">x</span>
								<?php echo functions::form_input_decimal('width', true, 3, 'min="0"'); ?>
								<span class="input-group-text">x</span>
								<?php echo functions::form_input_decimal('height', true, 3, 'min="0"'); ?>
								<?php echo functions::form_select_length_unit('length_unit', true); ?>
							</div>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_quantity', 'Quantity'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('quantity', true); ?>
								<?php echo functions::form_select_quantity_unit('quantity_unit_id', true); ?>
							</div>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_price', 'Price'); ?></div>
							<?php echo functions::form_input_money('price', $_POST['currency_code'], ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_discount', 'Discount'); ?></div>
							<?php echo functions::form_input_money('discount', $_POST['currency_code'], ''); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_tax_class', 'Tax Class'); ?></div>
							<?php echo functions::form_select_tax_class('tax_class_id', ''); ?>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_tax_rate', 'Tax Rate'); ?></div>
							<div class="input-group">
								<?php echo functions::form_input_decimal('items['.$key.'][tax_rate]', true, 2, 'readonly'); ?>
								<span class="input-group-text">%</span>
							</div>
						</label>
					</div>

					<div class="col-md-4">
						<label class="form-group">
							<div class="form-label"><?php echo language::translate('title_tax', 'Tax'); ?></div>
							<?php echo functions::form_input_money('tax', $_POST['currency_code'], true, 'readonly'); ?>
						</label>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_stock_items', 'Stock Items'); ?></div>
					<?php echo functions::form_select_stock_item('stock_items[]', true, 'style="height: 490px;"'); ?>
				</label>
			</div>
		</div>

		<div class="card-action">
			<?php echo functions::form_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
			<?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.litebox.close();"', 'cancel'); ?>
		</div>
	</div>
</div>

<script>

	// Local Page Money Formatting
	Number.prototype.toMoney = function(html) {

		var n = this,
			c = $('select[name="currency_code"] option:selected').val()
			d = $('select[name="currency_code"] option:selected').data('decimals'),
			p = _env.language.decimal_point,
			t = _env.language.thousands_separator,
			b = $('select[name="currency_code"] option:selected').data('prefix'),
			e = $('select[name="currency_code"] option:selected').data('suffix'),
			s = n < 0 ? '-' : '',
			i = parseInt(n = Math.abs(+n || 0).toFixed(d)) + '',
			f = n - i,
			j = (j = i.length) > 3 ? j % 3 : 0;

		if (html) {
			return '<span class="currency-amount"><small class="currency">'+ c +'</small> '+ s + b + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (d ? '<span class="decimals">' + p + Math.abs(f).toFixed(d).slice(2) + '</span>' : '') + e + '</span>';
		}

		return s + b + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (d ? p + Math.abs(f).toFixed(d).slice(2) : '') + e;
	}

	// Order

	$('select[name="order_status_id"]').on('change', function(e) {
		let color = $(this).find('option:selected').data('color');
		$(this).css('box-shadow', color ? '0 0 0px 2px'+ color +'cc' : '');
	}).trigger('change');

	$('select[name="currency_code"]').on('change', function(e) {
		$('input[type="number"][data-type="currency"]').data('decimals', $(this).find('option:selected').data('decimals'));
		$('input[name="currency_value"]').val($(this).find('option:selected').data('value'));
		$('input[data-type="currency"]').closest('.input-group').find('.input-group-text').text($(this).val());
		refresh_total();
	});

	// Customer

	$('#customer-details button[name="get_address"]').on('click', function() {
		$.ajax({
			url: '<?php echo document::ilink('customers/get_address.json'); ?>',
			type: 'post',
			data: 'customer_id=' + $('*[name="customer[id]"]').val(),
			cache: true,
			async: false,
			dataType: 'json',
			success: function(data) {
				$.each(data, function(key, value) {
					if (key.match(/^customer/)) {
						$.each(value, function(key, value) {
							if ($(':input[name="customer['+key+']"]').length) $(':input[name="customer['+key+']"]').val(value).trigger('change');
						});
					} else if (key.match(/^shipping_address/)) {
						$.each(value, function(key, value) {
							if ($(':input[name="shipping_address['+key+']"]').length) $(':input[name="shipping_address['+key+']"]').val(value).trigger('change');
						});
					} else {
						if ($(':input[name="customer['+key+']"]').length) $(':input[name="customer['+key+']"]').val(value).trigger('change');
					}
				});
			},
		});
	});

	$('#customer-details select[name="customer[country_code]"]').on('change', function() {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="customer[tax_id]"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
		} else {
			$('input[name="customer[tax_id]"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="customer[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
		} else {
			$('input[name="customer[postcode]"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="customer[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
		} else {
			$('input[name="customer[phone]"]').removeAttr('placeholder');
		}

		$.ajax({
			url: '<?php echo document::ilink('countries/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: false,
			dataType: 'json',
			success: function(data) {
				$('select[name="customer[zone_code]"]').html('');
				if ($('select[name="customer[zone_code]"]').is(':disabled')) $('select[name="customer[zone_code]"]').prop('disabled', false);
				if (data) {
					$.each(data, function(i, zone) {
						$('select[name="customer[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="customer[zone_code]"]').prop('disabled', true);
				}
				$('select[name="customer[zone_code]"]').trigger('change');
			},
		});
	});

	$('#customer-details button[name="copy_billing_address"]').on('click', function() {
		$.each(['company', 'firstname', 'lastname', 'address1', 'address2', 'postcode', 'city', 'country_code', 'zone_code', 'phone'], function(key, field) {
			$('*[name="shipping_address['+ field +']"]').val($('*[name="customer['+ field +']"]').val()).trigger('change');
		});
	});

	$('#customer-details select[name="shipping_address[country_code]"]').on('change', function() {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="shipping_address[tax_id]"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
		} else {
			$('input[name="shipping_address[tax_id]"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="shipping_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
		} else {
			$('input[name="shipping_address[postcode]"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
		} else {
			$('input[name="shipping_address[phone]"]').removeAttr('placeholder');
		}

		$('body').css('cursor', 'wait');
		$.ajax({
			url: '<?php echo document::ilink('countries/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="shipping_address[zone_code]"]').html('');
				if ($('select[name="shipping_address[zone_code]"]').is(':disabled')) $('select[name="shipping_address[zone_code]"]').prop('disabled', false);
				if (data) {
					$.each(data, function(i, zone) {
						$('select[name="shipping_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', true);
				}
				$('select[name="customer[zone_code]"]').trigger('change');
			}
		});
	});

	if ($('select[name="customer[country_code]"] option:selected').data('tax-id-format')) {
		$('input[name="customer[tax_id]"]').attr('pattern', $('select[name="country_code"] option:selected').data('tax-id-format'));
	} else {
		$('input[name="customer[tax_id]"]').removeAttr('pattern');
	}

	if ($('select[name="customer[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="customer[postcode]"]').attr('pattern', $('select[name="customer[country_code]"] option:selected').data('postcode-format'));
	} else {
		$('input[name="customer[postcode]"]').removeAttr('pattern');
	}

	if ($('select[name="customer[country_code]"] option:selected').data('phone-code')) {
		$('input[name="customer[phone]"]').attr('placeholder', '+' + $('select[name="customer[country_code]"] option:selected').data('phone-code'));
	} else {
		$('input[name="customer[phone]"]').removeAttr('placeholder');
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('tax-id-format')) {
		$('input[name="shipping_address[tax_id]"]').attr('pattern', $('select[name="shipping_address[country_code]"] option:selected').data('tax-id-format'));
	} else {
		$('input[name="shipping_address[tax_id]"]').removeAttr('pattern');
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"] option:selected').data('postcode-format'));
	} else {
		$('input[name="shipping_address[postcode]"]').removeAttr('pattern');
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('phone-code')) {
		$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $('select[name="shipping_address[country_code]"] option:selected').data('phone-code'));
	} else {
		$('input[name="shipping_address[phone]"]').removeAttr('placeholder');
	}
	$('select[name="language_code"], select[name="currency_code"], input[name="currency_value"], :input[name^="customer"]').on('input', function() {
		let params = {
			language_code: $('select[name="language_code"]').val(),
			currency_code: $('select[name="currency_code"]').val(),
			currency_value: $('input[name="currency_value"]').val(),
			customer: {
				id: $(':input[name="customer[id]"]').val(),
				tax_id: $('input[name="customer[tax_id]"]').val(),
				company: $('input[name="customer[company]"]').val(),
				country_code: $('select[name="customer[country_code]"]').val(),
				zone_code: $('select[name="customer[zone_code]"]').val(),
				city: $('select[name="customer[city]"]').val(),
				shipping_address: {
					tax_id: $('input[name="shipping_address[tax_id]"]').val(),
					company: $('input[name="shipping_address[company]"]').val(),
					country_code: $('select[name="shipping_address[country_code]"]').val(),
					zone_code: $('select[name="shipping_address[zone_code]"]').val(),
					city: $('select[name="shipping_address[city]"]').val(),
				}
			}
		};

		$('.add-product').attr('href', '<?php echo document::ilink('catalog/product_picker'); ?>?'+ $.param(params));
	});

	$(':input[name^="customer"]').first().trigger('input');

	// Comments

	$('#box-comments').on('input', 'textarea[name^="comments"][name$="[text]"]', function() {
		$(this).height('auto').height('calc(' + $(this).prop('scrollHeight') + 'px + 1em) ');
	}).trigger('input');


	$('#box-comments .add').on('click', function(e) {
		e.preventDefault();

		let __index__ = 0;
		while ($(':input[name^="comments[new_'+__index__+']"]').length) __index__++;

		let $output = $([
			'<div class="bubble local me">',
			'  <?php echo functions::form_input_hidden('comments[__index__][id]', ''); ?>',
			'  <?php echo functions::form_input_hidden('comments[__index__][author]', 'staff'); ?>',
			'  <?php echo functions::form_input_hidden('comments[__index__][created_at]', functions::datetime_format('datetime')); ?>',
			'  <?php echo functions::escape_js(functions::form_textarea('comments[__index__][text]', '')); ?>',
			'  <div class="date"><?php echo functions::datetime_format('datetime'); ?></div>',
			'  <div class="actions">',
			'    <label class="notify" title="<?php echo functions::escape_html(language::translate('title_notify', 'Notify')); ?>"><?php echo functions::escape_js(functions::form_checkbox('comments[__index__][notify]', [1, functions::draw_fonticon('icon-envelope')], true)); ?> </label>',
			'    <label class="private" title="<?php echo functions::escape_html(language::translate('title_hidden', 'Hidden')); ?>"><?php echo functions::escape_js(functions::form_checkbox('comments[__index__][hidden]', [1, functions::draw_fonticon('icon-eye-slash')], true)); ?></label>',
			'    <a class="btn btn-default btn-sm remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('icon-times'); ?></a>',
			'  </div>',
			'</div>'
		].join('\n')
			.replace('__index__', 'new_' + __index__)
		);

		$(this).before($output);
		$(this).closest('#box-comments .bubbles textarea:last-child').trigger('focus');
	});

	$('#box-comments').on('click', ':input[name$="[hidden]"]', function(e) {
		$(this).closest('.bubble').find(':input[name$="[notify]"]').prop('checked', false).trigger('change');
	});

	$('#box-comments').on('click', ':input[name$="[notify]"]', function(e) {
		$(this).closest('.bubble').find(':input[name$="[hidden]"]').prop('checked', false).trigger('change');
	});

	$('#box-comments').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('.bubble').remove();
	});

	$('#box-comments .bubbles').on('change', 'input[name^="comments"][name$="[hidden]"]', function(e) {
		if ($(this).is(':checked')) {
			$(this).closest('.bubble').addClass('semi-transparent');
		} else {
			$(this).closest('.bubble').removeClass('semi-transparent');
		}
	});

	// Tax Rates

	let tax_rates = [];

	function get_tax(tax_class_id) {
		$.each(tax_rates, function(i, tax_rate) {
			if (tax_class_id == i) return tax_rate;
		});
	}

	$('#customer-details').on('input', function() {
		$.ajax({
			url: '<?php echo document::ilink('tax/tax_rates.json'); ?>?' + $(':input[name^="customer\["]').serialize(),
			type: 'get',
			cache: true,
			async: false,
			dataType: 'json',
			success: function(data) {
				tax_rates = [];
				$.each(data, function(i, tax_rate) {
					tax_rates[tax_rate.tax_class_id] = tax_rate.rate;
				});
			},
		});
	});

	// Order Lines

	$('#order-lines').on('input change', ':input[name$="[quantity]"], :input[name$="[price]"], :input[name$="[tax_rate]"], :input[name$="[discount]"]', function() {

		let $row = $(this).closest('tr'),
			quantity = parseFloat($row.find(':input[name$="[quantity]"]').val() || 0),
			price = parseFloat($row.find(':input[name$="[price]"]').val() || 0),
			tax_rate = parseFloat($row.find(':input[name$="[tax_rate]"]').val() || 0),
			discount = parseFloat($row.find(':input[name$="[discount]"]').val() || 0),
			sum = quantity * (price - discount),
			sum_tax = (sum * tax_rate / 100),
			decimals = $('select[name="currency_code"] option:selected').data('decimals') || 0;

		$row.find(':input[name$="[sum]"]').val(sum.toFixed(decimals)).trigger('change');
		$row.find('.sum').text(sum.toMoney(false));

		$row.find(':input[name$="[tax]"]').val(sum_tax.toFixed(decimals)).trigger('change');
		$row.find('.sum_tax').text(sum_tax.toMoney(false));

		refresh_total();
	});

	$('#order-lines').on('click', '.edit', function() {

		let $row = $(this).closest('tr');
			type = $row.find(':input[name$="[type]"]').val();

		switch (type) {

			case 'product':
				$modal = $('#modal-edit-line-item');
				break;

			case 'custom':
				$modal = $('#modal-edit-line-item');
				break;
		}

			// Set origin row
		$modal.data('row', $row);

			// Set modal title
		$modal.find('h2').text("<?php echo functions::escape_js(language::translate('title_edit_line_item', 'Edit Line Item')); ?>");

			// Insert values into modal
		$.each($modal.find(':input'), function(i, element) {

			let field = $(element).attr('name');
			let value = $row.find(':input[name$="['+field+']"]').val();

			if ($modal.find(':input[name="'+field+'"]').attr('type') == 'number') {
				value = parseFloat(value || 0);
			}

			$modal.find(':input[name="'+field+'"]').val(value);
		});

		$.litebox($modal);
	});

	// Callback handler for product picker modal
	let selectProduct = function(product) {

		let params = {
			product_id: product.id,
			language_code: $('select[name="language_code"]').val(),
			currency_code: $('select[name="currency_code"]').val(),
			currency_value: $('input[name="currency_value"]').val(),
			customer: {
				id: $(':input[name="customer[id]"]').val(),
				tax_id: $('input[name="customer[tax_id]"]').val(),
				company: $('input[name="customer[company]"]').val(),
				country_code: $('select[name="customer[country_code]"]').val(),
				zone_code: $('select[name="customer[zone_code]"]').val(),
				city: $('select[name="customer[city]"]').val(),
				shipping_address: {
					company: $('input[name="shipping_address[company]"]').val(),
					country_code: $('select[name="shipping_address[country_code]"]').val(),
					zone_code: $('select[name="shipping_address[zone_code]"]').val(),
					city: $('select[name="shipping_address[city]"]').val(),
				}
			}
		};

		let url = '<?php echo document::ilink(__APP__.'/add_product'); ?>?' + $.param(params);

		$.get(url, function(content) {
			$('.litebox-modal').html(content);
		}, 'html');
	};

	$('#order-lines button[name="add"]').on('click', function() {

		$modal = $([

		].join('\n'));

		$.litebox('#modal-edit-line-item');

		let modal = $('.litebox.active'),
				row = $(this).closest('tr');

		$(modal).data('row', '');
	});

	$('#order-lines').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	// Edit Line Item Modal


	window.addItem = function(item) {
		$output.find('*[name$="[product_id]"]').val(item.product_id);
		$output.find('*[name$="[stock_item_id]"]').val(item.stock_item_id);
		$output.find('*[name$="[sku]"]').val(item.sku);
		$output.find('*[name$="[name]"]').val(item.name);
		$output.find('*[name$="[serial_number]"]').val(item.serial_number);
		$output.find('*[name$="[gtin]"]').val(item.gtin);
		$output.find('*[name$="[taric]"]').val(item.taric);
		$output.find('*[name$="[weight]"]').val(item.weight);
		$output.find('*[name$="[weight_unit]"]').val(item.weight_unit);
		$output.find('*[name$="[length]"]').val(item.length);
		$output.find('*[name$="[width]"]').val(item.width);
		$output.find('*[name$="[height]"]').val(item.height);
		$output.find('*[name$="[length_unit]"]').val(item.length_unit);
		$output.find('*[name$="[quantity]"]').val(item.quantity);
		$output.find('*[name$="[price]"]').val(item.price);
		$output.find('*[name$="[tax]"]').val(item.tax);
		$output.find('*[name$="[tax_rate]"]').val(item.tax_rate);
		$output.find('*[name$="[tax_class_id]"]').val(item.tax_class_id);
		$output.find('[data-type="currency"]').parent().find('.input-group-text').text($(':input[name="currency_code"]').val());
		$output.find('.weight').text(String(item.weight).trim('.0'));
		$output.find('.weight_unit').text(item.weight_unit);
		$output.find('.length').text(String(item.length).trim('.0'));
		$output.find('.width').text(String(item.width).trim('.0'));
		$output.find('.height').text(String(item.height).trim('.0'));
		$output.find('.length_unit').text(item.length_unit);
	};

	$('#modal-edit-line-item button[name="ok"]').on('click', function(e) {

		let $modal = $('.litebox.active');
		let $row = $(modal).data('row');

		if (!$row) {

			let __index__ = 0;
			while ($(':input[name^="items[new_'+__index__+']"]').length) __index__++;

			let $output = $([
				'  <tr class="item">',
				'    <td></td>',
				'    <td class="grabbable">' + item.name,
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][id]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][product_id]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][stock_item_id]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][name]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][description]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][data]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][serial_number]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][sku]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][gtin]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][taric]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][weight]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][weight_unit]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][length]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][width]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][height]', '')); ?>',
				'      <?php echo functions::escape_js(functions::form_input_hidden('items[__index__][length_unit]', '')); ?>',
				'    </td>',
				'    <td class="grabbable sku">'+ item.sku +'</td>',
				'    <td class="grabbable">',
				'      <span class="weight"></span> <span class="weight_unit"></span>',
				'    </td>',
				'    <td class="grabbable">',
				'      <span class="length"></span> x <span class="width"></span> x <span class="height"></span> <span class="length_unit"></span>',
				'    </td>',
				'    <td><?php echo functions::escape_js(functions::form_input_decimal('items[__index__][quantity]', '')); ?></td>',
				'    <td><?php echo functions::escape_js(functions::form_input_money('items[__index__][price]', $_POST['currency_code'], '')); ?></td>',
				'    <td><?php echo functions::escape_js(functions::form_input_money('items[__index__][discount]', $_POST['currency_code'], '')); ?></td>',
				'    <td class="sum"><?php echo currency::format(0, true, $_POST['currency_code'], $_POST['currency_value']); ?></td>',
				'    <td class="sum_tax"><?php echo currency::format(0, true, $_POST['currency_code'], $_POST['currency_value']); ?></td>',
				'    <td class="text-end">',
				'      <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('remove')); ?></a>',
				'    </td>',
				'    <td class="text-end">',
				'      <a class="btn btn-default btn-sm edit" href="#" title="<?php echo functions::escape_js(language::translate('title_edit', 'Edit'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('edit')); ?></a>',
				'    </td>',
				'  </tr>'
			].join('\n')
				.replace('__index__', 'new_' + __index__)
			);

			$row = $output;
			$('#order-lines tbody').append($output);
		}

		$.each($modal.find(':input'), function(i, $element) {
			let field = $element.attr('name');
			let value = $modal.find(':input[name="'+field+'"]').val();
			$row.find(':input[name$="['+field+']"]').val(value).trigger('keyup');
			$row.find('.'+field).text(value);
		});

		refresh_total();

		$.litebox.close();
	});

	// Order Total

	function refresh_total() {

		let subtotal = 0,
			discount = 0,
			fees = 0,
			tax = 0,
			total = 0;

		$('#order-lines tbody tr').each(function() {

			let final_price = parseFloat($(this).find(':input[name$="[price]"]').val() || 0) - parseFloat($(this).find(':input[name$="[discount]"]').val() || 0),
				sum = final_price * parseFloat($(this).find(':input[name$="[quantity]"]').val() || 0),
				sum_tax = sum * (parseFloat($(this).find(':input[name$="[tax_rate]"]').val() || 0) / 100);

				$(this).find('.sum').text(sum.toMoney());
				$(this).find('.sum_tax').text(sum_tax.toMoney());

			subtotal += sum;
			discount += parseFloat($(this).find(':input[name$="[quantity]"]').val() || 0) * parseFloat($(this).find(':input[name$="[discount]"]').val() || 0);
			tax += sum_tax;
		});

		total = subtotal + fees + tax;

		$('#subtotal .amount').text(subtotal.toMoney(false));
		$('#total-discount .amount').text(discount.toMoney(false));
		$('#total-fees .amount').text(fees.toMoney(false));
		$('#total-tax .amount').text(tax.toMoney(false));
		$('#order-total .amount').html(total.toMoney(true));
	}

/*
	$('body').on('input change', [
		'#order-lines :input[name$="[quantity]"]',
		'#order-lines :input[name$="[price]"]',
		'#order-lines :input[name$="[discount]"]',
		'#order-lines :input[name$="[tax_rate]"]',
		'#order-lines a.remove',
	].join(', '), function() {
		refresh_total();
	});
*/
</script>
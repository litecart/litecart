<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (!empty($_GET['customer_id'])) {
		$customer = new ent_customer($_GET['customer_id']);
	} else {
		$customer = new ent_customer();
	}

	if (!$_POST) {
		$_POST = $customer->data;
	}

	document::$title[] = !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_create_new_customer', 'Create New Customer');

	breadcrumbs::add(language::translate('title_customers', 'Customers'), document::ilink(__APP__.'/customers'));
	breadcrumbs::add(!empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_create_new_customer', 'Create New Customer'), document::ilink());

	if (isset($_POST['sign_in'])) {

		try {

			customer::load($_GET['customer_id']);

			session::$data['security.timestamp'] = time();
			session::regenerate_id();

			notices::add('success', strtr(language::translate('success_logged_in_as_user', 'You are now logged in as %firstname %lastname.'), [
				'%email' => customer::$data['email'],
				'%firstname' => customer::$data['firstname'],
				'%lastname' => customer::$data['lastname'],
			]));

			header('Location: '. document::ilink('f:'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['save'])) {

		try {

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."customers
				where id != ". (int)$customer->data['id'] ."
				and email like '". database::input($_POST['email']) ."'
				limit 1;"
			)->num_rows) {
				throw new Exception(language::translate('error_email_already_in_use_for_another_account', 'The email address is already in use for another account'));
			}

			if (empty($_POST['newsletter'])) {
				$_POST['newsletter'] = 0;
			}

			if (empty($_POST['company'])) {
				$_POST['company'] = '';
			}

			if (empty($_POST['tax_id'])) {
				$_POST['tax_id'] = '';
			}

			foreach ([
				'code',
				'status',
				'group_id',
				'email',
				'password',
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
				'newsletter',
				'notes',
				'different_shipping_address',
			] as $field) {
				if (isset($_POST[$field])) {
					$customer->data[$field] = $_POST[$field];
				}
			}

			foreach ([
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
				$customer->data['shipping_address'][$field] = !empty($_POST['shipping_address'][$field]) ? $_POST['shipping_address'][$field] : '';
			}

			$customer->save();

			if (!empty($_POST['new_password'])) {
				$customer->set_password($_POST['new_password']);
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/customers'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($customer->data['id'])) {
				throw new Exception(language::translate('error_must_provide_customer', 'You must provide a customer'));
			}

			$customer->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/customers'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($customer->data['id'])) {
		$orders = database::query(
			"select count(o.id) as total_count, sum(oi.total_sales) as total_sales
			from ". DB_TABLE_PREFIX ."orders o
			left join (
				select order_id, sum(price * quantity) as total_sales from ". DB_TABLE_PREFIX ."orders_items
				group by order_id
			) oi on (oi.order_id = o.id)
			where o.order_status_id in (
				select id from ". DB_TABLE_PREFIX ."order_statuses
				where is_sale
			)
			and (o.customer_id = ". (int)$customer->data['id'] ." or o.customer_email = '". database::input($customer->data['email']) ."');"
		)->fetch();
	}
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($customer->data['id']) ? language::translate('title_edit_customer', 'Edit Customer') : language::translate('title_create_new_customer', 'Create New Customer'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('customer_form', 'post', '', false, 'autocomplete="off"'); ?>

			<div class="grid" style="max-width: 960px;">

				<div class="col-md-8">

					<h3><?php echo language::translate('title_sign_in_and_security', 'Sign In and Security'); ?></h3>
					<?php if (!empty($customer->data['id'])) { ?>
					<label class="form-group">
						<?php echo functions::form_button('sign_in', ['true', language::translate('text_sign_in_as_customer', 'Sign in as customer')], 'submit', 'class="btn btn-default btn-block"'); ?>
					</label>
					<?php } ?>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_status', 'Status'); ?></div>
								<?php echo functions::form_toggle('status', 'e/d', (file_get_contents('php://input') != '') ? true : '1'); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_code', 'Code'); ?></div>
								<?php echo functions::form_input_text('code', true); ?>
							</label>
						</div>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_customer_group', 'Customer Group'); ?></div>
						<?php echo functions::form_select_customer_group('group_id', true); ?>
					</label>

					<div class="grid">
						<div class="col-md-8">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
								<?php echo functions::form_input_email('email', true); ?>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_newsletter', 'Newsletter'); ?></div>
								<?php echo functions::form_checkbox('newsletter', ['1', language::translate('title_subscribe', 'Subscribe')], true); ?>
							</label>
						</div>
					</div>

					<div class="grid">

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo !empty($customer->data['id']) ? language::translate('title_new_password', 'New Password') : language::translate('title_password', 'Password'); ?></div>
								<?php echo functions::form_input_password_unmaskable('new_password', '', 'autocomplete="new-password"'); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_last_login', 'Last Login'); ?></div>
								<div class="form-input" readonly><?php echo $customer->data['date_login'] ? language::strftime('datetime', $customer->data['date_login']) : '<em>'. language::translate('title_never', 'Never') .'</em>'; ?></div>
							</label>
						</div>
					</div>

					<?php if (!empty($customer->data['id'])) { ?>
					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_last_ip_address', 'Last IP Address'); ?></div>
								<?php echo functions::form_input_text('last_ip_address', true, 'readonly'); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_last_hostname', 'Last Hostname'); ?></div>
								<?php echo functions::form_input_text('last_hostname', true, 'readonly'); ?>
							</label>
						</div>
					</div>
					<?php } ?>

					<h3><?php echo language::translate('title_customer_details', 'Customer Details'); ?></h3>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_company', 'Company'); ?></div>
								<?php echo functions::form_input_text('company', true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_tax_id', 'Tax ID / VATIN'); ?></div>
								<?php echo functions::form_input_text('tax_id', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
								<?php echo functions::form_input_text('firstname', true); ?>
							</label>
						</div>
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
								<?php echo functions::form_input_text('lastname', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
								<?php echo functions::form_input_text('address1', true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
								<?php echo functions::form_input_text('address2', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
								<?php echo functions::form_input_text('postcode', true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
								<?php echo functions::form_input_text('city', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
								<?php echo functions::form_select_country('country_code', true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_zone', 'Zone'); ?></div>
								<?php echo functions::form_select_zone('zone_code', isset($_POST['country_code']) ? $_POST['country_code'] : '', true); ?>
							</label>
						</div>
					</div>

					<h3><?php echo functions::form_checkbox('different_shipping_address', ['1', language::translate('title_different_shipping_address', 'Different Shipping Address')], !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?></h3>

					<fieldset class="shipping-address"<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;" disabled' : ''; ?>>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_company', 'Company'); ?></div>
									<?php echo functions::form_input_text('shipping_address[company]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
									<?php echo functions::form_input_text('shipping_address[firstname]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
									<?php echo functions::form_input_text('shipping_address[lastname]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
									<?php echo functions::form_input_text('shipping_address[address1]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
									<?php echo functions::form_input_text('shipping_address[address2]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
									<?php echo functions::form_input_text('shipping_address[postcode]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
									<?php echo functions::form_input_text('shipping_address[city]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
									<?php echo functions::form_select_country('shipping_address[country_code]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
									<?php echo functions::form_select_zone(isset($_POST['shipping_address']['country_code']) ? $_POST['shipping_address']['country_code'] : $_POST['country_code'], 'shipping_address[zone_code]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_phone', 'Phone'); ?></div>
									<?php echo functions::form_input_phone('shipping_address[phone]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo language::translate('title_email', 'Email'); ?></div>
									<?php echo functions::form_input_email('shipping_address[email]', true); ?>
								</label>
							</div>
						</div>

					</fieldset>

					<div class="card-action">
						<?php echo functions::form_button_predefined('save'); ?>
						<?php if (!empty($customer->data['id'])) echo functions::form_button_predefined('delete'); ?>
						<?php echo functions::form_button_predefined('cancel'); ?>
					</div>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_notes', 'Notes'); ?></div>
						<?php echo functions::form_textarea('notes', true, 'style="height: 450px;"'); ?>
					</label>

					<?php if (!empty($customer->data['id'])) { ?>
					<table class="table data-table">
						<tbody>
							<tr>
								<td><?php echo language::translate('title_orders', 'Orders'); ?><br>
									<?php echo !empty($orders['total_count']) ? (int)$orders['total_count'] : '0'; ?>
								</td>
								<td><?php echo language::translate('title_total_sales', 'Total Sales'); ?><br>
									<?php echo currency::format(fallback($orders['total_sales'], 0), false, settings::get('store_currency_code')); ?>
								</td>
							</tr>
						</tbody>
					</table>
					<?php } ?>
				</div>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>

	// Init

	$('input[name="type"]').on('change', function() {
		if ($(this).val() == 'company') {
			$('.company-details :input').prop('disabled', false)
			$('.company-details').slideDown('fast')
		} else {
			$('.company-details :input').prop('disabled', true)
			$('.company-details').slideUp('fast')
		}
	}).first().trigger('change')

	if ($('select[name="country_code"]').find('option:selected').data('tax-id-format') != '') {
		$('input[name="tax_id"]').attr('pattern', $('select[name="country_code"]').find('option:selected').data('tax-id-format'))
	} else {
		$('input[name="tax_id"]').removeAttr('pattern')
	}

	if ($('select[name="country_code"]').find('option:selected').data('postcode-format') != '') {
		var postcode_format = $('select[name="country_code"]').find('option:selected').data('postcode-format')
		$('input[name="postcode"]').attr('pattern', postcode_format)
	} else {
		$('input[name="postcode"]').removeAttr('pattern')
	}

	if ($('select[name="country_code"]').find('option:selected').data('phone-code') != '') {
		$('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"]').find('option:selected').data('phone-code'))
	} else {
		$('input[name="phone"]').removeAttr('placeholder')
	}

	if (!$('select[name="zone_code"] option').length) {
		$('select[name="zone_code"]').closest('td').css('opacity', 0.15)
	}

// Init (Shipping address)

	$('input[name="different_shipping_address"]').on('change', function(e) {
		if (this.checked == true) {
			$('fieldset.shipping-address').prop('disabled', false).slideDown('fast')
		} else {
			$('fieldset.shipping-address').prop('disabled', true).slideUp('fast')
		}
	}).trigger('change')

	if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('tax-id-format') != '') {
		$('input[name="tax_id"]').attr('pattern', $('select[name="shipping_address[country_code]"]').find('option:selected').data('tax-id-format'))
	} else {
		$('input[name="tax_id"]').removeAttr('pattern')
	}

	if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('postcode-format') != '') {
		$('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"]').find('option:selected').data('postcode-format'))
		$('input[name="shipping_address[postcode]"]').prop('required', true)
		$('input[name="shipping_address[postcode]"]').closest('td').find('.required').show()
	} else {
		$('input[name="shipping_address[postcode]"]').removeAttr('pattern')
		$('input[name="shipping_address[postcode]"]').prop('required', false)
		$('input[name="shipping_address[postcode]"]').closest('td').find('.required').hide()
	}

	if ($('select[name="shipping_address[country_code]"]').find('option:selected').data('phone-code') != '') {
		$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $('select[name="shipping_address[country_code]"]').find('option:selected').data('phone-code'))
	} else {
		$('input[name="shipping_address[phone]"]').removeAttr('placeholder')
	}

	if (!$('select[name="shipping_address[zone_code]"] option').length) {
		$('select[name="shipping_address[zone_code]"]').closest('td').css('opacity', 0.15)
	}

// Get Address

	$('form[name="customer_form"]').on('change', ':input', function() {
		if ($(this).val() == '') return

		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $(this).closest('form').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: function(data) {
				if (data['alert']) {
					alert(data['alert'])
					return
				}
				$.each(data, function(key, value) {
					console.log(key +' '+ value)
					if ($('input[name="'+key+'"]').length && $('input[name="'+key+'"]').val() == '') $('input[name="'+key+'"]').val(data[key])
				})
			}
		})
	})

	// Get Address (Shipping address)

	$('form[name="customer_form"]').on('change', ':input', function() {

		if ($(this).val() == '') return

		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $(this).closest('form').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: function(data) {
				if (data['alert']) {
					alert(data['alert'])
					return
				}
				$.each(data, function(key, value) {
					if ($('input[name="shipping_address['+key+']"]').length && $('input[name="shipping_address['+key+']"]').val() == '') {
						$('input[name="shipping_address['+key+']"]').val(data[key])
					}
				})
			}
		})
	})

	// On change country

	$('select[name="country_code"]').on('change', function(e) {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="tax_id"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'))
		} else {
			$('input[name="tax_id"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="postcode"]').attr('pattern', $(this).find('option:selected').data('postcode-format'))
		} else {
			$('input[name="postcode"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="phone"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'))
		} else {
			$('input[name="phone"]').removeAttr('placeholder')
		}


		$.ajax({
			url: '<?php echo document::ilink('countries/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="zone_code"]').html('')
				if (data.length) {
					$('select[name="zone_code"]').prop('disabled', false)
					$.each(data, function(i, zone) {
						$('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>')
					})
				} else {
					$('select[name="zone_code"]').prop('disabled', true)
				}
			}
		})
	})

// On change country (Shipping address)

	$('select[name="shipping_address[country_code]"]').on('change', function(e) {

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="shipping_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'))
		} else {
			$('input[name="shipping_address[postcode]"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'))
		} else {
			$('input[name="shipping_address[phone]"]').removeAttr('placeholder')
		}


		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="shipping_address[zone_code]"]').html('')
				if (data.length) {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', false)
					$.each(data, function(i, zone) {
						$('select[name="shipping_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>')
					})
				} else {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', true)
				}
			}
		})
	})

	$('input[name="different_shipping_address"]').on('change', function(e) {
		if (this.checked == true) {
			$('#shipping-address').slideDown('fast')
		} else {
			$('#shipping-address').slideUp('fast')
		}
	})
</script>
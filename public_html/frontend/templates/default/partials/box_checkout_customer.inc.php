<section id="box-checkout-customer">

	<div class="card-body">

		<?php if (settings::get('accounts_enabled') && empty($shopping_cart->data['customer']['id'])) { ?>
		<div class="float-end">
			<a class="btn btn-outline" href="<?php echo document::href_ilink('login', ['redirect_url' => document::ilink('checkout/index')]) ?>" data-toggle="lightbox" data-seamless="true" data-require-window-width="768" data-seamless="true"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a>
		</div>
		<?php } ?>

		<h2 class="title"><?php echo language::translate('title_customer_details', 'Customer Details'); ?></h2>

		<?php if ($account_exists) { ?>
		<div class="alert alert-default">
			<?php echo functions::draw_fonticon('fa-info-circle'); ?> <?php echo language::translate('notice_existing_customer_account_will_be_used', 'We have an existing customer account that will be used for this order'); ?>
		</div>
		<?php } ?>

		<div class="address billing-address">

			<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
			<div class="form-group">
				<?php echo functions::form_toggle_buttons('billing_address[type]', ['individual' => language::translate('title_individual', 'Individual'), 'business' => language::translate('title_business', 'Business')], empty($_POST['billing_address']['type']) ? 'individual' : true); ?>
			</div>

			<div class="business-details" <?php if (empty($_POST['billing_address']['type']) || $_POST['billing_address']['type'] == 'individual') echo 'style="display: none;"'; ?>>
				<div class="row">
					<?php if (settings::get('customer_field_company')) { ?>
					<div class="form-group col-6">
						<label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
						<?php echo functions::form_input_text('billing_address[company]', true); ?>
					</div>
					<?php } ?>

					<?php if (settings::get('customer_field_tax_id')) { ?>
					<div class="form-group col-6">
						<label><?php echo language::translate('title_tax_id', 'Tax ID'); ?></label>
						<?php echo functions::form_input_text('billing_address[tax_id]', true); ?>
					</div>
					<?php } ?>
				</div>
			</div>
			<?php } ?>

			<div class="row">
				<div class="form-group col-6">
					<label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
					<?php echo functions::form_input_text('billing_address[firstname]', true, 'required'); ?>
				</div>

				<div class="form-group col-6">
					<label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
					<?php echo functions::form_input_text('billing_address[lastname]', true, 'required'); ?>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-6">
					<label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
					<?php echo functions::form_input_text('billing_address[address1]', true, 'required'); ?>
				</div>

				<div class="form-group col-6">
					<label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
					<?php echo functions::form_input_text('billing_address[address2]', true); ?>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-6">
					<label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
					<?php echo functions::form_input_text('billing_address[postcode]', true); ?>
				</div>

				<div class="form-group col-6">
					<label><?php echo language::translate('title_city', 'City'); ?></label>
					<?php echo functions::form_input_text('billing_address[city]', true); ?>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
					<label><?php echo language::translate('title_country', 'Country'); ?></label>
					<?php echo functions::form_select_country('billing_address[country_code]', true); ?>
				</div>

				<?php if (settings::get('customer_field_zone')) { ?>
				<div class="form-group col-6">
					<label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
					<?php echo functions::form_select_zone('billing_address[zone_code]', fallback($_POST['billing_address']['country_code']), true); ?>
				</div>
				<?php } ?>
			</div>

			<div class="row">
				<div class="form-group col-6">
					<label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
					<?php echo functions::form_input_email('billing_address[email]', true, 'required'. (!empty($shopping_cart->data['customer']['id']) ? ' readonly' : '')); ?>
				</div>

				<div class="form-group col-6">
					<label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
					<?php echo functions::form_input_phone('billing_address[phone]', true, 'required'); ?>
				</div>
			</div>
		</div>

		<?php if (!$subscribed_to_newsletter) { ?>
		<div class="form-group">
			<?php echo functions::form_checkbox('newsletter', ['1', language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.')], true); ?>
		</div>
		<?php } ?>

		<?php if (settings::get('customer_shipping_address')) { ?>
		<div class="address shipping-address">

			<h3><?php echo functions::form_checkbox('different_shipping_address', ['1', language::translate('title_different_shipping_address', 'Different Shipping Address')], !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?></h3>

			<fieldset<?php if (empty($_POST['different_shipping_address'])) echo ' style="display: none;" disabled'; ?>>

				<?php if (settings::get('customer_field_company')) { ?>
				<div class="row">
					<div class="form-group col-6">
					<label><?php echo language::translate('title_company_name', 'Company Name'); ?></label>
						<?php echo functions::form_input_text('shipping_address[company]', true); ?>
					</div>
				</div>
				<?php } ?>

				<div class="row">
					<div class="form-group col-6">
						<label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
						<?php echo functions::form_input_text('shipping_address[firstname]', true); ?>
					</div>

					<div class="form-group col-6">
						<label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
						<?php echo functions::form_input_text('shipping_address[lastname]', true); ?>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-6">
						<label><?php echo language::translate('title_address1', 'Address 1'); ?></label>
						<?php echo functions::form_input_text('shipping_address[address1]', true); ?>
					</div>

					<div class="form-group col-6">
						<label><?php echo language::translate('title_address2', 'Address 2'); ?></label>
						<?php echo functions::form_input_text('shipping_address[address2]', true); ?>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-6">
						<label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
						<?php echo functions::form_input_text('shipping_address[postcode]', true); ?>
					</div>

					<div class="form-group col-6">
						<label><?php echo language::translate('title_city', 'City'); ?></label>
						<?php echo functions::form_input_text('shipping_address[city]', true); ?>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
						<label><?php echo language::translate('title_country', 'Country'); ?></label>
						<?php echo functions::form_select_country('shipping_address[country_code]', true); ?>
					</div>

					<?php if (settings::get('customer_field_zone')) { ?>
					<div class="form-group col-6">
						<label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
						<?php echo functions::form_select_zone('shipping_address[zone_code]', fallback($_POST['shipping_address']['country_code'], $_POST['billing_address']['country_code']), true); ?>
					</div>
					<?php } ?>
				</div>

				<div class="row">
					<div class="form-group col-6">
						<label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
						<?php echo functions::form_input_phone('shipping_address[phone]', true); ?>
					</div>
				</div>

			</fieldset>
		</div>
		<?php } ?>

		<?php if (settings::get('accounts_enabled') && empty($shopping_cart->data['customer']['id'])) { ?>

		<?php if (!empty(customer::$data['id'])) { ?>
		<div class="form-group">
			<?php echo functions::form_checkbox('save_to_account', ['1', language::translate('title_save_details_to_my_account', 'Save details to my account')], true, 'style="margin: 0px;"'); ?>
		</div>
		<?php } ?>

		<div class="account">

			<?php if (!$account_exists) { ?>
			<h3><?php echo functions::form_checkbox('create_account', ['1', language::translate('title_create_account', 'Create Account')], (!empty($_POST['customer']['create_account']) || settings::get('register_guests')) ? '1' : '', 'style="margin: 0px;"' . (settings::get('register_guests') ? ' disabled' : '')); ?></h3>
			<?php if (settings::get('register_guests')) echo functions::form_input_hidden('create_account', '1'); ?>

			<fieldset<?php if (empty($_POST['customer']['create_account'])) echo ' style="display: none;" disabled'; ?>>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label><?php echo language::translate('title_desired_password', 'Desired Password'); ?></label>
							<?php echo functions::form_input_password('password', '', 'autocomplete="new-password"'); ?>
						</div>
					</div>

					<div class="col-sm-6">
						<div class="form-group">
							<label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
							<?php echo functions::form_input_password('confirmed_password', '', 'autocomplete="off"'); ?>
						</div>
					</div>
				</div>

			</fieldset>
			<?php } ?>
		</div>
		<?php } ?>

		<div>
			<button class="btn btn-block btn-default" name="save_customer_details" type="submit" disabled><?php echo language::translate('title_save_changes', 'Save Changes'); ?></button>
		</div>
	</div>
</section>

<script>
	<?php if (!empty(notices::$data['errors'])) { ?>
	alert("<?php echo functions::escape_js(notices::$data['errors'][0]); notices::$data['errors'] = []; ?>");
	<?php } ?>

	$('input[name="billing_address[type]"]').change(function(){
		if ($(this).val() == 'business') {
			$('.business-details :input').prop('disabled', false);
			$('.business-details').slideDown('fast');
		} else {
			$('.business-details :input').prop('disabled', true);
			$('.business-details').slideUp('fast');
		}
	}).first().trigger('change');

	if ($('select[name="billing_address[country_code]"] option:selected').data('tax-id-format')) {
		$('input[name="billing_address[tax_id]"]').attr('pattern', $('select[name="billing_address[country_code]"] option:selected').data('tax-id-format'));
	} else {
		$('input[name="billing_address[tax_id]"]').removeAttr('pattern');
	}

	if ($('select[name="billing_address[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="billing_address[postcode]"]').attr('pattern', $('select[name="billing_address[country_code]"] option:selected').data('postcode-format'));
	} else {
		$('input[name="billing_address[postcode]"]').removeAttr('pattern');
	}

	if ($('select[name="billing_address[country_code]"] option:selected').data('phone-code')) {
		$('input[name="billing_address[phone]"]').attr('placeholder', '+' + $('select[name="billing_address[country_code]"] option:selected').data('phone-code'));
	} else {
		$('input[name="billing_address[phone]"]').removeAttr('placeholder');
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

	$('input[name="create_account"]:checkbox').trigger('change');

	// Toggles

	$('#box-checkout-customer input[name="different_shipping_address"]').on('change', function(e){
		if (this.checked == true) {
			$('#box-checkout-customer .shipping-address fieldset').prop('disabled', false).slideDown('fast');
		} else {
			$('#box-checkout-customer .shipping-address fieldset').prop('disabled', true).slideUp('fast');
		}
	});

	$('#box-checkout-customer input[name="create_account"]').on('change', function(){
		if (this.checked == true) {
			$('#box-checkout-customer .account fieldset').prop('disabled', false).slideDown('fast');
		} else {
			$('#box-checkout-customer .account fieldset').prop('disabled', true).slideUp('fast');
		}
	});

	// Get Address

	$('#box-checkout-customer .billing-address :input').on('change', function() {
		if ($(this).val() == '') return;
		console.log('Get address (Trigger: '+ $(this).attr('name') +')');
		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $('.billing-address :input').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: function(data) {
				if (data['alert']) alert(data['alert']);
				$.each(data, function(key, value) {
					if ($('.billing-address :input[name="billing_address['+key+']"]').length && $('.billing-address :input[name="billing_address['+key+']"]').val() == '') {
						$('.billing-address :input[name="billing_address['+key+']"]').val(value).trigger('input');
					}
				});
			},
		});
	});

	$('#box-checkout-customer .shipping-address :input').on('change', function() {
		if ($(this).val() == '') return;
		console.log('Get address (Trigger: '+ $(this).attr('name') +')');
		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $('.shipping-address :input').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: function(data) {
				if (data['alert']) alert(data['alert']);
				$.each(data, function(key, value) {
					if ($('.shipping-address :input[name="shipping_address['+key+']"]').length && $('.shipping-address :input[name="shipping_address['+key+']"]').val() == '') {
						$('.shipping-address :input[name="shipping_address['+key+']"]').val(value).trigger('input');
					}
				});
			},
		});
	});

	// Fields

	$('#box-checkout-customer select[name="billing_address[country_code]"]').on('input', function(e) {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="billing_address[tax_id]"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
		} else {
			$('input[name="billing_address[tax_id]"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="billing_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
		} else {
			$('input[name="billing_address[postcode]"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="billing_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
		} else {
			$('input[name="billing_address[phone]"]').removeAttr('placeholder');
		}

		<?php if (settings::get('customer_field_zone')) { ?>
		$('body').css('cursor', 'wait');
		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="billing_address[zone_code]"]').html('');
				if (data.length) {
					$('select[name="billing_address[zone_code]"]').prop('disabled', false);
					$.each(data, function(i, zone) {
						$('select[name="billing_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="billing_address[zone_code]"]').prop('disabled', true);
				}
			},
			complete: function() {
				$('body').css('cursor', 'auto');
			}
		});
		<?php } ?>
	});

	$('#box-checkout-customer select[name="shipping_address[country_code]"]').on('input', function(e) {

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

		<?php if (settings::get('customer_field_zone')) { ?>
		$('body').css('cursor', 'wait');
		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: false,
			dataType: 'json',
			success: function(data) {
				$('select[name="shipping_address[zone_code]"]').html('');
				if (data.length) {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', false);
					$.each(data, function(i, zone) {
						$('select[name="shipping_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', true);
				}
			},
			complete: function() {
				$('body').css('cursor', 'auto');
			}
		});
		<?php } ?>
	});

	// Checksum

	$('#box-checkout-customer').data('checksum', $('#box-checkout-customer :input').serialize());

	$('#box-checkout-customer :input').on('input change', function(e) {
		if ($('#box-checkout-customer :input').serialize() != $('#box-checkout-customer').data('checksum')) {
			$('#box-checkout-customer').prop('changed', true);
			$('#box-checkout-customer button[name="save_customer_details"]').prop('disabled', false);
		} else {
			$('#box-checkout-customer').prop('changed', false);
			$('#box-checkout-customer button[name="save_customer_details"]').prop('disabled', true);
		}
	});

	// Prevent losing form focus when clicking the label of a checkbox
	$('#box-checkout-customer .form-check').click(function(e){
		$(this).find(':checkbox').trigger('focusin').focus();
	});

	// Auto-Save

	let timerSubmitCustomer;

	$('#box-checkout-customer').on('focusout', function() {
		timerSubmitCustomer = setTimeout(function() {
			if ($(this).not(':focus')) {
				if ($('#box-checkout-customer').prop('changed')) {

					console.log('Autosaving customer details');

					let formdata = $('#box-checkout-customer :input').serialize() + '&autosave=true';

					$('#box-checkout').trigger('update', [{component: 'customer', data: formdata, refresh: true}])
														.trigger('update', [{component: 'shipping', refresh: true}])
														.trigger('update', [{component: 'payment', refresh: true}])
														.trigger('update', [{component: 'summary'}]);

					$('#box-checkout-customer').data('checksum', $('#box-checkout-customer :input').serialize());
					$('#box-checkout-customer :input').first().trigger('input');
				}
			}
		}, 200);
	});

	$('#box-checkout-customer').on('focusin', function() {
		clearTimeout(timerSubmitCustomer);
	});

	// Process Data

	$('#box-checkout-customer button[name="save_customer_details"]').click(function(e){
		e.preventDefault();

		let formdata = $('#box-checkout-customer :input').serialize() + '&save_customer_details=true';

		$('#box-checkout').trigger('update', [{component: 'customer', data: formdata, refresh: true}])
											.trigger('update', [{component: 'shipping', refresh: true}])
											.trigger('update', [{component: 'payment', refresh: true}])
											.trigger('update', [{component: 'summary'}]);

		$('#box-checkout-customer').data('checksum', $('#box-checkout-customer :input').serialize());
		$('#box-checkout-customer :input').first().trigger('input');
	});

</script>
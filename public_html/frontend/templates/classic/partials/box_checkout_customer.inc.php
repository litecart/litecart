<section id="box-checkout-customer" class="box">

	<?php if (settings::get('accounts_enabled') && empty(customer::$data['id'])) { ?>
	<div style="float:right">
		<a href="<?php echo document::ilink('account/sign_in', ['redirect_url' => document::ilink('checkout/index')]) ?>" data-toggle="lightbox" data-require-window-width="768"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a>
	</div>
	<?php } ?>

	<h2 class="title"><?php echo language::translate('title_customer_details', 'Customer Details'); ?></h2>

	<div class="address billing-address">

		<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
		<div class="grid">
			<?php if (settings::get('customer_field_company')) { ?>
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_company_name', 'Company Name'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</div>
					<?php echo functions::form_input_text('billing_address[company]', true); ?>
				</label>
			</div>
			<?php } ?>

			<?php if (settings::get('customer_field_tax_id')) { ?>
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_tax_id', 'Tax ID'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</div>
					<?php echo functions::form_input_text('billing_address[tax_id]', true); ?>
				</label>
			</div>
			<?php } ?>
		</div>
		<?php } ?>

		<div class="grid">
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
					<?php echo functions::form_input_text('billing_address[firstname]', true, 'required'); ?>
				</label>
			</div>

			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
					<?php echo functions::form_input_text('billing_address[lastname]', true, 'required'); ?>
				</label>
			</div>
		</div>

		<div class="grid">
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
					<?php echo functions::form_input_text('billing_address[address1]', true, 'required'); ?>
				</label>
			</div>

			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
					<?php echo functions::form_input_text('billing_address[address2]', true); ?>
				</label>
			</div>
		</div>

		<div class="grid">
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
					<?php echo functions::form_input_text('billing_address[postcode]', true); ?>
				</label>
			</div>

			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
					<?php echo functions::form_input_text('billing_address[city]', true); ?>
				</label>
			</div>
		</div>

		<div class="grid">
			<div class="col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
					<?php echo functions::form_select_country('billing_address[country_code]', true); ?>
				</label>
			</div>

			<?php if (settings::get('customer_field_zone')) { ?>
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
					<?php echo functions::form_select_zone('billing_address[zone_code]', fallback($_POST['country_code']), true); ?>
				</label>
			</div>
			<?php } ?>
		</div>

		<div class="grid">
			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
					<?php echo functions::form_input_email('customer[email]', true, 'required'. (!empty(customer::$data['id']) ? ' readonly' : '')); ?>
				</label>
			</div>

			<div class="col-6">
				<label class="form-group">
					<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
					<?php echo functions::form_input_phone('billing_address[phone]', true, 'required'); ?>
				</label>
			</div>
		</div>
	</div>

	<div class="address shipping-address">

		<h3><?php echo functions::form_checkbox('different_shipping_address', '1', !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?> <?php echo language::translate('title_different_shipping_address', 'Different Shipping Address'); ?></h3>

		<fieldset<?php if (empty($_POST['different_shipping_address'])) echo ' style="display: none;" disabled'; ?>>

			<?php if (settings::get('customer_field_company')) { ?>
			<div class="grid">
				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_company_name', 'Company Name'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</div>
						<?php echo functions::form_input_text('shipping_address[company]', true); ?>
					</label>
				</div>
			</div>
			<?php } ?>

			<div class="grid">
				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
						<?php echo functions::form_input_text('shipping_address[firstname]', true); ?>
					</label>
				</div>

				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
						<?php echo functions::form_input_text('shipping_address[lastname]', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
						<?php echo functions::form_input_text('shipping_address[address1]', true); ?>
					</label>
				</div>

				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
						<?php echo functions::form_input_text('shipping_address[address2]', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
						<?php echo functions::form_input_text('shipping_address[postcode]', true); ?>
					</label>
				</div>

				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
						<?php echo functions::form_input_text('shipping_address[city]', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
						<?php echo functions::form_select_country('shipping_address[country_code]', true); ?>
					</label>
				</div>

				<?php if (settings::get('customer_field_zone')) { ?>
				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
						<?php echo functions::form_select_zone('shipping_address[zone_code]', fallback($_POST['shipping_address']['country_code'], $_POST['country_code']), true); ?>
					</label>
				</div>
				<?php } ?>
			</div>

			<div class="grid">
				<div class="col-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
						<?php echo functions::form_input_phone('shipping_address[phone]', true); ?>
					</label>
				</div>
			</div>

		</fieldset>
	</div>

	<?php if (!$subscribed_to_newsletter) { ?>
	<div class="form-group">
		<?php echo functions::form_checkbox('newsletter', ['1', language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.')], true); ?>
	</div>
	<?php } ?>

	<?php if (settings::get('accounts_enabled') && empty(customer::$data['id'])) { ?>

	<?php if (!empty(customer::$data['id'])) { ?>
	<div class="form-group">
		<?php echo functions::form_checkbox('save_to_account', ['1', language::translate('title_save_details_to_my_account', 'Save details to my account')], true, 'style="margin: 0px;"'); ?>
	</div>
	<?php } ?>

	<div class="account">

		<?php if (!empty($account_exists)) { ?>

		<div class="alert alert-info">
			<?php echo functions::draw_fonticon('icon-info'); ?> <?php echo language::translate('notice_existing_customer_account_will_be_used', 'We found an existing customer account that will be used for this order'); ?>
		</div>

		<?php } else { ?>

		<h3><?php echo functions::form_checkbox('sign_up', ['1', language::translate('title_sign_up', 'Sign Up')], true, 'style="margin: 0px;"'); ?></h3>

		<fieldset<?php if (empty($_POST['sign_up'])) echo ' style="display: none;" disabled'; ?>>

			<div class="grid">
				<div class="col-sm-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_desired_password', 'Desired Password'); ?></div>
						<?php echo functions::form_input_password('password', '', 'autocomplete="new-password"'); ?>
					</label>
				</div>

				<div class="col-sm-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></div>
						<?php echo functions::form_input_password('confirmed_password', '', 'autocomplete="off"'); ?>
					</label>
				</div>
			</div>

		</fieldset>
		<?php } ?>
	</div>
	<?php } ?>

	<div>
		<button class="btn btn-block btn-default" name="save_customer_details" type="submit" disabled><?php echo language::translate('title_save_changes', 'Save Changes'); ?></button>
	</div>

</section>

<script>
	<?php if (!empty(notices::$data['errors'])) { ?>
	alert("<?php echo functions::escape_js(notices::$data['errors'][0]); notices::$data['errors'] = []; ?>")
	<?php } ?>

	// Initiate fields

	if ($('select[name="billing_address[country_code]"] option:selected').data('tax-id-format')) {
		$('input[name="billing_address[tax_id]"]').attr('pattern', $('select[name="billing_address[country_code]"] option:selected').data('tax-id-format'))
	} else {
		$('input[name="billing_address[tax_id]"]').removeAttr('pattern')
	}

	if ($('select[name="billing_address[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="billing_address[postcode]"]').attr('pattern', $('select[name="billing_address[country_code]"] option:selected').data('postcode-format'))
	} else {
		$('input[name="billing_address[postcode]"]').removeAttr('pattern')
	}

	if ($('select[name="billing_address[country_code]"] option:selected').data('phone-code')) {
		$('input[name="billing_address[phone]"]').attr('placeholder', '+' + $('select[name="billing_address[country_code]"] option:selected').data('phone-code'))
	} else {
		$('input[name="billing_address[phone]"]').removeAttr('placeholder')
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"] option:selected').data('postcode-format'))
	} else {
		$('input[name="shipping_address[postcode]"]').removeAttr('pattern')
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('phone-code')) {
		$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $('select[name="shipping_address[country_code]"] option:selected').data('phone-code'))
	} else {
		$('input[name="shipping_address[phone]"]').removeAttr('placeholder')
	}

	$('input[name="sign_up"][type="checkbox"]').trigger('change')

	window.customer_form_changed = false
	window.customer_form_checksum = $('#box-checkout-customer :input').serialize()

	// Customer Form: Toggles

	$('#box-checkout-customer input[name="different_shipping_address"]').on('change', function(e) {
		if (this.checked == true) {
			$('#box-checkout-customer .shipping-address fieldset').removeAttr('disabled').slideDown('fast')
		} else {
			$('#box-checkout-customer .shipping-address fieldset').attr('disabled', 'disabled').slideUp('fast')
		}
	})

	$('#box-checkout-customer input[name="sign_up"]').on('change', function() {
		if (this.checked == true) {
			$('#box-checkout-customer .account fieldset').removeAttr('disabled').slideDown('fast')
		} else {
			$('#box-checkout-customer .account fieldset').attr('disabled', 'disabled').slideUp('fast')
		}
	})

	// Customer Form: Get Address

	$('#box-checkout-customer .billing-address :input').on('change', function() {
		if ($(this).val() == '') return
		if (console) console.log('Get address (Trigger: '+ $(this).attr('name') +')')
		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $('.billing-address :input').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: function(data) {
				if (data['alert']) alert(data['alert'])
				$.each(data, function(key, value) {
					if ($('.billing-address :input[name="billing_address['+key+']"]').length && $('.billing-address :input[name="billing_address['+key+']"]').val() == '') {
						$('.billing-address :input[name="billing_address['+key+']"]').val(value)
					}
				})
			},
		})
	})

	// Customer Form: Fields

	$('#box-checkout-customer select[name="billing_address[country_code]"]').on('change', function() {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="billing_address[tax_id]"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'))
		} else {
			$('input[name="billing_address[tax_id]"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="billing_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'))
		} else {
			$('input[name="billing_address[postcode]"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="billing_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'))
		} else {
			$('input[name="billing_address[phone]"]').removeAttr('placeholder')
		}

		<?php if (settings::get('customer_field_zone')) { ?>

		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="billing_address[zone_code]"]').html('')
				if (data.length) {
					$('select[name="billing_address[zone_code]"]').prop('disabled', false)
					$.each(data, function(i, zone) {
						$('select[name="billing_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>')
					})
				} else {
					$('select[name="billing_address[zone_code]"]').prop('disabled', true)
				}
			}
		})
		<?php } ?>
	})

	$('#box-checkout-customer select[name="shipping_address[country_code]"]').on('change', function() {

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

		<?php if (settings::get('customer_field_zone')) { ?>

		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: false,
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
		<?php } ?>
	})

	// Customer Form: Checksum

	let customer_form_changed = false
	let customer_form_checksum = $('#box-checkout-customer :input').serialize()
	$('#box-checkout-customer').on('input change', function() {
		if ($('#box-checkout-customer :input').serialize() != customer_form_checksum) {
			if (customer_form_checksum == null) return
			customer_form_changed = true
			$('#box-checkout-customer button[name="save_customer_details"]').removeAttr('disabled')
		} else {
			customer_form_changed = false
			$('#box-checkout-customer button[name="save_customer_details"]').attr('disabled', 'disabled')
		}
	})

	// Customer Form: Auto-Save

	let timerSubmitCustomer
	$('#box-checkout-customer').on('focusout', function() {
		timerSubmitCustomer = setTimeout(
			function() {
				if (!$(this).is(':focus')) {
					if (!customer_form_changed) return
					console.log('Autosaving customer details')
					let data = $('#box-checkout-customer :input').serialize()
					queueUpdateTask('customer', data, true)
					queueUpdateTask('cart', null, true)
					queueUpdateTask('shipping', true, true)
					queueUpdateTask('payment', true, true)
					queueUpdateTask('summary', null, true)
				}
			}, 50
		)
	})

	$('#box-checkout-customer').on('focusin', function() {
		clearTimeout(timerSubmitCustomer)
	})
</script>
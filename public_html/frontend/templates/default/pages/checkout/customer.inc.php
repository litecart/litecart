<style>
.form-check a {
	text-decoration: underline;
}
</style>

<main class="container">

	{{notices}}

	<section id="box-checkout-customer" class="card" style="max-width: 720px; margin: 0 auto;">

		<div class="card-header">

			<?php if (settings::get('accounts_enabled') && !customer::check_login()) { ?>
			<div class="float-end">
				<a class="btn btn-default" href="<?php echo document::ilink('account/sign_in', ['redirect_url' => document::ilink('checkout')]) ?>#box-login" data-toggle="lightbox" data-require-window-width="768" data-width="420px" data-seamless="true">
					<?php echo t('title_sign_in', 'Sign In'); ?>
				</a>
			</div>
			<?php } ?>

			<h2 class="card-title">
				<?php echo t('title_customer_details', 'Customer Details'); ?>
			</h2>
		</div>

		<div class="card-body">

			<?php echo functions::form_begin('customer_form', 'post'); ?>

				<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
				<div class="grid">
					<div class="col-6">
						<div class="form-group">
							<div class="form-label"><?php echo t('title_customer_type', 'Customer Type'); ?></div>
							<?php echo functions::form_toggle('customer[type]', ['business' => t('title_business', 'Business'), 'individual' => t('title_individual', 'Individual')], true); ?>
						</div>
					</div>
				</div>
				<?php } ?>

				<div id="business-details"<?php echo (isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? ' style="display: none;"' : ''; ?>>
					<div class="grid">
						<?php if (settings::get('customer_field_company')) { ?>
						<div class="col-sm-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_company_name', 'Company Name'); ?></div>
								<?php echo functions::form_input_text('customer[company]', true, 'required' . ((isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? ' disabled' : '')); ?>
							</label>
						</div>
						<?php } ?>

						<?php if (settings::get('customer_field_tax_id')) { ?>
						<div class="col-sm-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_tax_id', 'Tax ID'); ?> / <?php echo t('title_vat_number', 'VAT Number'); ?></div>
								<?php echo functions::form_input_text('customer[tax_id]', true, (isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? 'disabled' : ''); ?>
							</label>
						</div>
						<?php } ?>
					</div>
				</div>

				<div class="grid">
					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
							<?php echo functions::form_input_text('customer[firstname]', true, 'required'); ?>
						</label>
					</div>

					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
							<?php echo functions::form_input_text('customer[lastname]', true, 'required'); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_address1', 'Address 1'); ?></div>
							<?php echo functions::form_input_text('customer[address1]', true, 'required'); ?>
						</label>
					</div>

					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_address2', 'Address 2'); ?></div>
							<?php echo functions::form_input_text('customer[address2]', true); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
							<?php echo functions::form_input_text('customer[postcode]', true); ?>
						</label>
					</div>

					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_city', 'City'); ?></div>
							<?php echo functions::form_input_text('customer[city]', true); ?>
						</label>
					</div>
				</div>

				<div class="grid">
					<div class="col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
							<?php echo functions::form_select_country('customer[country_code]', true); ?>
						</label>
					</div>

					<?php if (settings::get('customer_field_zone')) { ?>
					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
							<?php echo functions::form_select_zone('customer[zone_code]', fallback($_POST['country_code'], customer::$data['country_code'], settings::get('store_country_code')), true); ?>
						</label>
					</div>
					<?php } ?>
				</div>

				<div class="grid">
					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
							<?php echo functions::form_input_email('customer[email]', true, 'required'. (customer::check_login() ? ' readonly' : '')); ?>
						</label>
					</div>

					<div class="col-sm-6">
						<label class="form-group">
							<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
							<?php echo functions::form_input_phone('customer[phone]', true, 'required'); ?>
						</label>
					</div>
				</div>

				<div class="address shipping-address">

					<h3><?php echo functions::form_checkbox('different_shipping_address', ['1', t('text_use_a_different_address_for_shipping', 'Use a different address for shipping')], !empty($_POST['different_shipping_address']) ? '1' : '', 'style="margin: 0px;"'); ?></h3>

					<fieldset<?php echo (empty($_POST['different_shipping_address'])) ? ' style="display: none;" disabled' : ''; ?>>

						<?php if (settings::get('customer_field_company')) { ?>
						<div class="form-grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_business_name', 'Business Name'); ?> (<?php echo t('text_or_leave_blank', 'Or leave blank'); ?>)</div>
									<?php echo functions::form_input_text('shipping_address[company]', true); ?>
								</label>
							</div>
						</div>
						<?php } ?>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
									<?php echo functions::form_input_text('shipping_address[firstname]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
									<?php echo functions::form_input_text('shipping_address[lastname]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_address1', 'Address 1'); ?></div>
									<?php echo functions::form_input_text('shipping_address[address1]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_address2', 'Address 2'); ?></div>
									<?php echo functions::form_input_text('shipping_address[address2]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
									<?php echo functions::form_input_text('shipping_address[postcode]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_city', 'City'); ?></div>
									<?php echo functions::form_input_text('shipping_address[city]', true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
									<?php echo functions::form_select_country('shipping_address[country_code]', true); ?>
								</label>
							</div>

							<?php if (settings::get('customer_field_zone')) { ?>
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
									<?php echo functions::form_select_zone('shipping_address[zone_code]', fallback($_POST['shipping_address']['country_code'], customer::$data['shipping_address']['country_code'], $_POST['country_code'], customer::$data['country_code'], settings::get('store_country_code')), true); ?>
								</label>
							</div>
							<?php } ?>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
									<?php echo functions::form_input_email('shipping_address[email]', true); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
									<?php echo functions::form_input_phone('shipping_address[phone]', true); ?>
								</label>
							</div>
						</div>

					</fieldset>
				</div>

				<?php if (settings::get('accounts_enabled') && !customer::check_login()) { ?>
				<div class="account">

					<?php if (!empty($account_exists)) { ?>

					<div class="notices">
						<div class="notice notice-default">
							<?php echo functions::draw_fonticon('icon-info'); ?> <?php echo t('notice_sign_in_to_connect_purchase_to_account', 'Sign in to connect this purchase to your account'); ?>
						</div>
					</div>

					<?php } else { ?>

					<h3><?php echo functions::form_checkbox('sign_up', ['1', t('text_create_an_account', 'Create an account')], !empty($_POST['sign_up']) ? '1' : '', 'style="margin: 0px;"'); ?></h3>

					<fieldset<?php echo (empty($_POST['sign_up'])) ? ' style="display: none;" disabled' : ''; ?>>

						<div class="form-grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_desired_password', 'Desired Password'); ?></div>
									<?php echo functions::form_input_password('password', '', 'autocomplete="new-password"'); ?>
								</label>
							</div>

							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_confirm_password', 'Confirm Password'); ?></div>
									<?php echo functions::form_input_password('confirmed_password', '', 'autocomplete="off"'); ?>
								</label>
							</div>
						</div>

					</fieldset>
					<?php } ?>
				</div>
				<?php } ?>

				<div class="form-group">
					<?php echo functions::form_checkbox('newsletter', ['1', t('consent_newsletter', 'I would like to be notified occasionally via email when there are new products or campaigns.')], true); ?>

					<?php if ($privacy_policy) { ?>
					<?php echo functions::form_checkbox('privacy_policy_consent', ['1', t('consent_privacy_policy', 'I have read and I consent to the terms and policy for privacy data management.')], true, 'required'); ?>
					<?php } ?>
				</div>

				<div>
					<button class="btn btn-lg btn-default btn-block" name="save" value="true" type="submit">
						<?php echo t('title_save_and_continue', 'Save and Continue'); ?>
					</button>
				</div>

			<?php echo functions::form_end(); ?>

		</div>
	</section>

</main>

<script>
	<?php if (!empty(notices::$data['errors'])) { ?>
	alert("<?php echo functions::escape_js(notices::$data['errors'][0]); notices::$data['errors'] = []; ?>");
	<?php } ?>

	// Initiate

	if ($('select[name="country_code"] option:selected').data('tax-id-format')) {
		$('input[name="tax_id"]').attr('pattern', $('select[name="country_code"] option:selected').data('tax-id-format'));
	} else {
		$('input[name="tax_id"]').removeAttr('pattern');
	}

	if ($('select[name="country_code"] option:selected').data('postcode-format')) {
		$('input[name="postcode"]').attr('pattern', $('select[name="country_code"] option:selected').data('postcode-format'));
	} else {
		$('input[name="postcode"]').removeAttr('pattern');
	}

	if ($('select[name="country_code"] option:selected').data('phone-code')) {
		$('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"] option:selected').data('phone-code'));
	} else {
		$('input[name="phone"]').removeAttr('placeholder');
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

	$('input[name="sign_up"][type="checkbox"]').trigger('change');

	// Toggle customer type
	$('input[name="customer[type]"]').on('change', function() {
		if ($(this).val() == 'business') {
			$('#business-details :input').prop('disabled', false);
			$('#business-details').slideDown('fast');
		} else {
			$('#business-details :input').prop('disabled', true);
			$('#business-details').slideUp('fast');
		}
	});

	// Toggle shipping address
	$('input[name="different_shipping_address"]').on('change', function(e) {
		if (this.checked == true) {
			$('.shipping-address fieldset').prop('disabled', false).slideDown('fast');
		} else {
			$('.shipping-address fieldset').prop('disabled', true).slideUp('fast');
		}
	});

	$('input[name="sign_up"]').on('change', function() {
		if (this.checked == true) {
			$('.account fieldset').prop('disabled', false).slideDown('fast');
		} else {
			$('.account fieldset').prop('disabled', true).slideUp('fast');
		}
	});

	// Get Address
	$('.billing-address :input').on('change', function() {
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
					if ($('.billing-address :input[name$="['+key+']"]').length && $('.billing-address *[name="'+key+'"]').val() == '') {
						$('.billing-address :input[name$="['+key+']"]').val(value).trigger('input');
					}
				});
			},
		});
	});

	// On Change Country
	$('select[name="country_code"]').on('input', function(e) {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="tax_id"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'));
		} else {
			$('input[name="tax_id"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="postcode"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
		} else {
			$('input[name="postcode"]').removeAttr('pattern');
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="phone"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'));
		} else {
			$('input[name="phone"]').removeAttr('placeholder');
		}

		<?php if (settings::get('customer_field_zone')) { ?>
		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="zone_code"]').html('');
				if (data.length) {
					$('select[name="zone_code"]').prop('disabled', false);
					$.each(data, function(i, zone) {
						$('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="zone_code"]').prop('disabled', true);
				}
			}
		});
		<?php } ?>
	});

	// Get Address (Shipping)
	$('.shipping-address :input').on('change', function() {
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
					if ($('.shipping-address :input[name$="['+key+']"]').length && $('.shipping-address *[name="'+key+'"]').val() == '') {
						$('.shipping-address :input[name$="['+key+']"]').val(value).trigger('input');
					}
				});
			},
		});
	});

	// On Change Country (Shipping)
	$('select[name="shipping_address[country_code]"]').on('input', function(e) {

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
			}
		});
		<?php } ?>
	});

</script>
<main id="main" class="container">
	{{notices}}

	<div id="content">

		<article id="modal-customer-details" class="modal" tabindex="-1" role="dialog" aria-label="<?php echo f::escape_attr(t('title_customer_details', 'Customer Details')); ?>">

			<?php echo f::form_begin('customer_details_form', 'post', document::ilink('checkout/customer'), false, ['aria-label' => f::escape_attr(t('title_customer_details', 'Customer Details'))]); ?>

				<div class="card">

					<div class="card-header">
						<h1 class="card-title"><?php echo t('title_customer_details', 'Customer Details'); ?></h1>
					</div>

					<div class="card-body">
						<div style="max-width: 800px;">

							<div id="billing-address">

								<?php echo f::form_input_hidden('customer[type]', 'business'); ?>

								<?php if (settings::get('customer_field_company')) { ?>
								<div id="business-details" class="grid"<?php echo (isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? ' style="display: none;"' : ''; ?>>
									<?php if (settings::get('customer_field_company')) { ?>
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_company_name', 'Company Name'); ?></div>
											<?php echo f::form_input_text('customer[company]', true, ['required' => '', 'autocomplete' => 'organization'] + ((isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? ['disabled' => ''] : [])); ?>
										</label>
									</div>
									<?php } ?>

									<?php if (settings::get('customer_field_tax_id')) { ?>
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_tax_id', 'Tax ID'); ?></div>
											<?php echo f::form_input_text('customer[tax_id]', true, ['readonly' => '', 'autocomplete' => 'off'] + ((isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? ['disabled' => ''] : [])); ?>
										</label>
									</div>
									<?php } ?>
								</div>
								<?php } ?>

								<div class="grid">
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
											<?php echo f::form_input_text('customer[firstname]', true, ['required' => '', 'autocomplete' => 'given-name']); ?>
										</label>
									</div>

									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
											<?php echo f::form_input_text('customer[lastname]', true, ['required' => '', 'autocomplete' => 'family-name']); ?>
										</label>
									</div>
								</div>

								<div class="grid">
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_address1', 'Address 1'); ?></div>
											<?php echo f::form_input_text('customer[address1]', true, ['autocomplete' => 'address-line1']); ?>
										</label>
									</div>

									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_address2', 'Address 2'); ?></div>
											<?php echo f::form_input_text('customer[address2]', true, ['autocomplete' => 'address-line2']); ?>
										</label>
									</div>
								</div>

								<div class="grid">
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
											<?php echo f::form_input_text('customer[postcode]', true, ['autocomplete' => 'postal-code']); ?>
										</label>
									</div>

									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_city', 'City'); ?></div>
											<?php echo f::form_input_text('customer[city]', true, ['autocomplete' => 'address-level2']); ?>
										</label>
									</div>
								</div>

								<div class="grid">
									<div class="col-sm-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
											<?php echo f::form_select_country('customer[country_code]', true, ['required' => '', 'autocomplete' => 'country']); ?>
										</label>
									</div>

									<?php if (settings::get('customer_field_zone')) { ?>
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
											<?php echo f::form_select_zone('customer[zone_code]', $_POST['customer']['country_code'] ?? null, true, ['required' => '', 'autocomplete' => 'address-level1']); ?>
										</label>
									</div>
									<?php } ?>
								</div>

								<div class="grid">
									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_email', 'Email'); ?></div>
											<?php echo f::form_input_email('customer[email]', true, ['required' => '', 'autocomplete' => 'email']); ?>
										</label>
									</div>

									<div class="col-sm-6">
										<label class="form-group">
											<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
											<?php echo f::form_input_phone('customer[phone]', true, ['autocomplete' => 'tel']); ?>
										</label>
									</div>
								</div>
							</div>

							<div id="shipping-address">

								<h3><?php echo f::form_checkbox('customer[different_shipping_address]', ['1', t('title_different_shipping_address', 'Different Shipping Address')], true); ?></h3>

								<fieldset class="details"<?php echo empty($_POST['customer']['different_shipping_address']) ? ' style="display: none;" disabled' : ''; ?>>

									<?php if (settings::get('customer_field_company')) { ?>
									<div class="grid">
										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_company_name', 'Company Name'); ?> (<?php echo t('text_or_leave_blank', 'Or leave blank'); ?>)</div>
												<?php echo f::form_input_text('customer[shipping_address][company]', true, ['autocomplete' => 'organization']); ?>
											</label>
										</div>
									</div>
									<?php } ?>

									<div class="grid">
										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
												<?php echo f::form_input_text('customer[shipping_address][firstname]', true, ['autocomplete' => 'given-name']); ?>
											</label>
										</div>

										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
												<?php echo f::form_input_text('customer[shipping_address][lastname]', true, ['autocomplete' => 'family-name']); ?>
											</label>
										</div>
									</div>

									<div class="grid">
										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_address1', 'Address 1'); ?></div>
												<?php echo f::form_input_text('customer[shipping_address][address1]', true, ['autocomplete' => 'address-line1']); ?>
											</label>
										</div>

										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_address2', 'Address 2'); ?></div>
												<?php echo f::form_input_text('customer[shipping_address][address2]', true, ['autocomplete' => 'address-line2']); ?>
											</label>
										</div>
									</div>

									<div class="grid">
										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
												<?php echo f::form_input_text('customer[shipping_address][postcode]', true, ['autocomplete' => 'postal-code']); ?>
											</label>
										</div>

										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_city', 'City'); ?></div>
												<?php echo f::form_input_text('customer[shipping_address][city]', true, ['autocomplete' => 'address-level2']); ?>
											</label>
										</div>
									</div>

									<div class="grid">
										<div class="col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
												<?php echo f::form_select_country('customer[shipping_address][country_code]', true, ['autocomplete' => 'country']); ?>
											</label>
										</div>

										<?php if (settings::get('customer_field_zone')) { ?>
										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
												<?php echo f::form_select_zone('customer[shipping_address][zone_code]', $_POST['shipping_address']['country_code'] ?? $_POST['customer']['country_code'] ?? null, true, ['autocomplete' => 'address-level1']); ?>
											</label>
										</div>
										<?php } ?>
									</div>

									<div class="grid">
										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
												<?php echo f::form_input_email('customer[shipping_address][email]', true, ['autocomplete' => 'email']); ?>
											</label>
										</div>

										<div class="col-sm-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
												<?php echo f::form_input_phone('customer[shipping_address][phone]', true, ['autocomplete' => 'tel']); ?>
											</label>
										</div>
									</div>

								</fieldset>
							</div>

							<div class="form-group">
								<?php echo f::form_checkbox('save_details', ['1', t('text_save_details_to_my_account', 'Save details to my account')], true); ?>
							</div>

							<div class="form-group">
								<!--<button class="btn btn-default" type="button" data-dismiss="modal"><?php echo t('title_cancel', 'Cancel'); ?></button>-->
								<?php echo f::form_button('save', t('title_save', 'Save'), 'submit'); ?>
							</div>
						</div>
					</div>
				</div>

			<?php echo f::form_end(); ?>
		</article>
	</div>
</main>

<script>
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

	if ($('select[name="customer[shipping_address][country_code]"] option:selected').data('postcode-format')) {
		$('input[name="customer[shipping_address][postcode]"]').attr('pattern', $('select[name="customer[shipping_address][country_code]"] option:selected').data('postcode-format'));
	} else {
		$('input[name="customer[shipping_address][postcode]"]').removeAttr('pattern');
	}

	if ($('select[name="customer[shipping_address][country_code]"] option:selected').data('phone-code')) {
		$('input[name="customer[shipping_address][phone]"]').attr('placeholder', '+' + $('select[name="customer[shipping_address][country_code]"] option:selected').data('phone-code'));
	} else {
		$('input[name="customer[shipping_address][phone]"]').removeAttr('placeholder');
	}

	$('input[name="sign_up"][type="checkbox"]').trigger('change');

	$('#modal-customer input[name="sign_up"]').on('change', function() {
		if (this.checked == true) {
			$('#modal-customer .account fieldset').prop('disabled', false).slideDown('fast');
		} else {
			$('#modal-customer .account fieldset').prop('disabled', true).slideUp('fast');
		}
	});

	$('#shipping-address input[name="customer[different_shipping_address]"]').on('change', function(e) {
		if (this.checked == true) {
			$('#shipping-address fieldset').prop('disabled', false).slideDown('fast');
		} else {
			$('#shipping-address fieldset').prop('disabled', true).slideUp('fast');
		}
	});

	// Type: support both 'type' and 'customer[type]' toggle names and multiple detail containers
	$('input[name="type"], input[name="customer[type]"]').on('change', function() {
		var is_business = $(this).val() == 'business';

		// billing-company container (used by some checkout partials)
		if (is_business) {
			$('#billing-company :input').prop('disabled', false);
			$('#billing-company').slideDown('fast');
		} else {
			$('#billing-company :input').prop('disabled', true);
			$('#billing-company').slideUp('fast');
		}

		// business-details container (used by other templates and sign_up)
		if (is_business) {
			$('#business-details :input').prop('disabled', false);
			$('#business-details').slideDown('fast');
		} else {
			$('#business-details :input').prop('disabled', true);
			$('#business-details').slideUp('fast');
		}
	}).first().trigger('change');

	$('#modal-customer select[name="country_code"]').on('input', function(e) {

		if ($('option:selected', this).data('tax-id-format')) {
			$('input[name="tax_id"]').attr('pattern', $('option:selected', this).data('tax-id-format'));
		} else {
			$('input[name="tax_id"]').removeAttr('pattern');
		}

		if ($('option:selected', this).data('postcode-format')) {
			$('input[name="postcode"]').attr('pattern', $('option:selected', this).data('postcode-format'));
		} else {
			$('input[name="postcode"]').removeAttr('pattern');
		}

		if ($('option:selected', this).data('phone-code')) {
			$('input[name="phone"]').attr('placeholder', '+' + $('option:selected', this).data('phone-code'));
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

	$('#modal-customer select[name="customer[shipping_address][country_code]"]').on('input', function(e) {

		if ($('option:selected', this).data('postcode-format')) {
			$('input[name="customer[shipping_address][postcode]"]').attr('pattern', $('option:selected', this).data('postcode-format'));
		} else {
			$('input[name="customer[shipping_address][postcode]"]').removeAttr('pattern');
		}

		if ($('option:selected', this).data('phone-code')) {
			$('input[name="customer[shipping_address][phone]"]').attr('placeholder', '+' + $('option:selected', this).data('phone-code'));
		} else {
			$('input[name="customer[shipping_address][phone]"]').removeAttr('placeholder');
		}

		<?php if (settings::get('customer_field_zone')) { ?>
		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: false,
			dataType: 'json',
			success: function(data) {
				$('select[name="customer[shipping_address][zone_code]"]').html('');
				if (data.length) {
					$('select[name="customer[shipping_address][zone_code]"]').prop('disabled', false);
					$.each(data, function(i, zone) {
						$('select[name="customer[shipping_address][zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="customer[shipping_address][zone_code]"]').prop('disabled', true);
				}
			}
		});
		<?php } ?>
	});
</script>
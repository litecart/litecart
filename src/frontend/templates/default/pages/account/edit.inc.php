<main id="main" class="container">
	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}

				<section id="box-edit-account" class="card" aria-label="<?php echo f::escape_attr(t('title_sign_in_and_security', 'Sign-In and Security')); ?>">
					<div class="card-header">
						<h1 class="card-title"><?php echo t('title_sign_in_and_security', 'Sign-In and Security'); ?></h1>
					</div>

					<div class="card-body">
						<?php echo f::form_begin('customer_account_form', 'post', null, false, ['style' => 'max-width: 720px;', 'aria-label' => f::escape_attr(t('title_sign_in_and_security', 'Sign-In and Security'))]); ?>

							<div class="form-grid">

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
										<?php echo f::form_input_email('email', true, ['required' => '', 'autocomplete' => 'email']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_password', 'Password'); ?></div>
										<?php echo f::form_input_password('password', '', ['required' => '', 'autocomplete' => 'current-password']); ?>
									</label>
								</div>
							</div>

							<div class="form-grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_new_password', 'New Password'); ?> (<?php echo t('text_or_leave_blank', 'Or leave blank'); ?>)</div>
										<?php echo f::form_input_password('new_password', '', ['autocomplete' => 'new-password']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_confirm_new_password', 'Confirm New Password'); ?></div>
										<?php echo f::form_input_password('confirmed_password', '', ['autocomplete' => 'new-password']); ?>
									</label>
								</div>
							</div>

							<p><?php echo f::form_button('save_account', t('title_save', 'Save')); ?></p>

						<?php echo f::form_end(); ?>
					</div>
				</section>

				<section id="box-edit-details" class="card" aria-label="<?php echo f::escape_attr(t('title_customer_profile', 'Customer Profile')); ?>">
					<div class="card-header">
						<h1 class="card-title"><?php echo t('title_customer_profile', 'Customer Profile'); ?></h1>
					</div>

					<div class="card-body">
						<?php echo f::form_begin('customer_details_form', 'post', null, false, ['style' => 'max-width: 720px;', 'aria-label' => f::escape_attr(t('title_customer_profile', 'Customer Profile'))]); ?>

							<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
							<div class="grid">
								<div class="col-6">
									<div class="form-group">
										<div class="form-label"><?php echo t('title_customer_type', 'Customer Type'); ?></div>
										<?php echo f::form_toggle('customer[type]', ['business' => t('title_business', 'Business'), 'individual' => t('title_individual', 'Individual')], true); ?>
									</div>
								</div>
							</div>
							<?php } ?>

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
										<?php echo f::form_input_text('tax_id', true, 'autocomplete="off" ' . ((isset($_POST['customer']['type']) && $_POST['customer']['type'] == 'individual') ? 'disabled' : '')); ?>
									</label>
								</div>
								<?php } ?>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
										<?php echo f::form_input_text('firstname', true, ['required' => '', 'autocomplete' => 'given-name']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
										<?php echo f::form_input_text('lastname', true, ['required' => '', 'autocomplete' => 'family-name']); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_address1', 'Address 1'); ?></div>
										<?php echo f::form_input_text('address1', true, ['autocomplete' => 'address-line1']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_address2', 'Address 2'); ?></div>
										<?php echo f::form_input_text('address2', true, ['autocomplete' => 'address-line2']); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
										<?php echo f::form_input_text('postcode', true, ['autocomplete' => 'postal-code']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_city', 'City'); ?></div>
										<?php echo f::form_input_text('city', true, ['autocomplete' => 'address-level2']); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
										<?php echo f::form_select_country('country_code', true, ['required' => '', 'autocomplete' => 'country']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
										<?php echo form_select_zone('zone_code', $_POST['country_code'] ?? null, true, ['required' => '', 'autocomplete' => 'address-level1']); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
										<?php echo f::form_input_phone('phone', true, ['autocomplete' => 'tel', 'placeholder' => (isset($_POST['country_code']) ? reference::country($_POST['country_code'])->phone_code : '')]); ?>
									</label>
								</div>
							</div>

							<div class="form-group">
								<?php echo f::form_checkbox('newsletter', ['1', t('consent_newsletter', 'I would like to be notified occasionally via email when there are new products or campaigns.')], true); ?>
							</div>

							<div class="form-group">
								<?php echo f::form_button('save_details', t('title_save', 'Save')); ?>
							</div>

						<?php echo f::form_end(); ?>
					</div>
				</section>
			</div>
		</div>
	</div>
</main>

<script>
	$('input[name="customer[type]"]').on('change', function() {
		if ($(this).val() == 'business') {
			$('#business-details :input').prop('disabled', false);
			$('#business-details').slideDown('fast');
		} else {
			$('#business-details :input').prop('disabled', true);
			$('#business-details').slideUp('fast');
		}
	});

	$('form[name="customer_form"]').on('input', ':input', function() {
		if ($(this).val() == '') return;

		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $(this).closest('form').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: function(data) {
				if (data['alert']) {
					alert(data['alert']);
					return;
				}
				$.each(data, function(key, value) {
					console.log(key +' '+ value);
					if ($('input[name="'+key+'"]').length && $('input[name="'+key+'"]').val() == '') $('input[name="'+key+'"]').val(data[key]);
				});
			}
		});
	});

	$('select[name="country_code"]').on('change', function(e) {

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

		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$("select[name='zone_code']").html('');
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
	});

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
</script>
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

				<section id="box-create-account" class="card">

					<div class="card-header">
						<h1 class="card-title"><?php echo t('title_sign_up', 'Sign Up'); ?></h1>
					</div>

					<div class="card-body">
						<?php echo functions::form_begin('customer_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

							<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<?php echo functions::form_toggle('type', ['business' => t('title_business', 'Business'), 'individual' => t('title_individual', 'Individual')], true); ?>
									</label>
								</div>
							</div>
							<?php } ?>

							<?php if (settings::get('customer_field_company')) { ?>
							<div id="business-details" class="grid"<?php echo (isset($_POST['type']) && $_POST['type'] == 'individual') ? ' style="display: none;"' : ''; ?>>
								<?php if (settings::get('customer_field_company')) { ?>
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_company_name', 'Company Name'); ?></div>
										<?php echo functions::form_input_text('company', true, 'required' . ((isset($_POST['type']) && $_POST['type'] == 'individual') ? ' disabled' : '')); ?>
									</label>
								</div>
								<?php } ?>

								<?php if (settings::get('customer_field_tax_id')) { ?>
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_tax_id', 'Tax ID'); ?></div>
										<?php echo functions::form_input_text('tax_id', true, (isset($_POST['type']) && $_POST['type'] == 'individual') ? 'disabled' : ''); ?>
									</label>
								</div>
								<?php } ?>
							</div>
							<?php } ?>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
										<?php echo functions::form_input_text('firstname', true, 'required'); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
										<?php echo functions::form_input_text('lastname', true, 'required'); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_address1', 'Address 1'); ?></div>
										<?php echo functions::form_input_text('address1', true); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_address2', 'Address 2'); ?></div>
										<?php echo functions::form_input_text('address2', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
										<?php echo functions::form_input_text('postcode', true); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_city', 'City'); ?></div>
										<?php echo functions::form_input_text('city', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
										<?php echo functions::form_select_country('country_code', true, 'required'); ?>
									</label>
								</div>

								<?php if (settings::get('customer_field_zone')) { ?>
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
										<?php echo functions::form_select_zone('zone_code', fallback($_POST['country_code']), true, 'required'); ?>
									</label>
								</div>
								<?php } ?>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_email', 'Email'); ?></div>
										<?php echo functions::form_input_email('email', true, 'required'); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
										<?php echo functions::form_input_phone('phone', true); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_desired_password', 'Desired Password'); ?></div>
										<?php echo functions::form_input_password('password', '', 'required'); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_confirm_password', 'Confirm Password'); ?></div>
										<?php echo functions::form_input_password('confirmed_password', '', 'required'); ?>
									</label>
								</div>
							</div>

							<div class="form-group">
								<?php echo functions::form_checkbox('newsletter', ['1', t('consent_newsletter', 'I would like to be notified occasionally via email when there are new products or campaigns.')], true); ?>
							</div>

							<?php if ($consent) { ?>

								<div class="form-group">
									<?php echo functions::form_checkbox('terms_agreed', ['1', $consent], true, 'required'); ?>
								</div>
							<?php } ?>

							<?php if (settings::get('captcha_enabled')) { ?>
								<label class="form-group">
									<div class="form-label"><?php echo t('title_captcha', 'CAPTCHA'); ?></div>
									<?php echo functions::form_captcha('sign_up'); ?>
								</label>
							<?php } ?>

							<?php echo functions::form_button('sign_up', t('title_sign_up', 'Sign Up')); ?>

						<?php echo functions::form_end(); ?>
					</div>
				</section>

			</div>
		</div>
	</div>
</main>

<script>
	$('input[name="type"]').on('change', function() {
		if ($(this).val() == 'business') {
			$('#business-details :input').prop('disabled', false);
			$('#business-details').slideDown('fast');
		} else {
			$('#business-details :input').prop('disabled', true);
			$('#business-details').slideUp('fast');
		}
	});

	$('#box-create-account').on('change', ':input', function() {
		if ($(this).val() == '') return;

		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $(this).closest('form').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			error: function(jqXHR, textStatus, errorThrown) {
				console.error('Get Address: ' + errorThrown);
			},
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
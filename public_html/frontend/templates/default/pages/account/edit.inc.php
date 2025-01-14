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

				<section id="box-edit-account" class="card">
					<div class="card-header">
						<h1 class="card-title"><?php echo language::translate('title_sign_in_and_security', 'Sign-In and Security'); ?></h1>
					</div>

					<div class="card-body">
						<?php echo functions::form_begin('customer_account_form', 'post', null, false, 'style="max-width: 640px;"'); ?>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
										<?php echo functions::form_input_email('email', true, 'required'); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_password', 'Password'); ?></div>
										<?php echo functions::form_input_password('password', '', 'required'); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_new_password', 'New Password'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</div>
										<?php echo functions::form_input_password('new_password', ''); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_confirm_new_password', 'Confirm New Password'); ?></div>
										<?php echo functions::form_input_password('confirmed_password', ''); ?>
									</label>
								</div>
							</div>

							<p><?php echo functions::form_button('save_account', language::translate('title_save', 'Save')); ?></p>

						<?php echo functions::form_end(); ?>
					</div>
				</section>

				<section id="box-edit-details" class="card">
					<div class="card-header">
						<h1 class="card-title"><?php echo language::translate('title_customer_profile', 'Customer Profile'); ?></h1>
					</div>

					<div class="card-body">
						<?php echo functions::form_begin('customer_details_form', 'post', null, false, 'style="max-width: 640px;"'); ?>

							<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
							<label class="form-group">
								<?php echo functions::form_toggle('type', ['individual' => language::translate('title_individual', 'Individual'), 'company' => language::translate('title_company', 'Company')], empty($_POST['type']) ? 'individual' : true); ?>
							</label>

							<div class="company-details" <?php echo (empty($_POST['type']) || $_POST['type'] == 'individual') ? 'style="display: none;"' : ''; ?>>
								<div class="grid">
									<?php if (settings::get('customer_field_company')) { ?>
									<div class="col-6">
										<label class="form-group">
											<div class="form-label"><?php echo language::translate('title_company_name', 'Company Name'); ?></div>
											<?php echo functions::form_input_text('company', true, 'required'); ?>
										</label>
									</div>
									<?php } ?>

									<?php if (settings::get('customer_field_tax_id')) { ?>
									<div class="col-6">
										<label class="form-group">
											<div class="form-label"><?php echo language::translate('title_tax_id', 'Tax ID'); ?></div>
											<?php echo functions::form_input_text('tax_id', true); ?>
										</label>
									</div>
									<?php } ?>
								</div>
							</div>
							<?php } ?>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
										<?php echo functions::form_input_text('firstname', true, 'required'); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
										<?php echo functions::form_input_text('lastname', true, 'required'); ?>
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
										<?php echo functions::form_select_country('country_code', true, 'required'); ?>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
										<?php echo form_select_zone('zone_code', fallback($_POST['country_code']), true, 'required'); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
										<?php echo functions::form_input_phone('phone', true, 'placeholder="'. (isset($_POST['country_code']) ? reference::country($_POST['country_code'])->phone_code : '') .'"'); ?>
									</label>
								</div>
							</div>

							<div class="form-group">
								<?php echo functions::form_checkbox('newsletter', ['1', language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.')], true); ?>
							</div>

							<div>
								<?php echo functions::form_button('save_details', language::translate('title_save', 'Save')); ?>
							</div>

						<?php echo functions::form_end(); ?>
					</div>
				</section>
			</div>
		</div>
	</div>
</main>

<script>
	$('input[name="type"]').on('change', function() {
		if ($(this).val() == 'company') {
			$('.company-details :input').prop('disabled', false)
			$('.company-details').slideDown('fast')
		} else {
			$('.company-details :input').prop('disabled', true)
			$('.company-details').slideUp('fast')
		}
	}).first().trigger('change')

	$('form[name="customer_form"]').on('input', ':input', function() {
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
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$("select[name='zone_code']").html('')
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

	if ($('select[name="country_code"] option:selected').data('tax-id-format')) {
		$('input[name="tax_id"]').attr('pattern', $('select[name="country_code"] option:selected').data('tax-id-format'))
	} else {
		$('input[name="tax_id"]').removeAttr('pattern')
	}

	if ($('select[name="country_code"] option:selected').data('postcode-format')) {
		$('input[name="postcode"]').attr('pattern', $('select[name="country_code"] option:selected').data('postcode-format'))
	} else {
		$('input[name="postcode"]').removeAttr('pattern')
	}

	if ($('select[name="country_code"] option:selected').data('phone-code')) {
		$('input[name="phone"]').attr('placeholder', '+' + $('select[name="country_code"] option:selected').data('phone-code'))
	} else {
		$('input[name="phone"]').removeAttr('placeholder')
	}
</script>
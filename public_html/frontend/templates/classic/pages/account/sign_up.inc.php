<main id="main" class="container">
	<div id="sidebar">
		<div id="column-left">
			<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
		</div>
	</div>

	<div id="content">
		{{notices}}
		{{breadcrumbs}}

		<section id="box-create-account" class="card">
			<div class="card-header">
				<h1 class="card-title"><?php echo language::translate('title_sign_up', 'Sign Up'); ?></h1>
			</div>

			<div class="card-body">
				<?php echo functions::form_begin('customer_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

				<div class="form-grid">

					<?php if (settings::get('customer_field_company') || settings::get('customer_field_tax_id')) { ?>
						<div class="col-6">
							<?php if (settings::get('customer_field_company')) { ?>
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_company_name', 'Company Name'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</div>
								<?php echo functions::form_input_text('company', true); ?>
							</label>
							<?php } ?>
						</div>

						<div class="col-6">
							<?php if (settings::get('customer_field_tax_id')) { ?>
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_tax_id', 'Tax ID'); ?> (<?php echo language::translate('text_or_leave_blank', 'Or leave blank'); ?>)</div>
								<?php echo functions::form_input_text('tax_id', true); ?>
							</label>
							<?php } ?>
						</div>

					<?php } ?>


						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_firstname', 'First Name'); ?></div>
								<?php echo functions::form_input_text('firstname', true, 'required'); ?>
							</label>
						</div>
						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_lastname', 'Last Name'); ?></div>
								<?php echo functions::form_input_text('lastname', true, 'required'); ?>
							</label>
						</div>

						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_address1', 'Address 1'); ?></div>
								<?php echo functions::form_input_text('address1', true); ?>
							</label>
						</div>

						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_address2', 'Address 2'); ?></div>
								<?php echo functions::form_input_text('address2', true); ?>
							</label>
						</div>

						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_postcode', 'Postal Code'); ?></div>
								<?php echo functions::form_input_text('postcode', true); ?>
							</label>
						</div>

						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_city', 'City'); ?></div>
								<?php echo functions::form_input_text('city', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-<?php echo settings::get('customer_field_zone') ? 6 : 12; ?>">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_country', 'Country'); ?></div>
								<?php echo functions::form_select_country('country_code', true, 'required'); ?>
							</label>
						</div>

						<?php if (settings::get('customer_field_zone')) { ?>
						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></div>
								<?php echo functions::form_select_zone('zone_code', fallback($_POST['country_code']), true, 'required'); ?>
							</label>
						</div>
						<?php } ?>
					</div>

					<div class="grid">
						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_email', 'Email'); ?></div>
								<?php echo functions::form_input_email('email', true, 'required'); ?>
							</label>
						</div>

						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
								<?php echo functions::form_input_phone('phone', true); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_desired_password', 'Desired Password'); ?></div>
								<?php echo functions::form_input_password('password', '', 'required'); ?>
							</label>
						</div>

						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></div>
								<?php echo functions::form_input_password('confirmed_password', '', 'required'); ?>
							</label>
						</div>
					</div>

					<div class="form-group">
						<?php echo functions::form_checkbox('newsletter', ['1', language::translate('consent_newsletter', 'I would like to be notified occasionally via e-mail when there are new products or campaigns.')], true); ?>
					</div>

					<?php if ($consent) { ?>
					<p class="consent">
						<?php echo functions::form_checkbox('terms_agreed', ['1', $consent], true, 'required'); ?>
					</p>
					<?php } ?>

					<?php if (settings::get('captcha_enabled')) { ?>
					<div class="grid">
						<div class="col-6">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></div>
								<?php echo functions::form_captcha('sign_up'); ?>
							</label>
						</div>
					</div>
					<?php } ?>

					<div class="btn-group">
						<?php echo functions::form_button('sign_up', language::translate('title_sign_up', 'Sign Up')); ?>
					</div>

				<?php echo functions::form_end(); ?>
			</div>
		</section>
	</div>
</main>

<script>
	$('#box-create-account').on('change', ':input', function() {
		if ($(this).val() == '') return



		$.getJSON(
			'<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			$('.billing-address :input').serialize(),
			function(data) {
				if (data['alert']) alert(data['alert'])
				$.each(data, function(key, value) {
					$('.billing-address :input[name="'+key+'"]').val(value)
				})
			}
		).always(function() {
			$('body').css('cursor', 'auto')
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

		<?php if (settings::get('customer_field_zone')) { ?>

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
		<?php } ?>
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
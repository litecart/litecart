<?php

	$currency_options = ['' => '-- '. t('title_select', 'Select') .' --'];
	foreach ($currencies as $currency) {
		$currency_options[$currency['code']] = $currency['name'];
	}

	$language_options = ['' => '-- '. t('title_select', 'Select') .' --',];
	foreach ($languages as $language) {
		$language_options[$language['code']] = $language['name'];
	}
?>

<main id="main" class="container">
	{{notices}}

	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">

				<section id="box-regional-settings" class="card" aria-label="<?php echo f::escape_attr(t('title_regional_settings', 'Regional Settings')); ?>">
					<div class="card-header">
						<h1 class="card-title"><?php echo t('title_regional_settings', 'Regional Settings'); ?></h1>
					</div>

					<div class="card-body">
						<?php echo f::form_begin('region_form', 'post', document::ilink(), false, ['style' => 'max-width: 640px;', 'aria-label' => f::escape_attr(t('title_regional_settings', 'Regional Settings'))]); ?>

							<div class="grid">

								<?php if (count($languages) > 1) { ?>
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_language', 'Language'); ?></div>
										<?php echo f::form_select('language_code', $language_options, language::$selected['code'], ['autocomplete' => 'language']); ?>
									</label>
								</div>
								<?php } ?>

								<?php if (count($currencies) > 1) { ?>
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_currency', 'Currency'); ?></div>
										<?php echo f::form_select('currency_code', $currency_options, currency::$selected['code']); ?>
									</label>
								</div>
								<?php } ?>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_country', 'Country'); ?></div>
										<?php echo f::form_select_country('country_code', customer::$data['country_code'], ['autocomplete' => 'country']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_zone_state_province', 'Zone/State/Province'); ?></div>
										<?php echo f::form_select_zone('zone_code', customer::$data['country_code'], customer::$data['zone_code'], ['autocomplete' => 'address-level1']); ?>
									</label>
								</div>
							</div>

							<div class="grid">
								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_postcode', 'Postal Code'); ?></div>
										<?php echo f::form_input_text('postcode', customer::$data['postcode'], ['autocomplete' => 'postal-code']); ?>
									</label>
								</div>

								<div class="col-sm-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_display_prices_including_tax', 'Display Prices Including Tax'); ?></div>
										<?php echo f::form_toggle('display_prices_including_tax', 'y/n', customer::$data['display_prices_including_tax']); ?>
									</label>
								</div>
							</div>

							<?php echo f::form_button('save', t('title_save', 'Save')); ?>

						<?php echo f::form_end(); ?>
					</div>
				</section>

			</div>
		</div>
	</div>
</main>

<script>
	if ($('#regional-settings .title').parents('.modal')) {
		$('#regional-settings .title').closest('.modal').find('.modal-title').text($('#regional-settings .title').text());
		$('#regional-settings .title').remove();
	}

	$('select[name="country_code"]').on('change', function() {

		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="zone_code"]').html('');
				if ($('select[name="zone_code"]').is(':disabled')) $('select[name="zone_code"]').prop('disabled', false);
				if (data) {
					$.each(data, function(i, zone) {
						$('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="zone_code"]').prop('disabled', true);
				}
			}
		});
	});
</script>
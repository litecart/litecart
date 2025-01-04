<?php

	if (!empty($_GET['currency_code'])) {
		$currency = new ent_currency($_GET['currency_code']);
	} else {
		$currency = new ent_currency();
	}

	if (!$_POST) {
		$_POST = $currency->data;
	}

	document::$title[] = !empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_create_new_currency', 'Create New Currency');

	breadcrumbs::add(language::translate('title_currencies', 'Currencies'), document::ilink(__APP__.'/currencies'));
	breadcrumbs::add(!empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_create_new_currency', 'Create New Currency'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			$_POST['code'] = strtoupper($_POST['code']);

			if (empty($_POST['code'])) {
				throw new Exception(language::translate('error_must_enter_code', 'You must enter a code'));
			}

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
			}

			if (empty($_POST['value'])) {
				throw new Exception(language::translate('error_must_enter_value', 'You must enter a value'));
			}

			if ((!empty($_POST['set_store']) || $_POST['code'] == settings::get('store_currency_code')) && (float)$_POST['value'] != 1) {
				throw new Exception(language::translate('error_store_currency_must_have_value_1', 'The store currency must always have the currency value 1.0.'));
			}

			if (empty($_POST['set_default']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code') && $currency->data['code'] != $_POST['code']) {
				throw new Exception(language::translate('error_cannot_rename_default_currency', 'You must change the default currency before renaming it.'));
			}

			if (empty($_POST['set_store']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code') && $currency->data['code'] != $_POST['code']) {
				throw new Exception(language::translate('error_cannot_rename_store_currency', 'You must change the store currency before renaming it.'));
			}

			if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('default_currency_code')) {
				throw new Exception(language::translate('error_cannot_set_disabled_default_currency', 'You cannot set a disabled currency as default currency.'));
			}

			if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($currency->data['code']) && $currency->data['code'] == settings::get('store_currency_code')) {
				throw new Exception(language::translate('error_cannot_set_disabled_store_currency', 'You cannot set a disabled currency as store currency.'));
			}

			foreach ([
				'status',
				'code',
				'number',
				'name',
				'value',
				'prefix',
				'suffix',
				'decimals',
				'priority',
			] as $field) {
				if (isset($_POST[$field])) {
					$currency->data[$field] = $_POST[$field];
				}
			}

			$currency->save();

			if (!empty($_POST['set_default'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."settings
					set `value` = '". database::input($_POST['code']) ."'
					where `key` = 'default_currency_code'
					limit 1;"
				);
			}

			if (!empty($_POST['set_store'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."settings
					set `value` = '". database::input($_POST['code']) ."'
					where `key` = 'store_currency_code'
					limit 1;"
				);
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/currencies'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($currency->data['id'])) {
				throw new Exception(language::translate('error_must_provide_currency', 'You must provide a currency'));
			}

			$currency->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/currencies'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$store_currency = reference::currency(settings::get('store_currency_code'));

	$statuses = [
		1 => language::translate('title_enabled', 'Enabled'),
		-1 => language::translate('title_hidden', 'Hidden'),
		0 => language::translate('title_disabled', 'Disabled'),
	];

	// Prefillable currencies
	if (empty($currency->data['id'])) {

		// Get all existing currencies
		$existing_currencies = database::query(
			"select code from ". DB_TABLE_PREFIX ."currencies;"
		)->fetch_all('code');

		// Get currencies from i18n repository
		$client = new http_client();
		$result = $client->call('GET', 'https://raw.githubusercontent.com/litecart/i18n/master/currencies.csv');
		$available_currencies = functions::csv_decode($result);

		// Filter already added
		$available_currencies = array_filter($available_currencies, function($a) use ($existing_currencies) {
			return !in_array($a['code'], $existing_currencies);
		});

		// Sort by code
		uasort($available_currencies, function($a, $b){
			return ($a['code'] < $b['code']) ? -1 : 1;
		});

		if ($available_currencies) {

			$prefillable_currency_options = [['', '-- '. language::translate('title_select', 'Select') .' --']];

			// Append to array of options
			foreach ($available_currencies as $available_currency) {
				$prefillable_currency_options[] = [
					$available_currency['code'],
					$available_currency['code'] .' &ndash; '. $available_currency['name'],
					implode(' ', array_map(function($k, $v){
						return 'data-'. str_replace('_', '-', $k) .'="'. functions::escape_attr($v) .'"';
					}, array_keys($available_currency), array_values($available_currency))),
				];
			}
		}
	}

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($currency->data['id']) ? language::translate('title_edit_currency', 'Edit Currency') : language::translate('title_create_new_currency', 'Create New Currency'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('currency_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

			<?php if (!empty($prefillable_currency_options)) { ?>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('text_prefill_from_the_web', 'Prefill from the web'); ?></label>
						<?php echo functions::form_select('prefill', $prefillable_currency_options, ''); ?>
					</div>
				</div>
			</div>
			<?php } ?>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_status', 'Status'); ?></label>
						<?php echo functions::form_toggle('status', $statuses, true); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_name', 'Name'); ?></label>
						<?php echo functions::form_input_text('name', true); ?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_code', 'Code'); ?> (ISO 4217) <a href="https://en.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></label>
						<?php echo functions::form_input_text('code', true, 'required pattern="[A-Z]{3}"'); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_number', 'Number'); ?> (ISO 4217) <a href="https://en.wikipedia.org/wiki/ISO_4217" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></label>
						<?php echo functions::form_input_text('number', true, 'required pattern="[0-9]{3}"'); ?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_value', 'Value'); ?></label>
						<div class="input-group">
							<?php echo functions::form_input_decimal('value', true, 4); ?>
							<span class="input-group-text"><?php echo $store_currency->code; ?></span>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_decimals', 'Decimals'); ?></label>
						<?php echo functions::form_input_number('decimals', true); ?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_prefix', 'Prefix'); ?></label>
						<?php echo functions::form_input_text('prefix', true); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_suffix', 'Suffix'); ?></label>
						<?php echo functions::form_input_text('suffix', true); ?>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo language::translate('title_priority', 'Priority'); ?></label>
						<?php echo functions::form_input_number('priority', true); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<?php echo functions::form_checkbox('set_default', ['1', language::translate('description_set_as_default_currency', 'Set as default currency')], (isset($currency->data['code']) && $currency->data['code'] && $currency->data['code'] == settings::get('default_currency_code')) ? '1' : true); ?>
						<?php echo functions::form_checkbox('set_store', ['1', language::translate('description_set_as_store_currency', 'Set as store currency')], (isset($currency->data['code']) && $currency->data['code'] && $currency->data['code'] == settings::get('store_currency_code')) ? '1' : true); ?>
					</div>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php echo (!empty($currency->data['id'])) ? functions::form_button_predefined('delete') : ''; ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<?php if (!empty($available_currencies)) { ?>
<script>
	$('select[name="prefill"]').on('change', function() {
		$.each($(this).find('option:selected').data(), function(key, value) {
			var field_name = key.replace(/([A-Z])/, '_$1').toLowerCase()
			$(':input[name="'+field_name+'"]').val(value)
		})
	})
</script>
<?php } ?>
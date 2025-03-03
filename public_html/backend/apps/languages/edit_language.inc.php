<?php

	if (!empty($_GET['language_code'])) {
		$language = new ent_language($_GET['language_code']);
	} else {
		$language = new ent_language();
		$language->data['direction'] = 'ltr';
		$language->data['url_type'] = 'path';
	}

	if (!$_POST) {
		$_POST = $language->data;
	}

	document::$title[] = !empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_create_new_language', 'Create New Language');

	breadcrumbs::add(language::translate('title_languages', 'Languages'), document::ilink(__APP__.'/languages'));
	breadcrumbs::add(!empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_create_new_language', 'Create New Language'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['code'])) {
				throw new Exception(language::translate('error_must_enter_code', 'You must enter a code'));
			}

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
			}

			if (!empty($_POST['url_type']) && $_POST['url_type'] == 'domain') {

				if (empty($_POST['domain_name'])) {
					throw new Exception(language::translate('error_must_provide_domain', 'You must provide a domain name'));
				}

				if (!empty($language->data['id'])) {
					if (database::query(
						"select id from ". DB_TABLE_PREFIX ."languages
						where domain_name = '". database::input($_POST['domain_name']) ."'
						and id != ". (int)$language->data['id'] ."
						limit 1;"
					)->num_rows) {
						throw new Exception(language::translate('error_domain_in_use_by_other_language', 'The domain name is already in use by another domain name.'));
					}
				}
			}

			if (empty($_POST['set_default']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code') && $language->data['code'] != $_POST['code']) {
				throw new Exception(language::translate('error_cannot_rename_default_language', 'You must change the default language before renaming it.'));
			}

			if (empty($_POST['set_store']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code') && $language->data['code'] != $_POST['code']) {
				throw new Exception(language::translate('error_cannot_rename_store_language', 'You must change the store language before renaming it.'));
			}

			if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code')) {
				throw new Exception(language::translate('error_cannot_set_disabled_default_language', 'You cannot set a disabled language as default language.'));
			}

			if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code')) {
				throw new Exception(language::translate('error_cannot_set_disabled_store_language', 'You cannot set a disabled language as store language.'));
			}

			if (!empty($_POST['locale']) && !setlocale(LC_ALL, preg_split('#\s*,\s*#', $_POST['locale'], -1, PREG_SPLIT_NO_EMPTY))) {
				throw new Exception(strtr(language::translate('error_not_a_valid_system_locale', '%locale is not a valid system locale on this machine'), ['%locale' => fallback($_POST['locale'], 'NULL')]));
			}

			setlocale(LC_ALL, preg_split('#\s*,\s*#', language::$selected['locale'], -1, PREG_SPLIT_NO_EMPTY)); // Restore

			if (!empty($_POST['locale_intl']) && !in_array($_POST['locale_intl'], ResourceBundle::getLocales(''))) {
				throw new Exception(language::translate('error_not_a_valid_intl_locale', '%locale is not a valid PHP Intl locale'));
			}

			##########

			if (empty($_POST['domain_name'])) {
				$_POST['domain_name'] = '';
			}

			$_POST['code'] = strtolower($_POST['code']);
			$_POST['raw_datetime'] = $_POST['raw_date'] .' '. $_POST['raw_time'];
			$_POST['format_datetime'] = $_POST['format_date'] .' '. $_POST['format_time'];

			foreach ([
				'status',
				'code',
				'code2',
				'name',
				'direction',
				'locale',
				'locale_intl',
				'mysql_collation',
				'url_type',
				'domain_name',
				'raw_date',
				'raw_time',
				'raw_datetime',
				'format_date',
				'format_time',
				'format_datetime',
				'decimal_point',
				'thousands_sep',
				'currency_code',
				'priority',
			] as $field) {
				if (isset($_POST[$field])) {
					$language->data[$field] = $_POST[$field];
				}
			}

			$language->save();

			if (!empty($_POST['set_default'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."settings
					set `value` = '". database::input($_POST['code']) ."'
					where `key` = 'default_language_code'
					limit 1;"
				);
			}

			if (!empty($_POST['set_store'])) {
				database::query(
					"update ". DB_TABLE_PREFIX ."settings
					set `value` = '". database::input($_POST['code']) ."'
					where `key` = 'store_language_code'
					limit 1;"
				);
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/languages'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($language->data['id'])) {
				throw new Exception(language::translate('error_must_provide_language', 'You must provide a language'));
			}

			$language->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(__APP__.'/languages'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$date_format_options = [
		'%e %b %Y' => language::strftime('%e %b %Y'),
		'%b %e %Y' => language::strftime('%b %e %Y'),
	];

	$time_format_options = [
		[
			'label' => '12-Hour Format',
			'options' => [
				'%I:%M %P' => language::strftime('%I:%M %p'),
			],
		],
		[
			'label' => '24-Hour Format',
			'options' => [
				'%H:%M' => language::strftime('%H:%M'),
			],
		],
	];

	$raw_date_options = [
		[
			'label' => 'Big-endian (YMD)', 'null', 'style="font-weight: bold;" disabled',
			'options' => [
				'Y-m-d' => date('Y-m-d'),
				'Y.m.d' => date('Y.m.d'),
				'Y/m/d' => date('Y/m/d'),
			],
		],
		[
			'label' => 'Little-endian (DMY)', 'null', 'style="font-weight: bold;" disabled',
			'options' => [
				'd-m-Y' => date('d-m-Y'),
				'd.m.Y' => date('d.m.Y'),
				'd/m/Y' => date('d/m/Y'),
			],
		],
		[
			'label' => 'Middle-endian (MDY)', 'null', 'style="font-weight: bold;" disabled',
			'options' => [
				'm/d/y' => date('m/d/y'),
			],
		],
	];

	$raw_time_options = [
		[
			'label' => '12-hour format',
			'options' => [
				'h:i A' => date('h:i A'),
			],
		],
		[
			'label' => '24-hour format',
			'options' => [
				'H:i' => date('H:i'),
			]
		],
	];

	$decimal_point_options = [
		'.' => language::translate('char_dot', 'Dot'),
		',' => language::translate('char_comma', 'Comma'),
	];

	$thousands_separator_options = [
		',' => language::translate('char_comma', 'Comma'),
		'.' => language::translate('char_dot', 'Dot'),
		' ' => language::translate('char_space', 'Space'),
		' ' => language::translate('char_nonbreaking_space', 'Non-Breaking Space'),
		'\'' => language::translate('char_single_quote', 'Single quote'),
	];

	$url_types = [
		'none' => language::translate('title_none', 'None'),
		'path' => language::translate('title_path_prefix', 'Path Prefix'),
		'domain' => language::translate('title_domain', 'Domain'),
	];

	$text_directions = [
		'ltr' => language::translate('title_left_to_right', 'Left To Right'),
		'rtl' => language::translate('title_right_to_left', 'Right To Left'),
	];

	$statuses = [
		'1' => language::translate('title_enabled', 'Enabled'),
		'-1' => language::translate('title_hidden', 'Hidden'),
		'0' => language::translate('title_disabled', 'Disabled'),
	];

	// Prefillable Languages
	if (empty($language->data['id'])) {

		// Get all existing languages
		$existing_languages = database::query(
			"select code from ". DB_TABLE_PREFIX ."languages;"
		)->fetch_all('code');

		// Get languages from i18n repository
		$client = new http_client();
		$result = $client->call('GET', 'https://raw.githubusercontent.com/litecart/i18n/master/languages.csv');
		$available_languages = functions::csv_decode($result);

		// Filter already added
		$available_languages = array_filter($available_languages, function($a) use ($existing_languages) {
			return !in_array($a['code'], $existing_languages);
		});

		// Sort by code
		uasort($available_languages, function($a, $b){
			return ($a['code'] < $b['code']) ? -1 : 1;
		});

		if ($available_languages) {

			$prefillable_language_options = [['', '-- '. language::translate('title_select', 'Select') .' --']];

			// Append to array of options
			foreach ($available_languages as $available_language) {
				$prefillable_language_options[] = [
					$available_language['code'],
					$available_language['code'] .' &ndash; '. $available_language['native'],
					implode(' ', array_map(function($k, $v){
						return 'data-'. str_replace('_', '-', $k) .'="'. functions::escape_attr($v) .'"';
					}, array_keys($available_language), array_values($available_language))),
				];
			}
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_create_new_language', 'Create New Language'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('language_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<?php if (!empty($prefillable_language_options)) { ?>
			<label class="form-group">
				<div class="form-label"><?php echo language::translate('text_prefill_from_the_web', 'Prefill from the web'); ?></div>
				<?php echo functions::form_select('prefill', $prefillable_language_options, ''); ?>
			</label>
			<?php } ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_status', 'Status'); ?></div>
						<?php echo functions::form_toggle('status', $statuses); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true, 'list="available-languages"'); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_text_direction', 'Text Direction'); ?></div>
						<?php echo functions::form_toggle('direction', $text_directions); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_code', 'Code'); ?> (ISO 639-1) <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></div>
						<?php echo functions::form_input_text('code', true, 'required pattern="[a-z]{2}"'); ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_code', 'Code'); ?> 2 (ISO 639-2) <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></div>
						<?php echo functions::form_input_text('code2', true, 'required pattern="[a-z]{3}"'); ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_php_int_locale', 'PHP Intl Locale'); ?></div>
						<?php echo functions::form_select_intl_locale('locale_intl', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">


				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_system_locale', 'System Locale'); ?></div>
						<?php echo functions::form_select_system_locale('locale', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_database_collation', 'Database Collation'); ?></div>
						<?php echo functions::form_select_mysql_collation('mysql_collation', true); ?>
					</label>
				</div>


			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_url_type', 'URL Type'); ?></div>
						<?php echo functions::form_toggle('url_type', $url_types); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_domain_name', 'Domain Name'); ?></div>
						<?php echo functions::form_input_text('domain_name', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_date_format', 'Date Format'); ?> <a href="https://php.net/manual/en/function.strftime.php" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></div>
						<?php echo functions::form_select('format_date', $date_format_options, true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_time_format', 'Time Format'); ?> <a href="https://php.net/manual/en/function.strftime.php" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></div>
						<?php echo functions::form_select_optgroup('format_time', $time_format_options, true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_raw_date_format', 'Raw Date Format'); ?> <a href="https://php.net/manual/en/function.date.php" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></div>
						<?php echo functions::form_select_optgroup('raw_date', $raw_date_options, true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_raw_time_format', 'Raw Time Format'); ?> <a href="https://php.net/manual/en/function.date.php" target="_blank"><?php echo functions::draw_fonticon('icon-square-out'); ?></a></div>
						<?php echo functions::form_select_optgroup('raw_time', $raw_time_options, true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_decimal_point', 'Decimal Point'); ?></div>
						<?php echo functions::form_select('decimal_point', $decimal_point_options, true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_thousands_sep', 'Thousands Separator'); ?></div>
						<?php echo functions::form_select('thousands_sep', $thousands_separator_options, true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_force_currency_code', 'Force Currency Code'); ?></div>
						<?php echo functions::form_input_text('currency_code', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_priority', 'Priority'); ?></div>
						<?php echo functions::form_input_number('priority', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<div class="form-group">
						<?php echo functions::form_checkbox('set_default', ['1', language::translate('description_set_as_default_language', 'Set as default language')], (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('default_language_code')) ? '1' : true); ?>
						<?php echo functions::form_checkbox('set_store', ['1', language::translate('description_set_as_store_language', 'Set as store language')], (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('store_language_code')) ? '1' : true); ?></label>
					</div>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($language->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<datalist id="available-languages"></datalist>

<script>
	$('input[name="url_type"]').on('change', function() {
		if ($('input[name="url_type"][value="domain"]:checked').length) {
			$('input[name="domain_name"]').prop('disabled', false)
		} else {
			$('input[name="domain_name"]').prop('disabled', true)
		}
	}).first().trigger('change')

	<?php if (!empty($available_languages)) { ?>
	$('select[name="prefill"]').on('change', function() {

		$.each($(this).find('option:selected').data(), function(key, value) {

			var field_name = key
				.replace(/([A-Z])/, '_$1')
				.toLowerCase()
				.replace(/^date_format$/, 'format_date')
				.replace(/^time_format$/, 'format_time')

			$(':input[name="'+field_name+'"]').not('[type="checkbox"]').not('[type="radio"]').val(value)
			$('input[name="'+field_name+'"][type="checkbox"][value="'+value+'"], input[name="'+field_name+'"][type="radio"][value="'+value+'"]').prop('checked', true)

			if (key == 'direction') {
				$('input[name="'+field_name+'"]:checked').parent('.btn').addClass('active').siblings().removeClass('active')
			}
		})
	})
	<?php } ?>
</script>
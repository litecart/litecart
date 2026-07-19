<?php

	document::$title[] = t('title_import_export_csv', 'Import/Export CSV');

	breadcrumbs::add(t('title_localization', 'Localization'));
	breadcrumbs::add(t('title_import_export_csv', 'Import/Export CSV'), document::ilink());

	$collections = include 'app://backend/apps/localization/translations/collections.inc.php';

	if (isset($_POST['import'])) {

		try {

			if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
				throw new Exception(t('error_must_select_file_to_upload', 'You must select a file to upload'));
			}

			if (!empty($_FILES['file']['error'])) {
				throw new Exception(t('error_uploaded_file_rejected', 'An uploaded file was rejected for unknown reason'));
			}

			$csv = file_get_contents($_FILES['file']['tmp_name']);

			if (!$csv = f::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
				throw new Exception(t('error_failed_decoding_csv', 'Failed decoding CSV'));
			}

			if (empty($csv[0]['code'])) {
				throw new Exception(t('error_missing_column_code', 'Missing column for code'));
			}

			$installed_language_codes = database::query(
				"select code from ". DB_TABLE_PREFIX ."languages
				order by priority;"
			)->fetch_all('code');

			$language_codes = array_diff(array_keys($csv[0]), ['code']);

			foreach ($language_codes as $language_code) {
				if (!in_array($language_code, $installed_language_codes)) {
					throw new Exception('Skipping unknown language ('. $language_code .') which is either missing or disabled');
				}
			}

			$updated = 0;
			$inserted = 0;
			$line = 0;

			foreach ($csv as $row) {
				$line++;

				if (preg_match('#^\[([a-z_]+):(\d+)\](.*)$#', $row['code'], $matches)) {

					if (!$collection = $collections[array_search($matches[1], array_column($collections, 'entity'))]) {
						throw new Exception('Unsupported entity on line '.$line);
					}

					list($entity, $id, $column) = array_slice($matches, 1);

					foreach ($language_codes as $language_code) {

						$translation = database::query(
							"select id, json_value(`". database::input($column) ."`, '$.". database::input($language_code) ."') as ". database::input($column) ."
							from `". DB_TABLE_PREFIX . database::input($collection['id']) ."`
							where id = '". database::input($id) ."'
							limit 1;"
						)->fetch();

						if ($translation) {

							if (empty($row[$language_code])) continue;
							if (empty($_POST['overwrite']) && empty($_POST['append'])) continue;
							if (empty($translation[$column]) && empty($_POST['append'])) continue;
							if (!empty($translation[$column]) && empty($_POST['update'])) continue;
							if (!in_array($language_code, $installed_language_codes)) continue;

							database::query(
								"update `". DB_TABLE_PREFIX . database::input($collection['id']) ."`
								set json_set(`". database::input($column) ."`, '$.". database::input($language_code) ."', '". database::input($row[$language_code], true) ."')
								where id = '". database::input($translation['id']) ."'
								limit 1;"
							);

							$updated++;

						}
					}

				} else {

					$translation = database::query(
						"select id from ". DB_TABLE_PREFIX ."translations
						where code = '". database::input($row['code']) ."'
						limit 1;"
					)->fetch();

					if ($translation) {

						foreach ($language_codes as $language_code) {

							if (empty($row[$language_code])) continue;
							if (empty($_POST['update']) && empty($_POST['append'])) continue;
							if (!in_array($language_code, $installed_language_codes)) continue;

							$current_value = database::query(
								"select json_unquote(coalesce(json_value(`text`, '$.". database::input($language_code) ."'), '')) as v
								from ". DB_TABLE_PREFIX ."translations
								where code = '". database::input($row['code']) ."'
								limit 1;"
							)->fetch();

							$current_value = $current_value['v'] ?? '';

							if (empty($current_value) && empty($_POST['append'])) continue;
							if (!empty($current_value) && empty($_POST['update'])) continue;

							database::query(
								"update ". DB_TABLE_PREFIX ."translations
								set `text` = json_set(coalesce(`text`, '{}'), '$.". database::input($language_code) ."', '". database::input($row[$language_code], true) ."')
								where code = '". database::input($row['code']) ."'
								limit 1;"
							);

							$updated++;
						}

					} else {

						if (empty($_POST['insert'])) continue;

						$languages = [];
						foreach ($language_codes as $language_code) {
							if (empty($row[$language_code])) continue;
							if (!in_array($language_code, $installed_language_codes)) continue;
							$languages[$language_code] = $row[$language_code];
						}

						if (!$languages) continue;

						$object_parts = [];
						foreach ($languages as $lang_code => $value) {
							$object_parts[] = "'". database::input($lang_code) ."', '". database::input($value, true) ."'";
						}
						$object_sql = 'json_object('. implode(', ', $object_parts) .')';

						database::query(
							"insert into ". DB_TABLE_PREFIX ."translations
							(code, `text`, created_at) values (
								'". database::input($row['code']) ."',
								$object_sql,
								'". date('Y-m-d H:i:s') ."'
							);"
						);

						$inserted++;
					}
				}
			}

			cache::clear_cache();

			notices::add($updated ? 'success' : 'notice', strtr(t('success_updated_n_existing_entries', 'Updated {n} existing entries'), [
				'{n}' => $updated
			]));

			notices::add($inserted ? 'success' : 'notice', strtr(t('success_insert_n_new_entries', 'Inserted {n} new entries'), [
				'{n}' => $inserted
			]));

			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['export'])) {

		try {

			if (empty($_POST['collections'])) {
				throw new Exception(t('error_must_select_at_least_one_collection', 'You must select at least one collection'));
			}

			if (empty($_POST['language_codes'])) {
				throw new Exception(t('error_must_select_at_least_one_language', 'You must select at least one language'));
			}

			$_POST['language_codes'] = array_filter($_POST['language_codes']);

			// AC-5, AC-6: validate codes against configured allowlist before
			// they are spliced into backtick-quoted column identifiers below.
			$allowed_language_codes = array_keys(language::$languages);
			foreach ($_POST['language_codes'] as $_lang_code) {
				try {
					database::identifier($_lang_code, $allowed_language_codes);
				} catch (InvalidArgumentException $e) {
					throw new Exception('Invalid language code');
				}
			}

			if (in_array('translations', $_POST['collections'])) {
				$sql_union[] = (
					"select 'translation' as entity, frontend, backend, code, updated_at, html,
					". implode(", ", f::array_each($_POST['language_codes'], fn($language_code) => "json_unquote(coalesce(json_value(`text`, '$.". database::input($language_code) ."'), '')) as `text_". database::identifier($language_code) ."`")) ."
					from ". DB_TABLE_PREFIX ."translations
					where code not regexp '^(settings_group:|settings_key:|cm|job|om|ot|pm|sm)_'"
				);
			}

			if (in_array('modules', $_POST['collections'])) {
				$sql_union[] = (
					"select 'translation' as entity, frontend, backend, code, updated_at, html,
					". implode(", ", f::array_each($_POST['language_codes'], fn($language_code) => "json_unquote(coalesce(json_value(`text`, '$.". database::input($language_code) ."'), '')) as `text_". database::identifier($language_code) ."`")) ."
					from ". DB_TABLE_PREFIX ."translations
					where code regexp '^(cm|job|om|ot|pm|sm)_'"
				);
			}

			if (in_array('setting_groups', $_POST['collections'])) {
				$sql_union[] = (
					"select 'translation' as entity, frontend, backend, code, updated_at, html,
					". implode(", ", f::array_each($_POST['language_codes'], fn($language_code) => "json_unquote(coalesce(json_value(`text`, '$.". database::input($language_code) ."'), '')) as `text_". database::identifier($language_code) ."`")) ."
					from ". DB_TABLE_PREFIX ."translations
					where code regexp '^settings_group:'"
				);
			}

			if (in_array('settings', $_POST['collections'])) {
				$sql_union[] = (
					"select 'translation' as entity, frontend, backend, code, updated_at, html,
					". implode(", ", f::array_each($_POST['language_codes'], fn($language_code) => "json_unquote(coalesce(json_value(`text`, '$.". database::input($language_code) ."'), '')) as `text_". database::identifier($language_code) ."`")) ."
					from ". DB_TABLE_PREFIX ."translations
					where code regexp '^settings_key:'"
				);
			}

			$union_select = function($id, $entity, $column) {
				return (
					"select '$entity' as entity, '1' as frontend, '1' as backend, concat('[". database::input($entity) ."', ':', id, ']". database::input($column) ."') as code, '' as updated_at,
						coalesce(". implode(', ', f::array_each($_POST['language_codes'], fn($language_code) => "if(json_value(`". database::input($column) ."`, '$.". database::input($language_code) ."') regexp '<', 1, null)")) .", 0) as html,
						". implode(', ', f::array_each($_POST['language_codes'], fn($language_code) => "json_value(`". $column ."`, '$.". database::input($language_code) ."') as `text_". database::identifier($language_code) ."`")) ."
					from ". DB_TABLE_PREFIX . database::input($id)
				);
			};

			foreach ($collections as $collection) {
				if (empty($collection['translatable'])) continue;
				if (empty($_GET['collections']) || in_array($collection['id'], $_GET['collections'])) {
					foreach ($collection['translatable'] as $column) {
						$sql_union[] = $union_select($collection['id'], $collection['entity'], $column);
					}
				}
			}

			$csv = database::query(
				"select * from (
					". implode(PHP_EOL . PHP_EOL . "union ", $sql_union) ."
				) x
				where x.code != ''
				order by x.code;"
			)->fetch_all(function($translation) {

				$row = ['code' => $translation['code']];

				foreach ($_POST['language_codes'] as $language_code) {
					$row[$language_code] = $translation['text_'.$language_code];
				}

				return $row;
			});

			ob_clean();

			if ($_POST['output'] == 'screen') {
				header('Content-Type: text/plain; charset='. $_POST['charset']);
			} else {
				header('Content-Type: application/csv; charset='. $_POST['charset']);
				// language_codes have already been allowlisted above, so they
				// are safe for the filename slot — but a whitelist filter here
				// protects against a future refactor that re-orders validation.
				$_filename_codes = preg_replace('#[^A-Za-z0-9_-]#', '', implode('-', $_POST['language_codes']));
				header('Content-Disposition: attachment; filename=translations-'. $_filename_codes .'.csv');
			}

			$eol = match($_POST['eol']) {
				'Linux' => "\r",
				'Mac' => "\n",
				'Win' => "\r\n",
				default => throw new Exception('Unsupported EOL character'),
			};

			echo f::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_csv_import_export', 'CSV Import/Export'); ?>
		</div>
	</div>

	<div class="card-body">
		<div class="grid" style="max-width: 980px;">

			<div class="col-xl-6">
				<?php echo f::form_begin('import_form', 'post', '', true); ?>

					<fieldset>
						<legend><?php echo t('title_import', 'Import'); ?></legend>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_csv_file', 'CSV File'); ?></div>
							<?php echo f::form_input_file('file', ['accept' => '.csv, .dsv, .tab, .tsv']); ?></label>
						</label>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_delimiter', 'Delimiter'); ?></div>
									<?php echo f::form_select('delimiter', ['' => t('title_auto', 'Auto') .' ('. t('text_default', 'default') .')', ',' => ',',  ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_enclosure', 'Enclosure'); ?></div>
									<?php echo f::form_select('enclosure', ['"' => '" ('. t('text_default', 'default') .')'], true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_escape_character', 'Escape Character'); ?></div>
									<?php echo f::form_select('escapechar', ['"' => '" ('. t('text_default', 'default') .')', '\\' => '\\'], true); ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_charset', 'Charset'); ?></div>
									<?php echo f::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
								</label>
							</div>
						</div>

						<div class="form-group">
							<?php echo f::form_checkbox('insert', ['1', t('text_insert_new_entries', 'Insert new entries')], true); ?>
							<?php echo f::form_checkbox('overwrite', ['1', t('text_overwrite_existing_entries', 'Overwrite existing entries')], true); ?>
							<?php echo f::form_checkbox('append', ['1', t('text_append_missing_entries', 'Append missing entries')], true); ?>
						</div>

						<p>
							<?php echo t('description_scan_before_importing_translations', 'It is recommended to always scan your installation for unregistered translations before performing an import or export.'); ?>
						</p>

						<?php echo f::form_button('import', t('title_import', 'Import'), 'submit'); ?>
					</fieldset>

				<?php echo f::form_end(); ?>
			</div>

			<div class="col-xl-6">
				<?php echo f::form_begin('export_form', 'post'); ?>

					<fieldset>
						<legend><?php echo t('title_export', 'Export'); ?></legend>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_collections', 'Collections'); ?></div>
							<?php echo f::form_select('collections[]', f::array_each($collections, fn($collection) => [$collection['id'], $collection['name']]), true); ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_languages', 'Languages'); ?></div>
							<?php echo f::form_select_language('language_codes[]', true); ?></label>
						</label>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_delimiter', 'Delimiter'); ?></div>
									<?php echo f::form_select('delimiter', [',' => ', ('. t('text_default', 'default') .')', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_enclosure', 'Enclosure'); ?></div>
									<?php echo f::form_select('enclosure', ['"' => '" ('. t('text_default', 'default') .')'], true); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_escape_character', 'Escape Character'); ?></div>
									<?php echo f::form_select('escapechar', ['"' => '" ('. t('text_default', 'default') .')', '\\' => '\\'], true); ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_charset', 'Charset'); ?></div>
									<?php echo f::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
								</label>
							</div>
						</div>

						<div class="grid">
							<div class="col-sm-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_line_ending', 'Line Ending'); ?></div>
									<?php echo f::form_select('eol', ['Win', 'Mac', 'Linux'], true); ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_output', 'Output'); ?></div>
									<?php echo f::form_select('output', ['screen' => t('title_screen', 'Screen'), 'file' => t('title_file', 'File')], true); ?>
								</label>
							</div>
						</div>

						<?php echo f::form_button('export', t('title_export', 'Export'), 'submit'); ?>
					</fieldset>

				<?php echo f::form_end(); ?>
			</div>
		</div>
	</div>
</div>

<?php

	document::$title[] = language::translate('title_import_export_csv', 'Import/Export CSV');

	breadcrumbs::add(language::translate('title_import_export_csv', 'Import/Export CSV'));

	$collections = include __DIR__.'/collections.inc.php';

	if (isset($_POST['import'])) {

		try {

			if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
				throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
			}

			if (!empty($_FILES['file']['error'])) {
				throw new Exception(language::translate('error_uploaded_file_rejected', 'An uploaded file was rejected for unknown reason'));
			}

			$csv = file_get_contents($_FILES['file']['tmp_name']);

			if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
				throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
			}

			if (empty($csv[0]['code'])) {
				throw new Exception(language::translate('error_missing_code_column', 'Missing column for code'));
			}

			$language_codes = array_diff(array_keys($csv[0]), ['code']);

			foreach ($language_codes as $language_code) {
				if (!in_array($language_code, array_keys(language::$languages))) {
					throw new Exception('Skipping unknown language ('. $language_code .') which is either missing or disabled');
				}
			}

			$updated = 0;
			$inserted = 0;
			$line = 0;

			foreach ($csv as $row) {
				$line++;

				$translation = database::query(
					"select * from ". DB_TABLE_PREFIX ."translations
					where code = '". database::input($row['code']) ."'
					limit 1;"
				)->fetch();

				if ($translation) {

					list($entity, $id, $column) = array_slice($matches, 1);

					foreach ($language_codes as $language_code) {

						if (empty($row[$language_code])) continue;
						if (empty($_POST['overwrite']) && empty($_POST['append'])) continue;
						if (empty($translation['text_'.$language_code]) && empty($_POST['append'])) continue;
						if (!empty($translation['text_'.$language_code]) && empty($_POST['overwrite'])) continue;
						if (!in_array($language_code, array_keys(language::$languages))) continue;

						database::query(
							"update ". DB_TABLE_PREFIX ."translations
							set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
							where code = '". database::input($row['code']) ."'
							limit 1;"
						);

						if ($translation = database::fetch($translation_query)) {

							if (empty($row[$language_code])) continue;
							if (empty($_POST['update']) && empty($_POST['append'])) continue;
							if (empty($translation['text_'.$language_code]) && empty($_POST['append'])) continue;
							if (!empty($translation['text_'.$language_code]) && empty($_POST['update'])) continue;
							if (!in_array($language_code, array_keys(language::$languages))) continue;

							database::query(
								"update ". DB_TABLE_PREFIX . $collection['info_table'] ."
								set `". database::input($column) ."` = '". database::input($row[$language_code], true) ."'
								where id = '". database::input($translation['id']) ."'
								limit 1;"
							);

							$updated++;
						} else {

							if (empty($_POST['append'])) continue;

							database::query(
								"insert into ". DB_TABLE_PREFIX . $collection['info_table'] ."
								(`". database::input($collection['entity_column']) ."`, language_code, `". database::input($column, !empty($translation['html'])) ."`)
								values ('". database::input($id) ."', '". database::input($language_code) ."', '". database::input($row[$language_code]) ."');"
							);

							$inserted++;
						}
					}

				} else {

					$translation_query = database::query(
						"select * from ". DB_TABLE_PREFIX ."translations
						where code = '". database::input($row['code']) ."'
						limit 1;"
					);

					if ($translation = database::fetch($translation_query)) {

						foreach ($language_codes as $language_code) {
							if (empty($row[$language_code])) continue;
							if (empty($_POST['update']) && empty($_POST['append'])) continue;
							if (empty($translation['text_'.$language_code]) && empty($_POST['append'])) continue;
							if (!empty($translation['text_'.$language_code]) && empty($_POST['update'])) continue;
							if (!in_array($language_code, array_keys(language::$languages))) continue;

							database::query(
								"update ". DB_TABLE_PREFIX ."translations
								set `text_". database::input($language_code) ."` = '". database::input($row[$language_code], true) ."'
								where code = '". database::input($row['code']) ."'
								limit 1;"
							);

							$updated++;
						}

					} else {

						if (empty($_POST['insert'])) continue;

						database::query(
							"insert into ". DB_TABLE_PREFIX ."translations
							(code) values ('". database::input($row['code']) ."');"
						);

						foreach ($language_codes as $language_code) {

							if (empty($row[$language_code])) continue;

							if (!in_array($language_code, array_keys(language::$languages))) continue;

							database::query(
								"update ". DB_TABLE_PREFIX ."translations
								set text_". $language_code ." = '". database::input($row[$language_code], true) ."'
								where code = '". database::input($row['code']) ."'
								limit 1;"
							);

							$inserted++;
						}
					}
				}
			}

			cache::clear_cache();

			notices::add($updated ? 'success' : 'notice', strtr(language::translate('success_updated_n_existing_entries', 'Updated %n existing entries'), ['%n' => $updated]));
			notices::add($inserted ? 'success' : 'notice', strtr(language::translate('success_insert_n_new_entries', 'Inserted %n new entries'), ['%n' => $inserted]));

			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['export'])) {

		try {

			if (empty($_POST['collections'])) {
				throw new Exception(language::translate('error_must_select_at_least_one_collection', 'You must select at least one collection'));
			}

			if (empty($_POST['language_codes'])) {
				throw new Exception(language::translate('error_must_select_at_least_one_language', 'You must select at least one language'));
			}

			$_POST['language_codes'] = array_filter($_POST['language_codes']);

			$csv = [];

			if (in_array('translations', $_POST['collections'])) {
				$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
											 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_POST['language_codes'])) ."
											 from ". DB_TABLE_PREFIX ."translations
											 where code not regexp '^(settings_group:|settings_key:|cm|job|om|ot|pm|sm)_'";
			}

			if (in_array('modules', $_POST['collections'])) {
				$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
											 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_POST['language_codes'])) ."
											 from ". DB_TABLE_PREFIX ."translations
											 where code regexp '^(cm|job|om|ot|pm|sm)_'";
			}

			if (in_array('setting_groups', $_POST['collections'])) {
				$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
											 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_POST['language_codes'])) ."
											 from ". DB_TABLE_PREFIX ."translations
											 where code regexp '^settings_group:'";
			}

			if (in_array('settings', $_POST['collections'])) {
				$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
											 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_POST['language_codes'])) ."
											 from ". DB_TABLE_PREFIX ."translations
											 where code regexp '^settings_key:'";
			}

			$union_select = function($entity, $entity_table, $info_table, $id, $field) {
				return (
					"select '$entity' as entity, '1' as frontend, '1' as backend, concat('[$entity', ':', e.id, ']$field') as code, '' as date_updated,
						coalesce(". implode(', ', array_map(function($language_code) use($field) { return "if($language_code.$field regexp '<', 1, null)"; }, $_POST['language_codes'])) .", 0) as html,
						". implode(', ', array_map(function($language_code) use($field) { return "`". database::input($language_code) ."`.$field as `text_". database::input($language_code) ."`"; }, $_POST['language_codes'])) ."
					from ". DB_TABLE_PREFIX ."$entity_table e
					". implode(PHP_EOL, array_map(function($language_code) use($info_table, $id) { return "left join ". DB_TABLE_PREFIX ."$info_table `". database::input($language_code) ."` on (`". database::input($language_code) ."`.$id = e.id and `". database::input($language_code) ."`.language_code = '$language_code')"; }, $_POST['language_codes']))
				);
			};

			foreach ($collections as $collection) {
				if (in_array($collection['id'], $_POST['collections'])) {
					foreach ($collection['info_columns'] as $column) {
						$sql_union[] = $union_select($collection['entity'], $collection['entity_table'], $collection['info_table'], $collection['entity_column'], $column);
					}
				}
			}

			$translations_query = database::query(
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
				header('Content-Disposition: attachment; filename=translations-'. implode('-', $_POST['language_codes']) .'.csv');
			}

			switch($_POST['eol']) {
				case 'Linux':
					echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
					break;
				case 'Mac':
					echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
					break;
				case 'Win':
				default:
					echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
					break;
			}

			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
		</div>
	</div>

	<div class="card-body">
		<div class="row" style="max-width: 980px;">

			<div class="col-xl-6">
				<?php echo functions::form_begin('import_form', 'post', '', true); ?>

					<fieldset>
						<legend><?php echo language::translate('title_import', 'Import'); ?></legend>

						<div class="form-group">
							<label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
							<?php echo functions::form_input_file('file', 'accept=".csv, .dsv, .tab, .tsv"'); ?></td>
						</div>

						<div class="row">
							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
								<?php echo functions::form_select('delimiter', ['' => language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ',' => ',',  ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
							</div>

							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
								<?php echo functions::form_select('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6">
								<label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
								<?php echo functions::form_select('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
							</div>

							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_charset', 'Charset'); ?></label>
								<?php echo functions::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
							</div>
						</div>

						<div class="form-group">
							<?php echo functions::form_checkbox('insert', ['1', language::translate('text_insert_new_entries', 'Insert new entries')], true); ?>
							<?php echo functions::form_checkbox('overwrite', ['1', language::translate('text_overwrite_existing_entries', 'Overwrite existing entries')], true); ?>
							<?php echo functions::form_checkbox('append', ['1', language::translate('text_append_missing_entries', 'Append missing entries')], true); ?>
						</div>

						<p><?php echo language::translate('description_scan_before_importing_translations', 'It is recommended to always scan your installation for unregistered translations before performing an import or export.'); ?></p>

						<?php echo functions::form_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
					</fieldset>

				<?php echo functions::form_end(); ?>
			</div>

			<div class="col-xl-6">
				<?php echo functions::form_begin('export_form', 'post'); ?>

					<fieldset>
						<legend><?php echo language::translate('title_export', 'Export'); ?></legend>

							<div class="form-group">
								<?php echo language::translate('title_collections', 'Collections'); ?>
								<?php echo functions::form_draw_select_multiple_field('collections[]', array_map(function($c) { return [$c['name'], $c['id']]; }, $collections), true); ?>
							</ul>

						<div class="form-group">
							<label><?php echo language::translate('title_languages', 'Languages'); ?></label>
							<?php echo functions::form_select_language('language_codes[]', true); ?></td>
						</div>

						<div class="row">
							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
								<?php echo functions::form_select('delimiter', [',' => ', ('. language::translate('text_default', 'default') .')', ';' => ';', "\t" => 'TAB', '|' => '|'], true); ?>
							</div>

							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
								<?php echo functions::form_select('enclosure', ['"' => '" ('. language::translate('text_default', 'default') .')'], true); ?>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6">
								<label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
								<?php echo functions::form_select('escapechar', ['"' => '" ('. language::translate('text_default', 'default') .')', '\\' => '\\'], true); ?>
							</div>

							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_charset', 'Charset'); ?></label>
								<?php echo functions::form_select_encoding('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-sm-6">
								<label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
								<?php echo functions::form_select('eol', ['Win', 'Mac', 'Linux'], true); ?>
							</div>

							<div class="form-group col-md-6">
								<label><?php echo language::translate('title_output', 'Output'); ?></label>
								<?php echo functions::form_select('output', ['screen' => language::translate('title_screen', 'Screen'), 'file' => language::translate('title_file', 'File')], true); ?>
							</div>
						</div>

						<?php echo functions::form_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
					</fieldset>

				<?php echo functions::form_end(); ?>
			</div>
		</div>
	</div>
</div>

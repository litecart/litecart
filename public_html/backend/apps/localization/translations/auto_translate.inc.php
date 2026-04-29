<?php

	document::$snippets['title'][] = t('title_auto_translate', 'Auto Translate');

	breadcrumbs::add(t('title_translations', 'Translations'));
	breadcrumbs::add(t('title_auto_translate', 'Auto Translate'));

	$collections = include 'app://backend/apps/localization/translations/collections.inc.php';

	$collections_by_entity = [];
	foreach ($collections as $collection) {
		$collections_by_entity[$collection['entity']] = $collection;
	}

	$translation_modules = new mod_translation();
	$translation_modules->load();

	$source_language_code = !empty($_POST['source_language_code']) ? $_POST['source_language_code'] : settings::get('default_language_code');
	$translate_mode = in_array($_POST['translate_mode'] ?? '', ['overwrite']) ? $_POST['translate_mode'] : 'missing';

	$build_translation_select = function($source_code, $target_codes, $where_sql) use ($translate_mode) {

		$selects = [
			"'translation' as entity",
			't.code',
			"coalesce(t.`text_". database::input($source_code) ."`, '') as source_text",
			't.html',
		];

		foreach ($target_codes as $target_language_code) {
			$selects[] = "coalesce(t.`text_". database::input($target_language_code) ."`, '') as `text_". database::input($target_language_code) ."`";
		}

		return (
			"select ". implode(', ', $selects) ."
			from ". DB_TABLE_PREFIX ."translations t
			where ". $where_sql
		);
	};

	$build_entity_select = function($collection, $source_code, $target_codes, $column) {

		$selects = [
			"'". database::input($collection['entity']) ."' as entity",
			"concat('[". database::input($collection['entity']) .":', e.id, ']". database::input($column) ."') as code",
			"coalesce(`source`.". database::input($column) .", '') as source_text",
			'0 as html',
		];

		foreach ($target_codes as $target_language_code) {
			$selects[] = "coalesce(`". database::input($target_language_code) ."`.". database::input($column) .", '') as `text_". database::input($target_language_code) ."`";
		}

		$joins = [
			"left join ". DB_TABLE_PREFIX . $collection['info_table'] ." `source` on (`source`.". database::input($collection['entity_column']) ." = e.id and `source`.language_code = '". database::input($source_code) ."')",
		];

		foreach ($target_codes as $target_language_code) {
			$joins[] = "left join ". DB_TABLE_PREFIX . $collection['info_table'] ." `". database::input($target_language_code) ."` on (`". database::input($target_language_code) ."`.". database::input($collection['entity_column']) ." = e.id and `". database::input($target_language_code) ."`.language_code = '". database::input($target_language_code) ."')";
		}

		return (
			"select ". implode(', ', $selects) ."
			from ". DB_TABLE_PREFIX . $collection['entity_table'] ." e
			". implode(PHP_EOL, $joins)
		);
	};

	$collect_batch_rows = function($selected_collection_ids, $source_code, $target_codes, $mode = 'missing') use ($collections, $build_translation_select, $build_entity_select) {

		$sql_union = [];

		if (in_array('translations', $selected_collection_ids)) {
			$sql_union[] = $build_translation_select($source_code, $target_codes, "code not regexp '^(settings_group:|settings_key:|cm|job|om|ot|pm|sm)_'");
		}

		if (in_array('modules', $selected_collection_ids)) {
			$sql_union[] = $build_translation_select($source_code, $target_codes, "code regexp '^(cm|job|om|ot|pm|sm)_'");
		}

		if (in_array('setting_groups', $selected_collection_ids)) {
			$sql_union[] = $build_translation_select($source_code, $target_codes, "code regexp '^settings_group:'");
		}

		if (in_array('settings', $selected_collection_ids)) {
			$sql_union[] = $build_translation_select($source_code, $target_codes, "code regexp '^settings_key:'");
		}

		foreach ($collections as $collection) {
			if (empty($collection['translatable'])) continue;
			if (!in_array($collection['id'], $selected_collection_ids)) continue;

			foreach ($collection['translatable'] as $column) {
				$sql_union[] = $build_entity_select($collection, $source_code, $target_codes, $column);
			}
		}

		if (empty($sql_union)) {
			throw new Exception(t('error_must_select_at_least_one_collection', 'You must select at least one collection'));
		}

		$rows = [];

		$translations_query = database::query(
			"select * from (
				". implode(PHP_EOL . PHP_EOL ."union ", $sql_union) ."
			) x
			where x.code != ''
			order by x.code;"
		);

		while ($translation = database::fetch($translations_query)) {
			if ($translation['source_text'] === '') continue;

			foreach ($target_codes as $target_language_code) {
				if ($mode === 'missing' && $translation['text_'. $target_language_code] !== '') continue;

				$rows[] = [
					'entity' => $translation['entity'],
					'code' => $translation['code'],
					'source_language_code' => $source_code,
					'target_language_code' => $target_language_code,
					'source_text' => $translation['source_text'],
					'html' => !empty($translation['html']),
				];
			}
		}

		return $rows;
	};

	$normalize_translation = function($result) use (&$normalize_translation) {

		if (is_string($result)) {
			return trim(html_entity_decode($result, ENT_QUOTES, 'UTF-8'));
		}

		if (!is_array($result)) {
			return null;
		}

		if (isset($result['data']['translations'][0]['translatedText'])) {
			return trim(html_entity_decode($result['data']['translations'][0]['translatedText'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($result[0]['translatedText'])) {
			return trim(html_entity_decode($result[0]['translatedText'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($result[0])) {
			return $normalize_translation($result[0]);
		}

		return null;
	};

	$store_translation = function($row, $translated_text) use ($collections_by_entity) {

		if ($row['entity'] == 'translation') {
			database::query(
				"update ". DB_TABLE_PREFIX ."translations
				set `text_". database::input($row['target_language_code']) ."` = '". database::input($translated_text, true) ."'
				where code = '". database::input($row['code']) ."'
				limit 1;"
			);

			return 'updated';
		}

		if (!preg_match('#^\[([a-z_]+):([0-9]+)\](.*)$#', $row['code'], $matches)) {
			throw new Exception('Could not decode entity translation code ('. $row['code'] .')');
		}

		list($entity, $id, $column) = array_slice($matches, 1);

		if (empty($collections_by_entity[$entity])) {
			throw new Exception('Unsupported entity ('. $entity .')');
		}

		$collection = $collections_by_entity[$entity];

		$translation_query = database::query(
			"select id from ". DB_TABLE_PREFIX . $collection['id'] ."
			where `". database::input($collection['entity_column']) ."` = '". database::input($id) ."'
			and language_code = '". database::input($row['target_language_code']) ."'
			limit 1;"
		);

		if ($translation = database::fetch($translation_query)) {
			database::query(
				"update ". DB_TABLE_PREFIX . $collection['id'] ."
				set `". database::input($column) ."` = '". database::input($translated_text, true) ."'
				where id = '". database::input($translation['id']) ."'
				limit 1;"
			);
		}

		database::query(
			"insert into ". DB_TABLE_PREFIX . $collection['id'] ."
			(`". database::input($collection['entity_column']) ."`, language_code, `". database::input($column) ."`)
			values ('". database::input($id) ."', '". database::input($row['target_language_code']) ."', '". database::input($translated_text, true) ."');"
		);

		return !empty($translation) ? 'updated' : 'inserted';
	};

	if (isset($_POST['translate']) || isset($_GET['resume'])) {

		try {

			ini_set('memory_limit', -1);

			ob_clean();

			header('Content-Type: text/plain; charset='. language::$selected['charset']);

			if (isset($_GET['resume'])) {

				if (empty(session::$data['auto_translate_batch'])) {
					throw new Exception('Missing translation batch to resume');
				}

				$batch = &session::$data['auto_translate_batch'];

				$completed = $batch['total_lines'] - count($batch['rows']);
				$progress = !empty($batch['total_lines']) ? round($completed / $batch['total_lines'] * 100, 2, PHP_ROUND_HALF_DOWN) : 100;
				$time_elapsed = round(microtime(true) - $batch['time_start'], 2);
				$time_remaining = ($progress > 0) ? round($time_elapsed / $progress * 100, 2) - $time_elapsed : 0;
				$memory_usage = round(memory_get_usage() / 1024 / 1024, 3);

				echo $progress .'% complete - Estimated time remaining: '. $time_remaining .' s - Memory usage: '. $memory_usage .' MB'. PHP_EOL . PHP_EOL;

			} else {

				$_POST['collections'] = !empty($_POST['collections'])
					? $_POST['collections']
					: array_column($collections, 'id');

				if (empty($_POST['source_language_code']) || empty(language::$languages[$_POST['source_language_code']])) {
					throw new Exception(t('error_must_select_a_language', 'You must select a language'));
				}

				if (empty($_POST['target_language_codes'])) {
					throw new Exception(t('error_must_select_at_least_one_language', 'You must select at least one language'));
				}

				$target_language_codes = array_values(array_unique(array_filter($_POST['target_language_codes'])));
				$target_language_codes = array_values(array_diff($target_language_codes, [$_POST['source_language_code']]));

				if (empty($target_language_codes)) {
					throw new Exception('You must select at least one target language different from the source language');
				}

				$rows = $collect_batch_rows($_POST['collections'], $_POST['source_language_code'], $target_language_codes, $_POST['translate_mode'] ?? 'missing');

				if (empty($rows)) {
					throw new Exception('No missing translations were found for the selected languages and collections');
				}

				echo 'Creating a batch of '. count($rows) .' translations for processing'. PHP_EOL . PHP_EOL;

				session::$data['auto_translate_batch'] = [
					'collections' => $_POST['collections'],
					'source_language_code' => $_POST['source_language_code'],
					'target_language_codes' => $target_language_codes,
					'time_start' => microtime(true),
					'rows' => $rows,
					'total_lines' => count($rows),
					'counters' => [
						'processed' => 0,
						'translated' => 0,
						'updated' => 0,
						'inserted' => 0,
					],
				];

				$batch = &session::$data['auto_translate_batch'];
			}

			$time_start = microtime(true);

			ignore_user_abort(true);

			echo 'Processing batch...'. PHP_EOL . PHP_EOL;

			while ($row = array_shift($batch['rows'])) {

				if (round(microtime(true) - $time_start) > 5) {
					array_unshift($batch['rows'], $row);
					echo PHP_EOL . 'Resuming '. number_format(count($batch['rows']), 0, '', ' ') .' remaining translations for processing...'. PHP_EOL . PHP_EOL;
					header('Refresh: 0; url='. document::link(null, ['resume' => 'true']));
					exit;
				}

				if (connection_aborted()) {
					throw new Exception('Connection aborted');
				}

				$batch['counters']['processed']++;

				$translation_result = $translation_modules->translate(
					$row['source_language_code'],
					$row['target_language_code'],
					[$row['code'] => $row['source_text']],
					!empty($row['html'])
				);

				if (is_array($translation_result) && array_key_exists($row['code'], $translation_result)) {
					$translated_text = trim($translation_result[$row['code']]);
				} else {
					$translated_text = $normalize_translation($translation_result);
				}

				if ($translated_text === null || $translated_text === '') {
					throw new Exception('No translation returned for '. $row['code'] .' ['. $row['target_language_code'] .']');
				}

				$result = $store_translation($row, $translated_text);

				$batch['counters']['translated']++;
				$batch['counters'][$result]++;

				echo '['. $batch['counters']['processed'] .'/'. $batch['total_lines'] .'] '
					. $row['code']
					. ' '
					. $row['source_language_code']
					. ' -> '
					. $row['target_language_code']
					. ' ('
					. $result
					. ')'
					. PHP_EOL;
			}

			$counters = $batch['counters'];

			unset(session::$data['auto_translate_batch']);

			cache::clear_cache();

			echo PHP_EOL . 'Completed!';

			notices::add('success', strtr(t('success_translated_n_entries', 'Translated %n entries'), ['%n' => $counters['translated']]));
			notices::add($counters['updated'] ? 'success' : 'notice', strtr(t('success_updated_n_existing_entries', 'Updated %n existing entries'), ['%n' => $counters['updated']]));
			notices::add($counters['inserted'] ? 'success' : 'notice', strtr(t('success_insert_n_new_entries', 'Inserted %n new entries'), ['%n' => $counters['inserted']]));

			header('Refresh: 5; url='. document::link(null, [], ['app', 'doc'], 'resume'));
			exit;

		} catch (Exception $e) {
			unset(session::$data['auto_translate_batch']);
			notices::add('errors', $e->getMessage());
			echo 'Error: '. $e->getMessage();
			header('Refresh: 5; url='. document::link(null, [], ['app', 'doc'], 'resume'));
			exit;
		}
	}

	$collection_options = f::array_each($collections, fn($collection) =>
		[$collection['id'], $collection['name']]
	);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_auto_translate', 'Auto Translate'); ?>
		</div>
	</div>

	<div class="card-body">
		<div style="width: 100%; max-width: 480px;">

			<?php echo f::form_begin('auto_translate_form', 'post'); ?>

				<label class="form-group">
					<dic class="form-labet"><?php echo t('title_collections', 'Collections'); ?></dic>
					<?php echo f::form_select('collections[]', $collection_options, true); ?>
				</label>

				<label class="form-group">
					<div class="<"><?php echo t('title_from_language', 'From Language'); ?></div>
					<?php echo f::form_select_language('source_language_code', true); ?>
				</label>

				<div class="form-group">
					<label><?php echo t('title_to_language', 'To Language'); ?></label>
					<?php echo f::form_select_language('target_language_codes[]', true); ?>
				</div>

				<div class="form-group">
					<label><?php echo t('title_translate_mode', 'Mode'); ?></label>
					<?php echo f::form_select('translate_mode', [
						'missing' => t('title_translate_mode_missing', 'Missing only — fill in blank translations'),
						'overwrite' => t('title_translate_mode_overwrite', 'Overwrite all — re-translate everything'),
					], $translate_mode); ?>
				</div>

				<?php echo f::form_button('translate', t('title_translate', 'Translate'), 'submit'); ?>

			<?php echo f::form_end(); ?>
		</div>
	</div>
</div>

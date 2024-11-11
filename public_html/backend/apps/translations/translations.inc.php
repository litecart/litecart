<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	if (empty($_GET['languages'])) {
		$all_languages = array_column(language::$languages, 'code');
		$defined_languages = [settings::get('site_language_code'), language::$selected['code'], settings::get('default_language_code')];
		$_GET['languages'] = array_slice(array_unique(array_merge($defined_languages, $all_languages)), 0, 2);
	}

	document::$snippets['title'][] = language::translate('title_translations', 'Translations');

	breadcrumbs::add(language::translate('title_translations', 'Translations'), document::ilink());

	$collections = include __DIR__.'/collections.inc.php';

	if (isset($_POST['save'])) {
		try {

			if (empty($_POST['translations'])) {
				throw new Exception('No translations to save');
			}

			foreach ($_POST['translations'] as $translation) {

				if (!in_array($translation['entity'], array_column($collections, 'entity'))) {
					throw new Exception('Unsupported entity ('. $translation['entity'] .')');
				}

				$collection = $collections[array_search($translation['entity'], array_column($collections, 'entity'))];

				if ($translation['entity'] == 'translation') {
					database::query(
						"update ". DB_TABLE_PREFIX ."translations
						set ". implode(', ' . PHP_EOL, array_map(function($language_code) use($translation) { return "text_$language_code = '". database::input($translation['text_'.$language_code], !empty($translation['html'])) ."'"; }, $_GET['languages'])) .",
							html = ". (!empty($translation['html']) ? 1 : 0) ."
						where code = '". database::input($translation['code']) ."'
						limit 1;"
					);

				} else {

					if (!preg_match('#^\[([a-z_]+):([0-9]+)\](.*)$#', $translation['code'], $matches)) {
						throw new Exception('Could not decode entity, id, and column from code');
					}

					list($entity, $id, $column) = array_slice($matches, 1);

					foreach ($_GET['languages'] as $language_code) {
						database::query(
							"insert into ". DB_TABLE_PREFIX . $collection['info_table'] ."
							(`". database::input($collection['entity_column']) ."`, language_code, `". database::input($column, !empty($translation['html'])) ."`)
							values ('". database::input($id) ."', '". database::input($language_code) ."', '". database::input($translation['text_'.$language_code]) ."')
							on duplicate key update
							$column = '". database::input($translation['text_'.$language_code], !empty($translation['html'])) ."';"
						);
					}
				}
			}

			notices::add('success', language::translate('title_changes_saved', 'Changes saved'));
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {
		try {

			if (empty($_POST['translations'])) {
				throw new Exception('No translations to delete');
			}

			foreach ($_POST['translations'] as $translation) {

				if ($translation['entity'] != 'translation') {
					throw new Exception('Cannot delete entity information');
				}

				database::query(
					"delete from ". DB_TABLE_PREFIX ."translations
					where code = '". database::input($translation['code']) ."'
					limit 1;"
				);
			}

			notices::add('success', language::translate('title_changes_saved', 'Changes saved'));
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Union select tables

	$sql_union = [];

	if (empty($_GET['collections']) || in_array('translations', $_GET['collections'])) {
		$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
									 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_GET['languages'])) ."
									 from ". DB_TABLE_PREFIX ."translations
									 where code not regexp '^(settings_group:|settings_key:|cm|job|om|ot|pm|sm)_'";
	}

	if (empty($_GET['collections']) || in_array('modules', $_GET['collections'])) {
		$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
									 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_GET['languages'])) ."
									 from ". DB_TABLE_PREFIX ."translations
									 where code regexp '^(cm|job|om|ot|pm|sm)_'";
	}

	if (empty($_GET['collections']) || in_array('setting_groups', $_GET['collections'])) {
		$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
									 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_GET['languages'])) ."
									 from ". DB_TABLE_PREFIX ."translations
									 where code regexp '^settings_group:'";
	}

	if (empty($_GET['collections']) || in_array('settings', $_GET['collections'])) {
		$sql_union[] = "select 'translation' as entity, frontend, backend, code, date_updated, html,
									 ". implode(", ", array_map(function($language_code) { return "`text_". database::input($language_code) ."`"; }, $_GET['languages'])) ."
									 from ". DB_TABLE_PREFIX ."translations
									 where code regexp '^settings_key:'";
	}

	$union_select = function($entity, $entity_table, $info_table, $id, $field) {
		return (
			"select '$entity' as entity, '1' as frontend, '1' as backend, concat('[$entity', ':', e.id, ']$field') as code, '' as date_updated,
				coalesce(". implode(', ', array_map(function($language_code) use($field) { return "if($language_code.$field regexp '<', 1, null)"; }, $_GET['languages'])) .", 0) as html,
				". implode(', ', array_map(function($language_code) use($field) { return "`". database::input($language_code) ."`.$field as `text_". database::input($language_code) ."`"; }, $_GET['languages'])) ."
			from ". DB_TABLE_PREFIX ."$entity_table e
			". implode(PHP_EOL, array_map(function($language_code) use($info_table, $id) { return "left join ". DB_TABLE_PREFIX ."$info_table `". database::input($language_code) ."` on (`". database::input($language_code) ."`.$id = e.id and `". database::input($language_code) ."`.language_code = '$language_code')"; }, $_GET['languages']))
		);
	};

	foreach ($collections as $collection) {
		if (empty($_GET['collections']) || in_array($collection['id'], $_GET['collections'])) {
			foreach ($collection['info_columns'] as $column) {
				$sql_union[] = $union_select($collection['entity'], $collection['entity_table'], $collection['info_table'], $collection['entity_column'], $column);
			}
		}
	}

	// Table Rows

	$translations = database::query(
		"select * from (
			". implode(PHP_EOL . PHP_EOL . "union ", $sql_union) ."
		) x
		where x.code != ''
		". ((!empty($_GET['endpoint']) && $_GET['endpoint'] == 'frontend') ? "and frontend = 1" : "") ."
		". ((!empty($_GET['endpoint']) && $_GET['endpoint'] == 'backend') ? "and backend = 1" : "") ."
		". (!empty($_GET['untranslated']) ? "and (". implode(" or ", array_map(function($language_code) { return "`text_$language_code` = ''"; }, $_GET['languages'])) .")" : "") ."
		". (!empty($_GET['query']) ? "and (code like '%". addcslashes(database::input($_GET['query']), '%_') ."%' or ". implode(' or ', array_map(function($language_code) { return "`text_". database::input($language_code) ."` like '%". addcslashes(database::input($_GET['query']), '%_') ."%'"; }, $_GET['languages'])) .")" : "") ."
		order by x.date_updated desc;"
	)->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

	// Reinsert post data
	if (!$_POST) {
		$_POST['translations'] = $translations;
	}

	$language_options = [['-- '. language::translate('title_select', 'Select') .' --', '']];
	foreach ($_GET['languages'] as $language_code) {
		$language_options[] = [$language_code, language::$languages[$language_code]['name']];
	}

	functions::draw_lightbox();
?>
<style>
#tokens .token {
	padding: .5em 1em;
	border-radius: var(--border-radius);
}
#tokens .token[data-name^="endpoint"] {
	background: #d6d4b4;
}
#tokens .token[data-name^="collections"] {
	background: #bcd6bc;
}
#tokens .token[data-name^="languages"] {
	background: #c2d3e3;
}
#tokens .token[data-name^="untranslated"] {
	background: #ddd;
}
#tokens .token + .token {
	margin-left: .5em;
}
#tokens .token .remove {
	margin-left: 1em;
	color: inherit;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_translations', 'Translations'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('filter_form', 'get'); ?>
		<div class="card-filter">
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>

			<div class="dropdown">

				<div class="form-select" data-toggle="dropdown">
					<?php echo language::translate('title_languages', 'Languages'); ?>
				</div>

				<ul class="dropdown-menu">
					<?php foreach (language::$languages as $language) { ?>
					<li>
						<label class="option"><?php echo functions::form_checkbox('languages[]', $language['code'], true); ?>
							<span class="title"><?php echo $language['name']; ?></span>
						</label>
					</li>
					<?php } ?>
				</ul>
			</div>

			<div class="dropdown">

				<div class="form-select" data-toggle="dropdown">
					<?php echo language::translate('title_endpoint', 'Endpoint'); ?>
				</div>

				<ul class="dropdown-menu">
					<li>
						<label class="option"><?php echo functions::form_checkbox('endpoint[]', 'frontend', true); ?>
							<span class="title"><?php echo language::translate('title_frontend', 'Frontend'); ?></span>
						</label>
					</li>
					<li>
						<label class="option"><?php echo functions::form_checkbox('endpoint[]', 'backend', true); ?>
							<span class="title"><?php echo language::translate('title_backend', 'Backend'); ?></span>
						</label>
					</li>
				</ul>
			</div>

			<div class="dropdown">

				<div class="form-select" data-toggle="dropdown">
					<?php echo language::translate('title_collections', 'Collections'); ?>
				</div>

				<ul class="dropdown-menu">
					<?php foreach ($collections as $collection) { ?>
					<li>
						<label class="option"><?php echo functions::form_checkbox('collections[]', $collection['id'], true); ?>
							<span class="title"><?php echo $collection['name']; ?></span>
						</label>
					</li>
					<?php } ?>
				</ul>
			</div>

			<div class="dropdown">

				<div class="form-select" data-toggle="dropdown">
					<?php echo language::translate('title_filters', 'Filters'); ?>
				</div>

				<ul class="dropdown-menu">
					<li>
						<label class="option"><?php echo functions::form_checkbox('untranslated', '1', true); ?>
							<span class="title"><?php echo language::translate('text_untranslated_only', 'Untranslated only'); ?></span>
						</label>
					</li>
				</ul>
			</div>

			<div>
				<?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?>
			</div>
		</div>
	<?php echo functions::form_end(); ?>

	<div class="card-body">
		<div id="tokens"></div>
	</div>

	<?php echo functions::form_begin('translations_form', 'post'); ?>

		<table class="table table-striped table-hover table-sortable data-table">
			<thead>
				<tr>
					<th style="width: 50px;"><?php echo functions::draw_fonticon('icon-check-square-o', 'data-toggle="checkbox-toggle"'); ?></th>
					<th data-sort="id"><?php echo language::translate('title_code', 'code'); ?></th>
					<?php foreach ($_GET['languages'] as $language_code) { ?><th><?php echo language::$languages[$language_code]['name']; ?></th><?php } ?>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($translations as $key => $translation) { ?>
				<tr>
					<td>
						<?php echo functions::form_checkbox('translations['.$key.'][checked]', $translation['code'], true, preg_match('#^\[#', $translation['code']) ? 'disabled' : ''); ?>
						<?php echo functions::form_input_hidden('translations['.$key.'][entity]', true); ?>
						<?php echo functions::form_input_hidden('translations['.$key.'][code]', true); ?>
					</td>
					<td>
						<pre><?php echo functions::escape_html($translation['code']); ?></pre>
						<small style="color: #999;"><?php echo functions::form_checkbox('translations['.$key.'][html]', '1', true); ?> <?php echo language::translate('text_html_enabled', 'HTML enabled'); ?></small>
					</td>
					<?php foreach ($_GET['languages'] as $language_code) { ?>
					<td><?php echo functions::form_textarea('translations['.$key.'][text_'. $language_code .']', true); ?></td>
					<?php } ?>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="8"><?php echo language::translate('title_translations', 'Translations'); ?>: <?php echo $num_rows; ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions" disabled>
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

				<ul class="list-inline">
					<li><?php echo functions::form_button_predefined('delete'); ?></li>
				</ul>
			</fieldset>
		</div>

		<div class="card-action">
			<?php echo functions::form_button('translator_tool', language::translate('title_translator_tool', 'Translator Tool'), 'button', 'class="btn btn-default translator-tool" data-toggle="lightbox" data-target="#translator-tool" data-width="980px"'); ?>
			<?php echo functions::form_button_predefined('save'); ?>
		</div>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<div id="translator-tool" style="display: none;">
	<h2><?php echo language::translate('title_translator_tool', 'Translator Tool'); ?></h2>

	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label><?php echo language::translate('title_from_language', 'From Language'); ?></label>
				<?php echo functions::form_select('from_language_code', $language_options, $_GET['languages'][0]); ?>
			</div>

			<div class="form-group">
				<label><?php echo language::translate('title_to_language', 'To Language'); ?></label>
				<?php echo functions::form_select('to_language_code', $language_options); ?>
			</div>

			<div class="form-group">
				<label><?php echo language::translate('text_copy_below_to_translation_service', 'Copy below to translation service'); ?></label>
				<textarea class="form-input" name="source" style="height: 320px;" readonly></textarea>
			</div>

			<div class="btn-group btn-block">
				<a class="btn btn-default" href="https://translate.google.com" target="_blank"><?php echo functions::draw_fonticon('icon-external-link'); ?> Google Translate</a>
				<a class="btn btn-default" href="https://www.bing.com/translator" target="_blank"><?php echo functions::draw_fonticon('icon-external-link'); ?> Bing Translate</a>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-group">
				<label><?php echo language::translate('text_paste_your_translated_result_below', 'Paste your translated result below'); ?></label>
				<textarea class="form-input" name="result" style="height: 455px;"></textarea>
			</div>

			<div>
				<button type="button" class="btn btn-primary" name="prefill_fields"><?php echo language::translate('title_prefill_fields', 'Prefill Fields'); ?></button>
			</div>
		</div>
	</div>
</div>

<script>

	$('form[name="filter_form"]').on('input', ':input', function() {
		$('#tokens').html('');

		$.each($('form[name="filter_form"] input[type="checkbox"]:checked, form[name="filter_form"] input[type="radio"]:checked'), function(i,el) {
			if (!$(this).val()) return;

			var $token = $('<span class="token"></span>');

			$token.attr('data-name', $(el).attr('name'))
				.attr('data-value', $(el).val())
				.text($(el).next('.title').text())
				.append('<a href="#" class="remove">×</a></span>');

			$('#tokens').append($token);
		});
	});

	$('form[name="filter_form"]').on('change', ':input', function() {
		$('form[name="filter_form"]').submit();
	});

	$('form[name="filter_form"] :input').first().trigger('input');

	$('#tokens').on('click', '.remove', function(e){

		e.preventDefault();
		var token = $(this).closest('.token');

		switch ($(':input[name="'+ $(token).data('name') +'"]').attr('type')) {

			case 'radio':
			case 'checkbox':
				$(':input[name="'+ $(token).data('name') +'"][value="'+ $(token).data('value') +'"]').prop('checked', false).trigger('input');
				break;

			case 'text':
			case 'search':
				$(':input[name="'+ $(token).data('name') +'"]').val('').trigger('input');
				break;
		}

		$('form[name="filter_form"]').submit();
	});


	$('textarea[name^="translations"]').on('input', function(){
		$(this).height('auto').height($(this).prop('scrollHeight') + 'px');
	}).trigger('input');

	// Translator Tool

	$('.data-table :checkbox').change(function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');


	$('#translator-tool select').change(function(e){

		var $modal = $(this).closest('.featherlight'),
			from_language_code = $modal.find('select[name="from_language_code"]').val(),
			to_language_code = $modal.find('select[name="to_language_code"]').val(),
			translations = [];

		if (!from_language_code || !to_language_code) return;

		$.each($(':input[name^="translations"][name$="[text_'+ from_language_code +']"]'), function(i){
			var source = $(this).val(),
				translation = $(this).closest('tr').find(':input[name^="translations"][name$="[text_'+ to_language_code +']"]').val();

			if (source && !translation) {
				translations.push('['+ i +'] = ' + source);
			}
		});

		translations = translations.join('\n');

		$modal.find(':input[name="source"]').val(translations).select();
	});

	$('#translator-tool :input[name="source"]').on('focus', function(e){
		$(this).select();
	});

	$('#translator-tool button[name="prefill_fields"]').on('click', function(){
		var $modal = $(this).closest('.featherlight'),
			 translated = $modal.find(':input[name="result"]').val().trim();

		translated = translated.split(/\n(?=\[[0-9]+\])/);

		if ($modal.find('select[name="to_language_code"]').val() == '') {
			alert('You must specify which language you are translating');
			return false;
		}

		$.each(translated, function(i){

			var matches = translated[i].trim().match(/^\[([0-9]+)\] = (.*)$/),
				index = matches[1],
				translation = matches[2].trim();

			$(':input[name$="[text_'+ $modal.find('select[name="to_language_code"]').val() +']"]:eq('+ index +')').val(translation).css('border', '1px solid #f00');
		});

		$.featherlight.close();
	});
</script>
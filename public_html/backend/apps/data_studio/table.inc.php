<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$tables = database::query(
		"show table status;"
	)->fetch_all('Name');

	if (empty($_GET['name'])) {
		$_GET['name'] = f::array_first($tables);
	}

	if (!in_array($_GET['name'], $tables)) {
		notices::add('errors', t('error_table_not_found', 'Table not found'));
		return;
	}

	document::$title[] = $_GET['name'];

	breadcrumbs::add(t('title_tables', 'Tables'), document::ilink(__APP__.'/tables'));
	breadcrumbs::add($_GET['name']);

	$columns = database::query(
		"show full columns from `". database::input($_GET['name']) ."`;"
	)->fetch_all(function($column) {
		return [
			'name' => $column['Field'],
			'type' => $column['Type'],
			'length' => preg_match('#\((.*?)\)#', $column['Type'], $matches) ? $matches[1] : '',
			'null' => preg_match('#^yes$#i', $column['Null']) ? true : false,
			'unsigned' => preg_match('#^unsigned$#i', $column['Type']) ? true : false,
			'zerofill' => preg_match('#^zerofill$#i', $column['Type']) ? true : false,
			'primary' => preg_match('#^pri$#i', $column['Key']) ? true : false,
			'key' => $column['Key'],
			'default' => $column['Default'],
			'auto_increment' => preg_match('#auto_increment#i', $column['Extra']) ? true : false,
			'collation' => $column['Collation'],
			'comment' => $column['Comment'],
		];
	});

	$primary_column = null;
	foreach ($columns as $column) {
		if ($column['primary']) {
			$primary_column = $column['name'];
			break;
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['rows']) || !is_array($_POST['rows'])) {
				throw new Exception(t('error_must_select_rows', 'You must select rows'));
			}

			foreach ($_POST['rows'] as $pkv) {
				database::query(
					"delete from `". database::input($_GET['name']) ."`
					where `". database::input($primary_column) ."` = '". database::input($pkv) ."'
					limit 1;"
				);
			}

			notices::add('notices', strtr(t('notice_n_rows_deleted', '%n rows deleted'), ['%n' => count($_POST['rows'])]));

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (empty($_POST['query'])) {
		$_POST['query'] = "select * from `". database::input($_GET['name']) ."`;";
	}

	// Table Rows
	$rows = database::query($_POST['query'])
		->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	$affected_rows = database::affected_rows();
	if (preg_match('#^\s*(INSERT|UPDATE|DELETE|ALTER) #i', $_POST['query'])) {
		notices::add('notices', strtr(t('notice_n_rows_affected', '%n rows affected'), ['%n' => $affected_rows]));
	}

	$table_options = array_map(function($table) {
	return [$table, $table];
	}, $tables);

/*
	$operator_options = [
		['='], ['!='], ['<'], ['<='], ['>'], ['>='],
		['LIKE'], ['NOT LIKE'], ['IN'], ['NOT IN'], ['FIND_IN_SET'], ['NOT FIND_IN_SET'],
		['IS NULL'], ['IS NOT NULL'], ['REGEXP'], ['NOT REGEXP'],
	];
*/

?>
<style>
.nav-pills .nav-link {
	padding: .25em .5em !important;
}

.card .columns {
	columns: 3;
	column-rule: 1px solid var(--default-border-color);
	column-gap: 2em;
}

.card table td {
	overflow-x: hidden;
	text-overflow: ellipsis;
	max-width: 250px;
}

.card table em {
	opacity: .5;
}

textarea[name="query"] {
	font-family: monospace;
}

.card-app table tr td:not(:first-child, :last-child) {
	border-inline-start: 1px solid var(--table-border-color);
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_table_data', 'Table Data'); ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><a class="btn btn-default" href="<?php echo document::href_ilink(__APP__.'/edit_table', ['name' => $_GET['name']]); ?>"><?php echo f::draw_fonticon('edit'); ?> <?php echo t('title_edit_table_structure', 'Edit Table Structure'); ?></a></li>
			<li><a class="btn btn-default" href="<?php echo document::href_ilink(__APP__.'/edit_row', ['table' => $_GET['name']]); ?>"><?php echo f::draw_fonticon('add'); ?> <?php echo t('title_create_new_row', 'Create New Row'); ?></a></li>
		</ul>
	</div>

	<?php f::form_begin('data_form', 'post'); ?>

		<div class="card-body">

			<div class="row">
				<div class="col-md-5">

					<label class="form-group">
						<div class="form-label"><?php echo t('title_select_table', 'Select Table'); ?></div>
						<?php echo f::form_select('table', $table_options, $_GET['name']); ?>
					</label>

					<?php echo f::form_begin('query_form', 'post', '', false, 'style="max-width: 100vw;"'); ?>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_query', 'Query'); ?></div>
						<?php echo f::form_textarea('query', true, 'style="min-height: 100px;" spellcheck="false"'); ?>
						</label>

						<div class="form-group">
							<?php echo f::form_button('run', t('title_run_query', 'Run Query'), 'submit', 'class="btn btn-success"'); ?>
							<?php echo f::form_button('pretty_print', t('title_pretty_print', 'Pretty Print'), 'button'); ?>
						</div>

					<?php echo f::form_end(); ?>

				</div>

				<?php if (!empty($columns)) { ?>
				<div class="col-md-7">
					<fieldset id="toggle-columns">
						<legend><?php echo t('title_toggle_columns', 'Toggle Columns'); ?></legend>
						<div class="columns">
						<?php foreach (array_slice($columns, 0, 10) as $column) echo f::form_checkbox('columns[]', [$column['name'], $column['name']], !empty($_POST['columns']) ? true : $column); ?>
						<?php foreach (array_slice($columns, 10) as $column) echo f::form_checkbox('columns[]', [$column['name'], $column['name']], true); ?>
						</div>
					</fieldset>
				</div>
				<?php } ?>
			</div>

		</div>

		<div style="overflow-x: auto;">
			<table class="table table-striped table-hover table-sortable data-table">
				<thead>
					<tr>
						<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
						<?php foreach ($columns as $column) {?>
						<th data-name="<?php echo f::escape_attr($column['name']); ?>" data-type="<?php echo f::escape_attr($column['type']); ?>" data-length="<?php echo f::escape_attr($column['length']); ?>" data-nullable="<?php echo f::escape_attr($column['null']); ?>" data-unsigned="<?php echo f::escape_attr($column['unsigned']); ?>" data-zerofill="<?php echo f::escape_attr($column['zerofill']); ?>" data-default="<?php echo f::escape_attr($column['default']); ?>"><?php echo f::escape_html($column['name']); ?></th>
						<?php } ?>
						<th class="main"></th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($rows as $row) { ?>
					<tr data-pkv="<?php echo f::escape_attr($row[$primary_column]); ?>">
						<td><?php echo f::form_checkbox('rows[]', $row[$primary_column]); ?></td>
						<?php foreach ($row as $column => $value) { ?>
						<td data-column-name="<?php echo f::escape_attr($column); ?>"><?php echo $value ? addcslashes(f::escape_html($value), "\t\r\n") : '<em>NULL</em>'; ?></td>
						<?php } ?>
						<td class="text-end">
							<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_row', ['table' => $_GET['name'], $primary_column => $row[$primary_column]]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
								<?php echo f::draw_fonticon('edit'); ?>
							</a>
						</td>
					</tr>
					<?php } ?>
				</tbody>

				<tfoot>
					<td colspan="<?php echo count($columns) + 2; ?>">
						<?php echo t('title_rows', 'Rows'); ?>: <?php echo f::format_number($num_rows); ?>
					</td>
				</tfoot>
			</table>
		</div>

		<?php if ($rows && in_array($primary_column, array_column($columns, 'name'))) { ?>
		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo t('text_with_selected', 'With selected'); ?></legend>

				<ul class="list-inline">
					<li><?php echo f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?></li>
				</ul>
			</fieldset>
		</div>
		<?php } ?>

	<?php echo f::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo f::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$('select[name="table"]').on('change', function() {
		window.location.href = '<?php echo document::ilink(__APP__.'/table'); ?>' + '?name=' + encodeURIComponent(this.value);
	});

	// Textarea auto-resize that respects CSS max-height
	const autosizeTextarea = function(el) {
		el.style.height = 'auto';
		const scrollH = el.scrollHeight;
		const cs = window.getComputedStyle(el);
		const maxH = cs.maxHeight === 'none' ? NaN : parseFloat(cs.maxHeight);
		if (Number.isNaN(maxH)) {
			el.style.height = scrollH + 'px';
			el.style.overflowY = 'hidden';
		} else {
			if (scrollH > maxH) {
				el.style.height = maxH + 'px';
				el.style.overflowY = 'auto';
			} else {
				el.style.height = scrollH + 'px';
				el.style.overflowY = 'hidden';
			}
		}
	};

	$('textarea[name="query"]').each(function() {
		autosizeTextarea(this);
	}).on('input', function(e) {
		autosizeTextarea(this);
	});

	// Toggle columns based on checkboxes in #toggle-columns
	const $toggles = $('#toggle-columns :input[type="checkbox"]');
	$toggles.on('change', function() {
		// Determine checkbox index among toggles
		const idx = $toggles.index(this) + 1; // +1 because first table column is the selection checkbox
		const show = $(this).is(':checked');
		// Header
		$('.data-table thead tr th').eq(idx).toggle(show);
		// Body cells
		$('.data-table tbody tr').each(function(){
			$(this).find('td').eq(idx).toggle(show);
		});
	});

	// Initialize columns: enable as many as fit in the wrapper without causing horizontal scroll
	(function initFitColumns(){
		const $table = $('.data-table');
		if (!$table.length) return;
		const $ths = $table.find('thead tr th');
		const wrapperWidth = $table.parent().width();
		// Always include first (selection) and last (actions) columns
		let total = 0;
		const firstCol = $ths.eq(0).outerWidth(true) || 0;
		const lastCol = $ths.eq($ths.length - 1).outerWidth(true) || 0;
		total += firstCol + lastCol;
		// iterate middle columns (these map to toggles)
		for (let i = 1; i < $ths.length - 1; i++) {
			const colWidth = $ths.eq(i).outerWidth(true) || 0;
			const toggleIndex = i - 1; // toggles correspond to th index-1
			if (total + colWidth <= wrapperWidth) {
				// enable this column
				$toggles.eq(toggleIndex).prop('checked', true).trigger('change');
				total += colWidth;
			} else {
				// disable remaining columns
				$toggles.eq(toggleIndex).prop('checked', false).trigger('change');
			}
		}
	})();

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change'); // Initial state

	$('button[name="pretty_print"]').on('click', function() {
		$.post('<?php echo document::ilink(__APP__.'/pretty_print'); ?>', {
			'csrf_token': '<?php echo session::csrf_token(); ?>',
			'query': $('form[name="query_form"] textarea[name="query"]').val(),
		}).then(function(response){
			$('form[name="query_form"] textarea[name="query"]').val(response);
		});
	});

	// Inline edit on double-click using header data-* metadata for input selection
	$('.data-table').on('dblclick', 'tbody tr td[data-column-name]', function(e) {
		const $td = $(this);
		const $tr = $td.closest('tr');
		const pkv = $tr.attr('data-pkv') || $tr.data('pkv') || $tr.attr('data-pk') || $tr.data('pk');

		if (typeof pkv === 'undefined') return;

		// Prevent multiple editors
		if ($td.find('input, textarea, select').length) return;

		const original = $td.text().trim();
		const colIndex = $td.index();
		const $th = $('.data-table thead tr th').eq(colIndex);
		const column = $th.data('name') || $td.data('column-name');
		const type = ($th.data('type') || '').toString().toLowerCase();
		const nullable = !!$th.data('nullable');
		const unsigned = !!$th.data('unsigned');
		const zerofill = !!$th.data('zerofill');
		const length = parseInt($th.data('length')) || 0;

		if (!column) return;

		// Choose input element based on type (switch for clarity)
		let $input;
		switch (true) {

			case /tinyint\(1\)/i.test(type):
				$input = $('<input type="checkbox">').addClass('form-check-input');
				$input.prop('checked', original === '1' || original.toLowerCase() === 'true');
				break;

			case /int|tinyint|smallint|mediumint|bigint/i.test(type):
				$input = $('<input class="form-input" type="number">');
				if (unsigned) $input.attr('min', 0);
				$input.val(original.replace(/[^0-9\-]/g, ''));
				break;

			case /decimal|float|double/i.test(type):
				$input = $('<input class="form-input" type="number">').attr('step', 'any');
				if (unsigned) $input.attr('min', 0);
				$input.val(original.replace(/[^0-9\.\-]/g, ''));
				break;

			case /text|blob/i.test(type):
				$input = $('<textarea class="form-input">');
				$input.val(original);
				break;

			default:
				if (length > 200) {
					$input = $('<textarea class="form-input">');
					$input.val(original);
				} else {
					$input = $('<input class="form-input" type="text">');
					$input.val(original);
				}
		}

		$td.empty().append($input);

		// focus the input (avoid unsupported ':number' pseudo-selector)
		$input.focus();

		const finalize = function(displayVal, fallback) {
			if (displayVal) $td.html(displayVal); else $td.text(fallback || original);
		};

		const save = function() {
			let sendVal;
			if ($input.is(':checkbox')) sendVal = $input.prop('checked') ? '1' : '0';
			else sendVal = $input.val();

			// Treat empty string as NULL only when column is nullable
			if (sendVal === '' && nullable) sendVal = '';

			$.post('<?php echo document::ilink(__APP__.'/edit_cell'); ?>', {
				'csrf_token': '<?php echo session::csrf_token(); ?>',
				'table': '<?php echo f::escape_attr($_GET['name'] ?? ''); ?>',
				'primary_column': '<?php echo f::escape_attr($primary_column); ?>',
				'pkv': pkv,
				'column': column,
				'value': sendVal,
				}).done(function(resp) {

					try {
						var result = JSON.parse(resp);
					} catch(e) {
						result = { error: resp };
					}

				if (result.success) {
					finalize(result.value, original);
				} else {
					alert(result.error || 'Update failed');
					finalize(null, original);
				}

			}).fail(function() {
				alert('Request failed');
				finalize(null, original);
			});
		};

		$input.on('blur', save).on('keydown', function(e){
			if (e.key === 'Enter' && $input.is(':text')) {
				e.preventDefault();
				$input.blur();
			}
			if (e.key === 'Escape') {
				$td.text(original);
			}
		});
	});
</script>

<?php

	if (!empty($_GET['name'])) {
		$table = new ent_database_table($_GET['name']);
	} else {
		$table = new ent_database_table();
	}

	if (!$_POST) {
		$_POST = $table->data;
	}

	document::$title[] = !empty($customer->data['id']) ? t('title_edit_table', 'Edit Table') : t('title_create_new_table', 'Create New Table');

	breadcrumbs::add(t('title_tables', 'Tables'), document::ilink(__APP__.'/tables'));
	breadcrumbs::add($table->previous['name'],  document::ilink(__APP__.'/table', ['name' => $table->previous['name']]));

	if (isset($_POST['save'])) {

		try {

			foreach ([
				'name',
				'auto_increment',
				'collation',
				'engine',
				'columns',
				'indexes',
			] as $field) {
				if (isset($_POST[$field])) {
					$table->data[$field] = $_POST[$field];
				}
			}

			$table->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/table', ['name' => $table->data['name']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {
			if (empty($table->previous['name'])) throw new Exception(t('error_must_provide_table', 'You must provide a table'));

			$table->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/tables'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$key_types = [
		'primary' => t('title_primary_key', 'Primary Key'),
		'key' => t('title_index_key', 'Index Key'),
		'unique' => t('title_unique_key', 'Unique Key'),
		'fulltext' => t('title_fulltext_index', 'Fulltext Index'),
	];

	$column_types = [
		[
			'label' => 'Common',
			'options' => [
				['int', 'int', 'data-length="11" data-unsigned=true data-default="0" data-collation=false'],
				['float', 'float', 'data-length="11,4" data-unsigned=true data-default="0" data-collation=false'],
				['varchar', 'varchar', 'data-length="65535" data-unsigned=false data-default="0" data-collation=true'],
				['text', 'text', 'data-length="65535" data-unsigned=false data-default="" data-collation=true'],
				['timestamp', 'timestamp', 'data-length="" data-unsigned=false data-default="" data-collation=false'],
				['enum', 'enum', 'data-length="\'a\',\'b\'" data-unsigned=false data-default="" data-collation=true'],
			],
		],
		[
			'label' => 'Alphanumerical',
			'options' => [
				['char', 'char', 'data-length="255" data-default="" data-collation=true'],
				['varchar', 'varchar', 'data-length="65535" data-default="" data-collation=true'],
				['text', 'text', 'data-length="65535" data-default="" data-collation=true'],
				['tinytext', 'tinytext', 'data-length="255" data-default="" data-collation=true'],
				['smalltext', 'smalltext', 'data-length="65535" data-default="" data-collation=true'],
				['mediumtext', 'mediumtext', 'data-length="" data-default="" data-collation=true'],
				['longtext', 'longtext', 'data-length="" data-default="" data-collation=true'],
			],
		],
		[
			'label' => 'Numerical',
			'options' => [
				['int', 'int', 'data-length="11" data-unsigned=true data-default="0" data-collation=false'],
				['float', 'float', 'data-length="11,4" data-max-size="4294967295" data-unsigned=true data-default="0" data-collation=false'],
				['double', 'double', 'data-length="22,8" data-unsigned=true data-default="0" data-collation=false'],
				['tinyint', 'tinyint', 'data-length="4" data-unsigned=true data-default="0" data-collation=false'],
				['smallint', 'smallint', 'data-length="6" data-unsigned=true data-default="0" data-collation=false'],
				['mediumint', 'mediumint', 'data-length="9" data-unsigned=true data-default="0" data-collation=false'],
				['bigint', 'bigint', 'data-length="20" data-unsigned=true data-default="0" data-collation=false'],
			],
		],
		[
			'label' => 'Date/Time',
			'options' => [
				['date', 'date', 'data-length="11" data-default="" data-unsigned=false'],
				['time', 'time', 'data-length="" data-default="" data-unsigned=false'],
				['year', 'year', 'data-length="" data-default="" data-unsigned=false'],
				['datetime', 'datetime', 'data-length="" data-default="" data-unsigned=false'],
				['timestamp', 'timestamp', 'data-length="" data-default="" data-unsigned=false'],
			],
		],
		[
			'label' => 'Binary',
			'options' => [
				['binary', 'binary', 'data-length="11" data-default="" data-unsigned=false'],
				['blob', 'blob', 'data-length="" data-default="65535" data-unsigned=false'],
				['varbinary', 'varbinary', 'data-length="65535" data-default="" data-unsigned=false'],
			],
		],
	];

?>
<style>
.card-app table td:not([class="main"]) {
	white-space: nowrap;
}

.card-app table.columns thead th,
.card-app table.columns tbody td {
	padding: .25em .5em;
}

.card-app table tfoot td {
	padding: 1em !important;
}

.card-app table td input,
.card-app table td select {
}

.card-app table tr td:not(:first-child) {
	border-inline-start: 1px solid var(--table-border-color);
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($table->previous['name']) ? t('title_edit_table', 'Edit Table') : t('title_create_new_table', 'Create New Table'); ?>
		</div>
	</div>

	<?php echo f::form_begin('closest_form', 'post'); ?>

		<div class="card-body">

			<div class="row">
				<div class="col-md-3">
					<h2><?php echo t('title_general', 'General'); ?></h2>

					<div class="">
						<div class="form-group">
							<label><?php echo t('title_name', 'Name'); ?></label>
							<?php echo f::form_input_text('name'); ?>
						</div>

						<div class="form-group">
							<label><?php echo t('title_auto_increment', 'Auto Increment'); ?></label>
							<?php echo f::form_input_number('auto_increment', true); ?>
						</div>

						<div class="form-group">
							<label><?php echo t('title_collation', 'Collation'); ?></label>
							<?php echo f::form_select_mysql_collation('collation', true); ?>
						</div>

						<div class="form-group">
							<label><?php echo t('title_engine', 'Engine'); ?></label>
							<?php echo f::form_select_mysql_engine('engine', true); ?>
						</div>
					</div>

				</div>

				<div class="col-md-9">
					<h2><?php echo t('title_indexes', 'Indexes'); ?></h2>

					<table class="indexes table table-striped table-hover table-sortable data-table">
						<thead>
							<tr>
								<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
								<th></th>
								<th><?php echo t('title_name', 'Name'); ?></th>
								<th><?php echo t('title_type', 'Type'); ?></th>
								<th class="main"><?php echo t('title_columns', 'Columns'); ?></th>
								<th><?php echo t('title_cardinality', 'Cardinality'); ?></th>
								<th class="main"><?php echo t('title_type', 'Type'); ?></th>
								<th></th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($_POST['indexes'] as $key => $index) { ?>
							<tr<?php echo ($index['kind'] == 'primary') ? ' style="font-weight: bold;"' : ''; ?>>
								<td><?php echo f::form_checkbox('selected_indexes[]', $key); ?></td>
								<td>
									<?php echo ($index['kind'] == 'primary') ? f::draw_fonticon('icon-key', 'style="color: #e5d72c;"') : ''; ?>
									<?php echo ($index['kind'] == 'unique') ? f::draw_fonticon('icon-key', 'style="color: #e52c2c;"') : ''; ?>
									<?php echo ($index['kind'] == 'key') ? f::draw_fonticon('icon-key', 'style="color: #7ce52c;"') : ''; ?>
									<?php echo ($index['kind'] == 'fulltext') ? f::draw_fonticon('icon-search', 'style="color: #2c7ce5;"') : ''; ?>
								</td>
								<td><?php echo $index['name']; ?></td>
								<td><?php echo $index['kind']; ?></td>
								<td><?php echo implode(', ', $index['columns']); ?></td>
								<td><?php echo $index['cardinality']; ?></td>
								<td><?php echo $index['type']; ?></td>
								<td class="text-end">
									<button class="btn btn-danger btn-sm" name="remove" value="true" type="button" title="<?php echo t('title_remove', 'Remove'); ?>"><?php echo f::draw_fonticon('icon-trash'); ?></button>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<table class="columns table table-striped table-hover table-dragable table-form data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th style="min-width: 150px;"><?php echo t('title_column', 'Column'); ?></th>
					<th style="min-width: 150px;"><?php echo t('title_type', 'Type'); ?></th>
					<th style="min-width: 35px;"><?php echo t('title_unsigned', 'Unsigned'); ?></th>
					<th style="min-width: 100px;"><?php echo t('title_length_set', 'Length/Set'); ?></th>
					<th style="min-width: 35px;"><?php echo t('title_nullable', 'Nullable'); ?></th>
					<th style="min-width: 150px;"><?php echo t('title_default', 'Default'); ?></th>
					<th style="min-width: 200px;"><?php echo t('title_collation', 'Collation'); ?></th>
					<th class="main"><?php echo t('title_comment', 'Comment'); ?></th>
					<th style="min-width: 10px;"><?php echo t('title_move', 'Move'); ?></th>
					<th style="min-width: 45px;"></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($_POST['columns'] as $column) { ?>
				<tr draggable="true">
					<td><?php echo f::form_checkbox('selected_columns[]', '1'); ?></td>
					<td>
						<?php echo ($column['key'] == 'PRI') ? f::draw_fonticon('icon-key', 'style="color: #e5d72c;"') : ''; ?>
						<?php echo ($column['key'] == 'UNI') ? f::draw_fonticon('icon-key', 'style="color: #e52c2c;"') : ''; ?>
						<?php echo ($column['key'] == 'MUL') ? f::draw_fonticon('icon-key', 'style="color: #7ce52c;"') : ''; ?>
					</td>
					<td><?php echo f::form_input_text('columns['.$column['name'].'][name]', true); ?></td>
					<td><?php echo f::form_select_optgroup('columns['.$column['name'].'][type]', $column_types, true); ?></td>
					<td class="text-center"><?php echo f::form_checkbox('columns['.$column['name'].'][unsigned]', '1', true); ?></td>
					<td><?php echo f::form_input_text('columns['.$column['name'].'][length]', true); ?></td>
					<td class="text-center"><?php echo f::form_checkbox('columns['.$column['name'].'][null]', '1', true); ?></td>
					<td><?php echo f::form_input_text('columns['.$column['name'].'][default]', true, ['list' => 'default-options']); ?></td>
					<td><?php echo f::form_select_mysql_collation('columns['.$column['name'].'][collation]', true); ?></td>
					<td><?php echo f::form_input_text('columns['.$column['name'].'][comment]', true); ?></td>
					<td class="grabbable text-center"><?php echo f::draw_fonticon('icon-arrows-vertical'); ?></td>
					<td class="text-end">
						<button class="btn btn-danger btn-sm" name="remove" value="true" type="button" title="<?php echo t('title_remove', 'Remove'); ?>">
							<?php echo f::draw_fonticon('icon-trash'); ?>
						</button>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<td colspan="99">
					<button class="btn btn-default" name="add_column" type="button">
						<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_column', 'Add Column'); ?>
					</button>
					<button class="btn btn-default" name="delete" type="button" data-require-columns="true">
						<?php echo f::draw_fonticon('icon-trash'); ?> <?php echo t('title_delete', 'Delete'); ?>
					</button>
					<button class="btn btn-default" name="add_primary_key" type="button" data-require-columns="true">
						<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_primary_key', 'Add Primary Key'); ?>
					</button>
					<button class="btn btn-default" name="add_key" type="button" data-require-columns="true">
						<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_key', 'Add Key'); ?>
					</button>
					<button class="btn btn-default" name="add_unique_key" type="button" data-require-columns="true">
						<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_unique_key', 'Add Unique Key'); ?>
					</button>
					<button class="btn btn-default" name="add_fulltext_key" type="button" data-require-columns="true">
						<?php echo f::draw_fonticon('add'); ?> <?php echo t('title_add_fulltext_key', 'Add Fulltext Key'); ?>
					</button>
				</td>
			</tfoot>
		</table>

		<div class="card-action">
			<?php echo f::form_button('save', t('title_save', 'Save'), 'submit', ['class' => 'btn btn-success'], 'save'); ?>
			<?php echo (!empty($table->previous['name'])) ? f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
			<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
		</div>

	<?php echo f::form_end(); ?>
</div>

<datalist id="default-options">
	<option value="NULL"></option>
	<option value="current_timestamp()"></option>
</datalist>

<script>
	$('table.columns select[name$="\[type\]"]').on('change', function(){
		let $tr = $(this).closest('tr');
		let $option = $(this).find('option:selected');
		$tr.find(':input[name$="\[collation\]"]').prop('disabled', !$option.data('collation'));
		$tr.find(':input[name$="\[unsigned\]"]').prop('disabled', !$option.data('unsigned'));
		$tr.find(':input[name$="\[default\]"]').attr('placeholder', $tr.find(':input[name$="\[null\]"]').is('checked') ? 'NULL' : $option.data('default'));
		$tr.find(':input[name$="\[length\]"]').attr('placeholder', $option.data('length'));
	});

	$('table.columns :input[name$="\[null\]"]').on('change', function(){
		let $tr = $(this).closest('tr');
		let $type = $tr.find('select[name$="\[type\]"] option:selected');
		$tr.find(':input[name$="\[default\]"]').attr('placeholder', $(this).is(':checked') ? 'NULL' : $type.data('default'));
	});

	$('table.columns select[name$="\[type\]"]').trigger('change');

	var new_column_key_i = 0; while ($('columns[new_'+ new_column_key_i +']').length) new_column_key_i++;
	$('table.columns button[name="add_column"]').on('click', function(){
		$row = $(
			'<tr draggable="true">' +
			'  <td><?php echo f::escape_js(f::form_checkbox('selected_columns[]', '1')); ?></td>' +
			'  <td></td>' +
			'  <td><?php echo f::escape_js(f::form_input_text('columns[new_key_i][name]', '')); ?></td>' +
			'  <td><?php echo f::escape_js(f::form_select_optgroup('columns[new_key_i][type]', $column_types, '')); ?></td>' +
			'  <td class="text-center"><?php echo f::escape_js(f::form_checkbox('columns[new_key_i][unsigned]', 'YES', '')); ?></td>' +
			'  <td><?php echo f::escape_js(f::form_input_text('columns[new_key_i][length]', '')); ?></td>' +
			'  <td class="text-center"><?php echo f::escape_js(f::form_checkbox('columns[new_key_i][null]', 'YES', '')); ?></td>' +
			'  <td><?php echo f::escape_js(f::form_input_text('columns[new_key_i][default]', '', ['list' => 'default-options'])); ?></td>' +
			'  <td><?php echo f::escape_js(f::form_select_mysql_collation('columns[new_key_i][collation]', '')); ?></td>' +
			'  <td><?php echo f::escape_js(f::form_input_text('columns[new_key_i][comment]', '')); ?></td>' +
			'  <td class="grabbable text-center"><?php echo f::draw_fonticon('icon-arrows-vertical'); ?></td>' +
			'  <td class="text-end">' +
			'    <button class="btn btn-danger btn-sm" name="remove" value="true" type="button" title="<?php echo f::escape_js(t('title_remove', 'Remove')); ?>"><?php echo f::escape_js(f::draw_fonticon('icon-trash')); ?></button>' +
			'  </td>' +
			'</tr>'
		).html(function(index,html){
			return html.replace(/new_column_key_i/, new_column_key_i++);
		});
		$('table.columns tbody').append($row);
		$('table.columns tbody tr:last select[name$="\[type\]"]').trigger('change');
	});

	$('table.columns').on('click', 'button[name="remove"]', function(){
		if (!window.confirm("<?php echo f::escape_js(t('text_are_you_sure', 'Are you sure?')); ?>")) return false;
		$(this).closest('tr').remove();
	});

	$('table.tfoot').on('click', 'button[name="delete"]', function(){
		if (!window.confirm("<?php echo f::escape_js(t('text_are_you_sure', 'Are you sure?')); ?>")) return false;
		$('table.columns tbody tr td:first-child :checkbox:checked').closest('tr').remove();
	});

	let new_index_key_i = 0; while ($('indexes[new_'+ new_index_key_i +']').length) new_index_key_i++;
	$('button[name="add_primary_key"], button[name="add_key"], button[name="add_unique_key"], button[name="add_fulltext_key"]').on('click', function(){
			var kind = $(this).attr('name').replace('add_', '').replace('_key', '');
			var name = prompt("What would you like to name this key?");
			if (!name) return;
			var $row = $([
				'<tr draggable="true">',
				'  <td><?php echo f::escape_js(f::form_checkbox('selected_columns[]', '')); ?></td>',
				'  <td>'+ name +'</td>',
				'  <td>'+ kind +'</td>',
				'  <td>'+ kind +'</td>',
				'  <td></td>',
				'  <td></td>',
				'  <td></td>',
				'  <td class="grabbable text-center"><?php echo f::draw_fonticon('icon-arrows-vertical'); ?></td>',
				'  <td class="text-end"><button class="btn btn-danger btn-sm" name="remove" value="true" type="button" title="<?php echo f::escape_js(t('title_remove', 'Remove')); ?>"><?php echo f::draw_fonticon('icon-trash'); ?></button></td>',
				'</tr>'
			].join('\n')
				.replace(/new_index_key_i/, new_index_key_i++));
			$('table.indexes tbody').append($row);
	});

	$('table.indexes').on('click', 'button[name="remove"]', function(){
		if (!window.confirm("<?php echo f::escape_js(t('text_are_you_sure', 'Are you sure?')); ?>")) return false;
		$(this).closest('tr').remove();
	});

	$('table.columns tr td:first-child :checkbox').on('change', function() {
		$('button[data-require-columns="true"]').prop('disabled', !$('table.columns tr td:first-child :checkbox:checked').length);
	}).first().trigger('change');
</script>

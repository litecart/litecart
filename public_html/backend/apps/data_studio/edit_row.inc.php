<?php

	$tables = database::query(
		"show table status;"
	)->fetch_all('Name');

	if (empty($_GET['table']) || !in_array($_GET['table'], $tables)) {
		notices::add('errors', t('error_table_not_found', 'Table not found'));
		return;
	}

	breadcrumbs::add(t('title_database', 'Database'), document::ilink(__APP__.'/tables'));
	breadcrumbs::add($_GET['table'], document::ilink(__APP__.'/table', ['name' => $_GET['table']]));
	breadcrumbs::add(t('title_edit_row', 'Edit Row'));

	$columns = database::query(
		"show full columns from `". database::input($_GET['table']) ."`;"
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

	if (!empty($_GET[$primary_column])) {
		$row = database::query(
			"select * from ". database::input($_GET['table']) ."
			where $primary_column = '". database::input($_GET[$primary_column]) ."'
			limit 1;"
		)->fetch();
	} else {
		$row = [];
		foreach ($columns as $column) {
			$row[$column['name']] = $column['default'];
		}
	}

	if (!$_POST) {
		$_POST = $row;
		foreach ($columns as $column) {
			$_POST['null'][$column['name']] = (!empty($column['null']) && !isset($_POST[$column['name']])) ? '1' : '0';
		}
	}

	if (isset($_POST['save'])) {

		try {

			$fields = array_column($columns, 'name');

			foreach ($fields as $field) {
				if (isset($_POST[$field])) $row[$field] = $_POST[$field];
				if (!empty($_POST['null'][$field])) $row[$field] = null;
			}

			$map_insert_values = function($key, $value){ return isset($value) ? "'". database::input($value) ."'" : "null"; };
			$map_update_values = function($key, $value){ return "`". database::input($key) ."` = ". (isset($value) ? "'". database::input($value) ."'" : "null"); };

			if (empty($_GET[$primary_column])) {
				database::query(
					"insert into `". database::input($_GET['table']) ."`
					(`". implode("`, `", database::input(array_keys($row))) ."`)
					values (". implode(", ", array_map($map_insert_values, array_keys($row), $row)) .");"
				);
			} else {
				database::query(
					"update `". database::input($_GET['table']) ."`
					set ". implode(", ", array_map($map_update_values, array_keys($row), $row)) ."
					where `". database::input($primary_column) ."` = '". database::input($_GET[$primary_column]) ."'
					limit 1;"
				);
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/table', ['name' => $_GET['table']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			database::query(
				"delete from `". database::input($_GET['table']) ."`
				where `". database::input($primary_column) ."` = '". database::input($row[$primary_column]) ."'
				limit 1;"
			);

			notices::add('success', t('success_row_deleted', 'Row deleted'));
			redirect(document::ilink(__APP__.'/table', ['name' => $_GET['table']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$draw_input_field = function($column) {
		switch (true) {

			case (preg_match('#^enum\((.*)\)$#', $column['type'], $matches)):
				$options = array_merge([''], preg_split('#\'\s*(?!\\\\),\s*\'#', trim($matches[1], "'"), -1, PREG_SPLIT_NO_EMPTY));
				return f::form_select($column['name'], $options, true);

			case (preg_match('#^((tiny|small|medium|big)?int|bytes)\(([0-9]+)\)( unsigned)?#i', $column['type'], $matches)):
				return f::form_input_number($column['name'], true, 'size="'. $matches[3] .'" maxlength="'. $matches[3] .'"' . (isset($matches[4]) ? ' min="0"' : ''));

			case (preg_match('#^(float|double|decimal)\(([0-9]+)?(,([0-9]+))?\)( unsigned)?#', $column['type'], $matches)):
				return f::form_input_decimal($column['name'], true, $matches[5], 'size="'. ($matches[3]+1) .'" maxlength="'. ($matches[3]+1) .'"' . (isset($matches[4]) ? ' min="0"' : ''));

			case (preg_match('#^(timestamp|datetime)$#', $column['type'])):
				return f::form_input_datetime($column['name'], true, 'size="19" maxlength="19"');

			case (preg_match('#^date$#', $column['type'])):
				return f::form_input_date($column['name'], true, 'size="10" maxlength="10"');

			case (preg_match('#^time$#', $column['type'])):
				return f::form_input_time($column['name'], true, 'size="8" maxlength="8"');

			default:
				if (preg_match('#\(([0-9]+)\)$#', $column['type'], $matches) && $matches[1] < 255)  {
					return f::form_input_text($column['name'], true, 'size="'. $matches[1] .'" maxlength="'. $matches[1] .'"');
				} else {
					return f::form_textarea($column['name'], true, 'style="max-height: 250px;"');
				}
		}
	};

?>
<style>
.card table input {
	text-align: inline-start;
}

.card table input[size],
.card table select {
	width: auto;
}
</style>

<div class="card">
	<div class="card-header">
		<h1 class="card-title">
			<?php echo f::draw_fonticon('edit'); ?> <?php echo (!empty($row[$primary_column])) ? t('title_edit_row', 'Edit Row') : t('title_create_new_row', 'Create New Row'); ?>
		</h1>
	</div>

	<?php echo f::form_begin('row_form', 'post'); ?>
		<table class="table table-striped table-hover table-sortable table-dragable data-table">
			<thead>
				<tr>
					<th><?php echo t('title_column_name', 'Column Name'); ?></th>
					<th><?php echo t('title_null', 'Null'); ?></th>
					<th class="main"><?php echo t('title_value', 'Value'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($columns as $column) { ?>
				<tr>
					<td><?php echo $column['name']; ?></td>
					<td><?php echo f::form_checkbox('null['.$column['name'].']', '1', true, empty($column['null']) ? 'disabled' : ''); ?></td>
					<td><?php echo $draw_input_field($column); ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<td colspan="3">
					<?php echo t('title_columns', 'Columns'); ?>: <?php echo count($columns); ?>
				</td>
			</tfoot>
		</table>

		<div class="card-action">
			<?php echo f::form_button('save', t('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
			<?php echo (!empty($row[$primary_column])) ? f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
			<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
		</div>

	<?php echo f::form_end(); ?>
</div>

<script>
	$('input[name^="null\["]').on('change', function(){
		$(this).closest('tr').find('td:last-child :input').prop('disabled', $(this).is(':checked'));
	}).trigger('change');

	// Textarea auto-resize
	$('textarea').on('input', function(e) {
		$(this).css('height', 'auto');
		$(this).css('height', (this.scrollHeight + 5) + 'px');
	});

	$('textarea').trigger('input'); // Initial resize
</script>
<?php

	$tables = database::query(
		"show table status;"
	)->fetch_all(function($table) {
		return [
			'name' => $table['Name'],
			'rows' => $table['Rows'],
			'engine' => $table['Engine'],
			'collation' => $table['Collation'],
			'comment' => $table['Comment'],
		];
	});

?>
<style>
.card table {
	/*font-family: Monospace;*/
}
.card table td:not([class="main"]) {
	white-space: nowrap;
}
.card table td input,
.card table td select {
	min-width: max-content;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_database', 'Database'); ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><a class="btn btn-default" href="<?php echo document::href_ilink(__APP__.'/edit_table'); ?>"><?php echo f::draw_fonticon('icon-plus'); ?> <?php echo t('title_create_new_table', 'Create New Table'); ?></a></li>
		</ul>
	</div>

		<table class="table table-striped table-hover table-sortable data-table">
			<thead>
				<tr>
					<th><?php echo f::draw_fonticon('icon-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo t('title_table_name', 'Table Name'); ?></th>
					<th class="main"><?php echo t('title_comment', 'Comment'); ?></th>
					<th><?php echo t('title_rows', 'Rows'); ?></th>
					<th><?php echo t('title_collation', 'Collation'); ?></th>
					<th><?php echo t('title_engine', 'Engine'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($tables as $table) { ?>
				<tr>
					<td><?php echo f::form_checkbox('tables[]', $table['name']); ?></td>
					<td>
						<a class="link" href="<?php echo document::href_ilink(__APP__.'/table', ['name' => $table['name']]); ?>">
							<?php echo f::draw_fonticon('icon-table'); ?> <?php echo $table['name']; ?>
						</a>
					</td>
					<td><?php echo f::escape_html($table['comment']); ?></td>
					<td class="text-center"><?php echo f::format_number($table['rows']); ?></td>
					<td><?php echo $table['collation']; ?></td>
					<td><?php echo $table['engine']; ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_table', ['name' => $table['name']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>">
							<?php echo f::draw_fonticon('icon-pencil'); ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<td colspan="10">
					<?php echo t('title_tables', 'Tables'); ?>: <?php echo f::format_number(count($tables)); ?>
				</td>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo t('text_with_selected', 'With selected'); ?></legend>

				<ul class="list-inline">
					<li><?php echo f::form_button('check', t('title_check', 'Check'), 'submit', '', 'icon-stethoscope'); ?></li>
					<li><?php echo f::form_button('repair', t('title_repair', 'Repair'), 'submit', '', 'icon-medkit'); ?></li>
					<li><?php echo f::form_button('truncate', t('title_truncate', 'Truncate'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?></li>
					<li><?php echo f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete'); ?></li>
				</ul>
			</fieldset>
		</div>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	});

	$('.data-table :checkbox').trigger('change'); // Initial state
</script>

<?php

	administrator::require_login();

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
	/* font-family: Monospace; */
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
			<?= $app_icon ?> <?= t('title_database', 'Database') ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><a class="btn btn-default" href="<?= document::href_ilink(__APP__.'/edit_table') ?>"><?= f::draw_fonticon('add') ?> <?= t('title_create_new_table', 'Create New Table') ?></a></li>
		</ul>
	</div>

		<table class="table table-striped table-hover table-sortable data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_table_name', 'Table Name') ?></th>
					<th class="main"><?= t('title_comment', 'Comment') ?></th>
					<th><?= t('title_rows', 'Rows') ?></th>
					<th><?= t('title_collation', 'Collation') ?></th>
					<th><?= t('title_engine', 'Engine') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($tables as $table) { ?>
				<tr>
					<td><?= f::form_checkbox('tables[]', $table['name']) ?></td>
					<td>
						<a class="link" href="<?= document::href_ilink(__APP__.'/table', ['name' => $table['name']]) ?>">
							<?= f::draw_fonticon('icon-table') ?> <?= $table['name'] ?>
						</a>
					</td>
					<td><?= f::escape_html($table['comment']) ?></td>
					<td class="text-center"><?= f::format_number($table['rows']) ?></td>
					<td><?= $table['collation'] ?></td>
					<td><?= $table['engine'] ?></td>
					<td class="text-end">
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_table', ['name' => $table['name']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<td colspan="10">
					<?= t('title_tables', 'Tables') ?>: <?= f::format_number(count($tables)) ?>
				</td>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?= t('text_with_selected', 'With selected') ?></legend>

				<ul class="list-inline">
					<li><?= f::form_button('check', t('title_check', 'Check'), 'submit', '', 'icon-stethoscope') ?></li>
					<li><?= f::form_button('repair', t('title_repair', 'Repair'), 'submit', '', 'icon-medkit') ?></li>
					<li><?= f::form_button('truncate', t('title_truncate', 'Truncate'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') ?></li>
					<li><?= f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(\''. t('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') ?></li>
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

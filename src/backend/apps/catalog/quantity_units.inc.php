<?php

	administrator::require_login();

	document::$title[] = t('title_quantity_units', 'Quantity Units');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_quantity_units', 'Quantity Units'), document::ilink(__APP__.'/quantity_units'));

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$quantity_units = database::prepare(
		"select qu.id, json_value(qu.name, '$.". database::input(language::$selected['code']) ."') as name,
			json_value(qu.description, '$.". database::input(language::$selected['code']) ."') as description
		from ". DB_PREFIX ."quantity_units qu
		order by qu.priority, name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_quantity_units', 'Quantity Units') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_quantity_unit'), t('title_create_new_unit', 'Create New Unit'), '', 'create') ?>
	</div>

	<?= f::form_begin('quantity_units_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th><?= t('title_name', 'Name') ?></th>
					<th class="main"><?= t('title_description', 'Description') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($quantity_units as $quantity_unit) { ?>
				<tr>
					<td><?= f::form_checkbox('quantity_units[]', $quantity_unit['id']) ?></td>
					<td><?= $quantity_unit['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_quantity_unit', ['quantity_unit_id' => $quantity_unit['id']]) ?>"><?= $quantity_unit['name'] ?></a></td>
					<td><?= $quantity_unit['description'] ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_quantity_unit', ['quantity_unit_id' => $quantity_unit['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_quantity_units', 'Quantity Units') ?>: <?= f::format_number($num_rows) ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

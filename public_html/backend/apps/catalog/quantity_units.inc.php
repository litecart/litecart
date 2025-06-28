<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_quantity_units', 'Quantity Units');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_quantity_units', 'Quantity Units'), document::ilink(__APP__.'/quantity_units'));

	// Table Rows, Total Number of Rows, Total Number of Pages
	$quantity_units = database::query(
		"select qu.id, json_value(qu.name, '$.". database::input(language::$selected['code']) ."') as name,
			json_value(qu.description, '$.". database::input(language::$selected['code']) ."') as description
		from ". DB_TABLE_PREFIX ."quantity_units qu
		order by qu.priority, name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_quantity_units', 'Quantity Units'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_quantity_unit'), t('title_create_new_unit', 'Create New Unit'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('quantity_units_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th><?php echo t('title_name', 'Name'); ?></th>
					<th class="main"><?php echo t('title_description', 'Description'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($quantity_units as $quantity_unit) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('quantity_units[]', $quantity_unit['id']); ?></td>
					<td><?php echo $quantity_unit['id']; ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_quantity_unit', ['quantity_unit_id' => $quantity_unit['id']]); ?>"><?php echo $quantity_unit['name']; ?></a></td>
					<td><?php echo $quantity_unit['description']; ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_quantity_unit', ['quantity_unit_id' => $quantity_unit['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_quantity_units', 'Quantity Units'); ?>: <?php echo language::number_format($num_rows); ?>
					</td>
				</tr>
			</tfoot>
		</table>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

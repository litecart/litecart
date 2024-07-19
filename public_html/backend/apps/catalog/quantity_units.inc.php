<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = language::translate('title_quantity_units', 'Quantity Units');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_quantity_units', 'Quantity Units'));

	// Table Rows, Total Number of Rows, Total Number of Pages
	$quantity_units = database::query(
		"select qu.id, qui.name, qui.description from ". DB_TABLE_PREFIX ."quantity_units qu
		left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qu.id = qui.quantity_unit_id and qui.language_code = '". database::input(language::$selected['code']) ."')
		order by qu.priority, qui.name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_quantity_units', 'Quantity Units'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_quantity_unit'), language::translate('title_create_new_unit', 'Create New Unit'), '', 'add'); ?>
	</div>

	<?php echo functions::form_begin('quantity_units_form', 'post'); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw', 'data-toggle="checkbox-toggle"'); ?></th>
					<th><?php echo language::translate('title_id', 'ID'); ?></th>
					<th><?php echo language::translate('title_name', 'Name'); ?></th>
					<th class="main"><?php echo language::translate('title_description', 'Description'); ?></th>
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
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_quantity_unit', ['quantity_unit_id' => $quantity_unit['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="5"><?php echo language::translate('title_quantity_units', 'Quantity Units'); ?>: <?php echo language::number_format($num_rows); ?></td>
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

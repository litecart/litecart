<?php

	document::$title[] = t('title_suppliers', 'Suppliers');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_suppliers', 'Suppliers'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$suppliers = database::query(
		"select id, name
		from ". DB_TABLE_PREFIX ."suppliers
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_suppliers', 'Suppliers'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_supplier'), t('title_create_new_supplier', 'Create New Supplier'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('suppliers_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th class="main"><?php echo t('title_name', 'Name'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($suppliers as $supplier) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('suppliers[]', $supplier['id']); ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_supplier', ['supplier_id' => $supplier['id']]); ?>"><?php echo $supplier['name']; ?></a></td>
					<td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_supplier', ['supplier_id' => $supplier['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_suppliers', 'Suppliers'); ?>: <?php echo language::number_format($num_rows); ?>
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

<?php

	administrator::require_login();

	document::$title[] = t('title_suppliers', 'Suppliers');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_suppliers', 'Suppliers'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Search Filter
	if (!empty($_GET['query'])) {
		$sql_find = [
			"id = '". database::input($_GET['query']) ."'",
			"name like '%". database::input($_GET['query']) ."%'",
		];
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$suppliers = database::prepare(
		"select id, code, name
		from ". DB_PREFIX ."suppliers
		where id
		". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_suppliers', 'Suppliers') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_supplier'), t('title_create_new_supplier', 'Create New Supplier'), '', 'create') ?>
	</div>

	<?= f::form_begin('search_form', 'get') ?>

		<div class="card-filter">
			<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></div>
			<?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?>
		</div>

	<?= f::form_end() ?>

	<?= f::form_begin('suppliers_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th><?= t('title_code', 'Code') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($suppliers as $supplier) { ?>
				<tr>
					<td><?= f::form_checkbox('suppliers[]', $supplier['id']) ?></td>
					<td><?= $supplier['id'] ?></td>
					<td><?= $supplier['code'] ?></td>
					<td>
						<a class="link" href="<?= document::href_ilink(__APP__.'/edit_supplier', ['supplier_id' => $supplier['id']]) ?>">
							<?= $supplier['name'] ?>
						</a>
					</td>
					<td>
						<a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_supplier', ['supplier_id' => $supplier['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>">
							<?= f::draw_fonticon('edit') ?>
						</a>
					</td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_suppliers', 'Suppliers') ?>: <?= f::format_number($num_rows) ?>
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

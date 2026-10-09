<?php

	administrator::require_login();

	document::$title[] = t('title_tax_classes', 'Tax Classes');

	breadcrumbs::add(t('title_localization', 'Localization'));
	breadcrumbs::add(t('title_tax_classes', 'Tax Classes'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$tax_classes = database::prepare(
		"select * from ". DB_PREFIX ."tax_classes
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_tax_classes', 'Tax Classes') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/tax/edit_tax_class'), t('title_create_new_tax_class', 'Create New Tax Class'), '', 'create') ?>
	</div>

	<?= f::form_begin('tax_classs_form', 'post') ?>

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
				<?php foreach ($tax_classes as $tax_class) { ?>
				<tr>
					<td><?= f::form_checkbox('tax_classes[]', $tax_class['id']) ?></td>
					<td><?= $tax_class['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/tax/edit_tax_class', ['tax_class_id' => $tax_class['id']]) ?>"><?= $tax_class['name'] ?></a></td>
					<td style="color: #999;"><?= $tax_class['description'] ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/tax/edit_tax_class', ['tax_class_id' => $tax_class['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_tax_classes', 'Tax Classes') ?>: <?= f::format_number($num_rows) ?>
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

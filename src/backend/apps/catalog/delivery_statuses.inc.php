<?php

	administrator::require_login();

	document::$title[] = t('title_delivery_statuses', 'Delivery Statuses');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_delivery_statuses', 'Delivery Statuses'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$delivery_statuses = database::query(
		"select ds.id, json_value(ds.name, '$.".database::input(language::$selected['code'])."') as name
		from ". DB_PREFIX ."delivery_statuses ds
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_delivery_statuses', 'Delivery Statuses') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_delivery_status'), t('title_create_new_status', 'Create New Status'), '', 'create') ?>
	</div>

	<?= f::form_begin('delivery_statuses_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th width="100%"><?= t('title_name', 'Name') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($delivery_statuses as $delivery_status) { ?>
				<tr>
					<td><?= f::form_checkbox('delivery_statuses[]', $delivery_status['id']) ?></td>
					<td><?= $delivery_status['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_delivery_status', ['delivery_status_id' => $delivery_status['id']]) ?>"><?= $delivery_status['name'] ?></a></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_delivery_status', ['delivery_status_id' => $delivery_status['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
				<td colspan="99">
					<?= t('title_delivery_statuses', 'Delivery Statuses') ?>: <?= f::format_number($num_rows) ?>
				</td>
			</tr>
		</table>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

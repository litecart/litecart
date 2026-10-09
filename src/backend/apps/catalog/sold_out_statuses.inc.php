<?php

	administrator::require_login();

	document::$title[] = t('title_sold_out_statuses', 'Sold-Out Statuses');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_sold_out_statuses', 'Sold-Out Statuses'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$sold_out_statuses = database::prepare(
		"select sos.id, sos.orderable, json_value(sos.name, '$.". database::input(language::$selected['code']) ."') as name
		from ". DB_PREFIX ."sold_out_statuses sos
		order by name asc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_sold_out_statuses', 'Sold Out Statuses') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_sold_out_status'), t('title_create_new_status', 'Create New Status'), '', 'create') ?>
	</div>

	<?= f::form_begin('sold_out_statuses_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_hidden', 'Hidden') ?></th>
					<th><?= t('title_orderable', 'Orderable') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($sold_out_statuses as $sold_out_status) { ?>
				<tr>
					<td><?= f::form_checkbox('delivery_statuses[]', $sold_out_status['id']) ?></td>
					<td><?= $sold_out_status['id'] ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_sold_out_status', ['sold_out_status_id' => $sold_out_status['id']]) ?>"><?= $sold_out_status['name'] ?></a></td>
					<td class="text-center"><?php if (!empty($sold_out_status['hidden'])) echo f::draw_fonticon('icon-check'); ?></td>
					<td class="text-center"><?php if (!empty($sold_out_status['orderable'])) echo f::draw_fonticon('icon-check'); ?></td>
					<td style="text-end"><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_sold_out_status', ['sold_out_status_id' => $sold_out_status['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_sold_out_statuses', 'Sold Out Statuses') ?>: <?= f::format_number($num_rows) ?>
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

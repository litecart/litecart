<?php

	administrator::require_login();

	document::$title[] = t('title_stock_transactions', 'Stock Transactions');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_stock_transactions', 'Stock Transactions'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$transactions = database::prepare(
		"select id, name, created_at
		from ". DB_PREFIX ."stock_transactions t
		where true
		". (!empty($_GET['query']) ? "t.name like '%". database::input($_GET['query']) ."%' or t.notes like '%". database::input($_GET['query']) ."%'" : "") ."
		". (!empty($_GET['query']) ? "and id in (
			select transaction_id from ". DB_PREFIX ."stock_transactions_contents
			where stock_item_id in (
				select id from ". DB_PREFIX ."stock_items si
				where json_value(si.name, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'
				or si.sku like '%". database::input($_GET['query']) ."%'
				or si.mpn like '%". database::input($_GET['query']) ."%'
				or si.gtin like '%". database::input($_GET['query']) ."%'
			)
		)" : "") ."
		". (!empty($_GET['date_from']) ? "and t.created_at >= '". date('Y-m-d H:i:s', strtotime($_GET['date_from'])) ."'" : '') ."
		". (!empty($_GET['date_to']) ? "and t.created_at <= '". date('Y-m-d H:i:s', strtotime($_GET['date_to'])) ."'" : '') ."
		order by created_at desc;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_stock_transactions', 'Stock Transactions') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_stock_transaction'), t('title_create_new_transaction', 'Create New Transaction'), '', 'create') ?>
	</div>

	<?= f::form_begin('search_form', 'get') ?>
		<div class="card-filter">

			<div class="expandable">
				<?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?>
			</div>

			<div class="input-group">
				<?= f::form_input_datetime('date_from', true, ['style' => 'width: 50%;']) ?>
				<span class="input-group-text">-</span>
				<?= f::form_input_datetime('date_to', true, ['style' => 'width: 50%;']) ?>
			</div>

			<div>
				<?= f::form_button('search', t('title_filter', 'Filter'), 'submit') ?>
			</div>

		</div>
	<?= f::form_end() ?>

	<?= f::form_begin('stock_transactions_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"') ?></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th class="text-end"><?= t('title_date', 'Date') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
			<?php foreach ($transactions as $transaction) { ?>
			<tr>
				<td><?= f::form_checkbox('stock_transactions[]', $transaction['id']) ?></td>
				<td><?= $transaction['id'] ?></td>
				<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_stock_transaction', ['transaction_id' => $transaction['id']]) ?>"><?= $transaction['name'] ?></a></td>
				<td class="text-end"><?= f::datetime_when($transaction['created_at']) ?></td>
				<td><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_stock_transaction', ['transaction_id' => $transaction['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
			</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_stock_transactions', 'Stock Transactions') ?>: <?= $num_rows ?>
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
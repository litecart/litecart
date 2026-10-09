<?php

	administrator::require_login();

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = t('title_stock_items', 'Stock Items');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_stock_items', 'Stock Items'), document::ilink());

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['stock_items'])) {
				throw new Exception(t('error_must_select_stock_items', 'You must select stock items'));
			}

			foreach ($_POST['stock_items'] as $stock_item_id) {
				$stock_item = new ent_stock_item($stock_item_id);
				$stock_item->delete();
			}

			notices::add('success', strtr(t('success_deleted_d_stock_items', 'Deleted {n} stock items'), [
				'{n}' => count($_POST['stock_items'])
			]));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_GET['query'])) {
		$sql_where_query = [
			"si.id = '". database::input($_GET['query']) ."'",
			"json_value(si.name, '$.". database::input($_GET['language_code']) ."') like '%". database::input($_GET['query']) ."%'",
			"si.sku regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
			"si.mpn regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
			"si.gtin regexp '^". database::input(implode('([ -\./]+)?', str_split(preg_replace('#[ -\./]+#', '', $_GET['query'])))) ."$'",
		];
	}

	// Table Rows, Total Number of Rows, Total Number of Pages
	$stock_items = database::prepare(
		"select si.*,
			json_value(si.name, '$.". database::input(language::$selected['code']) ."') as name,
			oi.quantity_reserved,
			stt.quantity_deposited,
			oit.quantity_withdrawn

		from ". DB_PREFIX ."stock_items si

		left join (
			select stock_item_id, sum(quantity_adjustment) as quantity_deposited
			from ". DB_PREFIX ."stock_transactions_contents
			group by stock_item_id
		) stt on (stt.stock_item_id = si.id)

		left join (
			select osi.stock_item_id, sum(oi.quantity * osi.quantity) as quantity_reserved
			from ". DB_PREFIX ."orders_stock_items osi
			left join ". DB_PREFIX ."orders_items oi on (oi.id = osi.item_id)
			where osi.order_id in (
				select id from ". DB_PREFIX ."orders o
				where order_status_id in (
					select id from ". DB_PREFIX ."order_statuses os
					where stock_action = 'reserve'
				)
			)
			group by osi.stock_item_id
		) oi on (oi.stock_item_id = si.id)

		left join (
			select osi.stock_item_id, sum(oi.quantity * osi.quantity) as quantity_withdrawn
			from ". DB_PREFIX ."orders_stock_items osi
			left join ". DB_PREFIX ."orders_items oi on (oi.id = osi.item_id)
			where osi.order_id in (
				select id from ". DB_PREFIX ."orders o
				where order_status_id in (
					select id from ". DB_PREFIX ."order_statuses os
					where stock_action = 'withdraw'
				)
			)
			group by osi.stock_item_id
		) oit on (oit.stock_item_id = si.id)

		where si.id
		". (!empty($sql_where_query) ? "and (". implode(" or ", $sql_where_query) .")" : "") ."
		order by si.sku, name;"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	foreach ($stock_items as $i => $stock_item) {
		if ($stock_item['quantity'] != $stock_item['quantity_deposited'] - $stock_item['quantity_withdrawn']) {
			$stock_items[$i]['warning'] = t('text_stock_inconsistency_detected', 'Stock inconsistency detected');
		}
	}

?>
<style>
.icon-exclamation-triangle {
	color: #f00;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<div class="card-title">
				<?= $app_icon ?> <?= t('title_stock_items', 'Stock Items') ?>
			</div>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_button_link(document::ilink(__APP__.'/edit_stock_item'), t('title_create_new_stock_item', 'Create New Stock Item'), '', 'create') ?>
	</div>

	<?= f::form_begin('search_form', 'get') ?>
	<div class="card-filter">
		<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_items', 'Search items')]) ?></div>
		<?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?>
	</div>
	<?= f::form_end() ?>

	<?= f::form_begin('stock_items_form', 'post') ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th class="text-center"><?= f::draw_fonticon('icon-square-check') ?></th>
					<th></th>
					<th><?= t('title_id', 'ID') ?></th>
					<th style="min-width: 52px;"></th>
					<th class="main"><?= t('title_name', 'Name') ?></th>
					<th><?= t('title_sku', 'SKU') ?></th>
					<th><?= t('title_gtin', 'GTIN') ?></th>
					<th><?= t('title_mpn', 'MPN') ?></th>
					<th><?= t('title_in_stock', 'In Stock') ?></th>
					<th><?= t('title_reserved', 'Reserved') ?></th>
					<th><?= t('title_backordered', 'Backordered') ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($stock_items as $stock_item) { ?>
				<tr>
					<td><?= f::form_checkbox('stock_items[]', $stock_item['id']) ?></td>
					<td><?php if (!empty($stock_item['warning'])) echo f::draw_fonticon('icon-exclamation-triangle', 'title="'. f::escape_attr($stock_item['warning']) .'"'); ?></td>
					<td><?= $stock_item['id'] ?></td>
					<td><?= f::draw_thumbnail('storage://images/' . ($stock_item['image'] ?: 'no_image.svg'), 64, 64, settings::get('product_image_clipping')) ?></td>
					<td><a class="link" href="<?= document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]) ?>"><?= $stock_item['name'] ?></a></td>
					<td><?= $stock_item['sku'] ?></td>
					<td><?= $stock_item['gtin'] ?></td>
					<td><?= $stock_item['mpn'] ?></td>
					<td class="text-end"><?= (float)$stock_item['quantity'] ?></td>
					<td class="text-end"><?= (float)$stock_item['quantity_reserved'] ?></td>
					<td class="text-end"><?= (float)$stock_item['backordered'] ?></td>
					<td><a class="btn btn-default btn-sm" href="<?= document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]) ?>" title="<?= t('title_edit', 'Edit') ?>"><?= f::draw_fonticon('edit') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?= t('title_stock_items', 'Stock Items') ?>: <?= $num_rows ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?= t('text_with_selected', 'With selected') ?>:
				</legend>

				<?= f::form_button_predefined('delete') ?>

			</fieldset>
		</div>

	<?= f::form_end() ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

<script>
	$('input[name="category_id"]').on('change', function(e) {
		$(this).closest('form').submit();
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>

<?php

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
	$stock_items = database::query(
		"select si.*,
			json_value(si.name, '$.". database::input(language::$selected['code']) ."') as name,
			oi.quantity_reserved,
			stt.quantity_deposited,
			oit.quantity_withdrawn

		from ". DB_TABLE_PREFIX ."stock_items si

		left join (
			select stock_item_id, sum(quantity_adjustment) as quantity_deposited
			from ". DB_TABLE_PREFIX ."stock_transactions_contents
			group by stock_item_id
		) stt on (stt.stock_item_id = si.id)

		left join (
			select oi.stock_item_id, sum(ol.quantity * oi.quantity) as quantity_reserved
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."orders_lines ol on (ol.id = oi.line_id)
			where oi.order_id in (
				select id from ". DB_TABLE_PREFIX ."orders o
				where order_status_id in (
					select id from ". DB_TABLE_PREFIX ."order_statuses os
					where stock_action = 'reserve'
				)
			)
			group by oi.stock_item_id
		) oi on (oi.stock_item_id = si.id)

		left join (
			select oi.stock_item_id, sum(ol.quantity * oi.quantity) as quantity_withdrawn
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."orders_lines ol on (ol.id = oi.line_id)
			where oi.order_id in (
				select id from ". DB_TABLE_PREFIX ."orders o
				where order_status_id in (
					select id from ". DB_TABLE_PREFIX ."order_statuses os
					where stock_action = 'withdraw'
				)
			)
			group by oi.stock_item_id
		) oit on (oi.stock_item_id = si.id)

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
				<?php echo $app_icon; ?> <?php echo t('title_stock_items', 'Stock Items'); ?>
			</div>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_stock_item'), t('title_create_new_stock_item', 'Create New Stock Item'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
	<div class="card-filter">
		<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. t('text_search_items', 'Search items').'"'); ?></div>
		<?php echo functions::form_button('filter', t('title_search', 'Search'), 'submit'); ?>
	</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('stock_items_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check'); ?></th>
					<th></th>
					<th><?php echo t('title_id', 'ID'); ?></th>
					<th style="min-width: 52px;"></th>
					<th class="main"><?php echo t('title_name', 'Name'); ?></th>
					<th><?php echo t('title_sku', 'SKU'); ?></th>
					<th><?php echo t('title_gtin', 'GTIN'); ?></th>
					<th><?php echo t('title_mpn', 'MPN'); ?></th>
					<th><?php echo t('title_in_stock', 'In Stock'); ?></th>
					<th><?php echo t('title_reserved', 'Reserved'); ?></th>
					<th><?php echo t('title_backordered', 'Backordered'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($stock_items as $stock_item) { ?>
				<tr>
					<td><?php echo functions::form_checkbox('stock_items[]', $stock_item['id']); ?></td>
					<td><?php if (!empty($stock_item['warning'])) echo functions::draw_fonticon('icon-exclamation-triangle', 'title="'. functions::escape_attr($stock_item['warning']) .'"'); ?></td>
					<td><?php echo $stock_item['id']; ?></td>
					<td><?php echo functions::draw_thumbnail('storage://images/' . ($stock_item['image'] ?: 'no_image.svg'), 64, 64, settings::get('product_image_clipping')); ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]); ?>"><?php echo $stock_item['name']; ?></a></td>
					<td><?php echo $stock_item['sku']; ?></td>
					<td><?php echo $stock_item['gtin']; ?></td>
					<td><?php echo $stock_item['mpn']; ?></td>
					<td class="text-end"><?php echo (float)$stock_item['quantity']; ?></td>
					<td class="text-end"><?php echo (float)$stock_item['quantity_reserved']; ?></td>
					<td class="text-end"><?php echo (float)$stock_item['backordered']; ?></td>
					<td><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="99">
						<?php echo t('title_stock_items', 'Stock Items'); ?>: <?php echo $num_rows; ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?php echo t('text_with_selected', 'With selected'); ?>:
				</legend>

				<?php echo functions::form_button_predefined('delete'); ?>

			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
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

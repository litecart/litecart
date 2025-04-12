<?php

	if (!isset($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) : null;
	$_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) : date('Y-m-d 23:59:59');

	if ($_GET['date_from'] > $_GET['date_to']) {
		list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];
	}

	$date_first_order = database::query(
		"select min(date_created)
		from ". DB_TABLE_PREFIX ."orders
		limit 1;"
	)->fetch('min(date_created)');

	if ($_GET['date_from'] < $date_first_order) {
		$_GET['date_from'] = $date_first_order;
	}

	if ($_GET['date_from'] > date('Y-m-d H:i:s')) {
		$_GET['date_from'] = date('Y-m-d H:i:s');
	}

	if ($_GET['date_to'] > date('Y-m-d H:i:s')) {
		$_GET['date_to'] = date('Y-m-d H:i:s');
	}

	$items = [];

	$timestamp_from = strtotime($_GET['date_from']);
	$timestamp_to = strtotime($_GET['date_to']);

	$result = database::query(
		"select
			oi.product_id, oi.quantity, oi.sku, oi.name,
			o.id as order_id, o.date_created as order_date_created,
			if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) as customer_name, o.customer_country_code, o.customer_email
		from ". DB_TABLE_PREFIX ."orders_items oi
		left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
		where o.order_status_id in (
			select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale
		)
		". (!empty($_GET['query']) ? "and (oi.product_id = '". database::input($_GET['query']) ."' or oi.sku like '". database::input($_GET['query']) ."' or oi.name like '%". addcslashes(database::input($_GET['query']), '%_') ."%')" : null) ."
		and o.date_created >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp_from), date('d', $timestamp_from), date('Y', $timestamp_from))) ."'
		and o.date_created <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp_to), date('d', $timestamp_to), date('Y', $timestamp_to))) ."'
		having quantity > 0
		order by oi.name asc, o.date_created desc, customer_name asc;"
	);

	if (isset($_GET['download'])) {

		$rows = $result->fetch_all();

		header('Content-Type: application/csv; charset='. language::$selected['charset']);
		header('Content-Disposition: filename="who_purchased_'. date('Ymd', strtotime($_GET['date_from'])) .'-'. date('Ymd', strtotime($_GET['date_to'])) .'.csv"');
		echo functions::csv_encode($rows);
		exit;
	}

	$rows = $result->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_who_purchased', 'Who Purchased?'); ?>
		</div>
	</div>

	<?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>

		<?php echo functions::form_draw_hidden_field('app'); ?>
		<?php echo functions::form_draw_hidden_field('doc'); ?>

		<div class="card-filter">

			<div class="expandable">
				<?php echo functions::form_draw_search_field('query', true, 'placeholder="'. functions::escape_html(language::translate('title_item_name_or_sku', 'Item Name or SKU')) .'"'); ?>
			</div>

			<div class="input-group">
				<?php echo functions::form_draw_month_field('date_from'); ?>
				<span class="input-group-text">-</span>
				<?php echo functions::form_draw_month_field('date_to'); ?>
			</div>

			<?php echo functions::form_draw_button('filter', language::translate('title_filter_now', 'Filter')); ?>

		</div>

		<div class="card-action">
			<?php echo functions::form_draw_button('download', functions::draw_fonticon('fa-download') .' '. language::translate('title_download', 'Download')); ?>
		</div>

	<?php echo functions::form_draw_form_end(); ?>

	<table class="table table-striped data-table">
		<thead>
			<tr>
				<th style="width: 0;"><?php echo language::translate('title_product_id', 'Product ID'); ?></th>
				<th><?php echo language::translate('title_product_name', 'Product Name'); ?></th>
				<th><?php echo language::translate('title_sku', 'SKU'); ?></th>
				<th><?php echo language::translate('title_customer_name', 'Customer Name'); ?></th>
				<th><?php echo language::translate('title_country', 'Country'); ?></th>
				<th><?php echo language::translate('title_email', 'Email'); ?></th>
				<th style="width: 0;"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
				<th style="width: 0;"><?php echo language::translate('title_purchase_date', 'Purchase Date'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($rows as $row) { ?>
			<tr>
				<td class="text-center"><?php echo $row['product_id']; ?></td>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo $row['sku']; ?></td>
				<td><a class="link" href="<?php echo document::link(null, ['app' => 'orders', 'doc' => 'edit_order', 'order_id' => (float)$row['order_id']], false); ?>"><?php echo $row['customer_name']; ?></a></td>
				<td><?php echo reference::country($row['customer_country_code'])->name; ?></td>
				<td><?php echo $row['customer_email']; ?></td>
				<td class="text-center"><?php echo (float)$row['quantity']; ?></td>
				<td><?php echo $row['order_date_created']; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>
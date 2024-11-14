<?php

	document::$title[] = language::translate('title_most_sold_products', 'Most Sold Products');

	breadcrumbs::add(language::translate('title_reports', 'Reports'));
	breadcrumbs::add(language::translate('title_most_sold_products', 'Most Sold Products'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : date('Y-01-01 00:00:00');
	$_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : date('Y-m-d');

	if ($_GET['date_from'] > $_GET['date_to']) {
		list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];
	}

	if ($_GET['date_from'] > date('Y-m-d')) {
		$_GET['date_from'] = date('Y-m-d');
	}

	if ($_GET['date_to'] > date('Y-m-d')) {
		$_GET['date_to'] = date('Y-m-d');
	}

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Table Rows
	$rows = [];

	$order_items_query = database::query(
		"select
			oi.product_id,
			oi.sku,
			oi.name,
			sum(oi.quantity) as total_quantity,
			sum(oi.price * oi.quantity * o.currency_value) as total_sales,
			sum(oi.tax * oi.quantity * o.currency_value) as total_tax
		from ". DB_TABLE_PREFIX ."orders_items oi
		left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
		where o.order_status_id in (
			select id from ". DB_TABLE_PREFIX ."order_statuses
			where is_sale
		)
		and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'
		and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'
		". (!empty($_GET['query']) ? "and (
			oi.product_id = '". database::input($_GET['query']) ."'
			or oi.name like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
			or oi.sku like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
			or oi.gtin like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
		)" : "") ."
		group by oi.product_id, oi.sku
		order by total_quantity desc;"
	);

	if (!isset($_GET['download']) && $_GET['page'] > 1) {
		database::seek($order_items_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));
	}

	$page_items = 0;
	while ($order_item = database::fetch($order_items_query)) {
		$rows[] = $order_item;
		if (!isset($_GET['download']) && ++$page_items == settings::get('data_table_rows_per_page')) break;
	}

	if (isset($_GET['download'])) {
		header('Content-Type: application/csv; charset='. mb_http_output());
		header('Content-Disposition: filename="most_sold_products_'. date('Ymd', strtotime($_GET['date_from'])) .'-'. date('Ymd', strtotime($_GET['date_to'])) .'.csv"');
		echo functions::csv_encode($rows);
		exit;
	}

	// Number of Rows
	$num_rows = database::num_rows($order_items_query);

	// Pagination
	$num_pages = ceil($num_rows / settings::get('data_table_rows_per_page'));
?>

<style>
form[name="filter_form"] li {
	vertical-align: middle;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_most_sold_products', 'Most Sold Products'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_begin('filter_form', 'get'); ?>
			<ul class="list-inline">
				<li><?php echo functions::form_input_search('query', true, 'placeholder="'. functions::escape_attr(language::translate('title_item_name_or_sku', 'Item Name or SKU')) .'"'); ?></li>
				<li>
					<div class="input-group" style="max-width: 380px;">
						<?php echo functions::form_input_date('date_from', true); ?>
						<span class="input-group-text"> - </span>
						<?php echo functions::form_input_date('date_to', true); ?>
					</div>
				</li>
				<li><?php echo functions::form_button('filter', language::translate('title_filter_now', 'Filter')); ?></li>
				<li><?php echo functions::form_button('download', language::translate('title_download', 'Download')); ?></li>
			</ul>
		<?php echo functions::form_end(); ?>
	</div>

	<table class="table table-striped table-hover data-table">
		<thead>
			<tr>
				<th class="main"><?php echo language::translate('title_product', 'Product'); ?></th>
				<th class="text-center"><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
				<th class="text-center"><?php echo language::translate('title_sales', 'Sales'); ?></th>
				<th class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($rows as $row) { ?>
			<tr>
				<td><?php echo $row['name']; ?></td>
				<td class="text-center border-start"><?php echo (float)$row['total_quantity']; ?></td>
				<td class="text-end border-start"><?php echo currency::format($row['total_sales'], false, settings::get('store_currency_code')); ?></td>
				<td class="text-end border-start"><?php echo currency::format($row['total_tax'], false, settings::get('store_currency_code')); ?></td>
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

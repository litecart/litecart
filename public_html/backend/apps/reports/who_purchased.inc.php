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
		"select min(created_at)
		from ". DB_TABLE_PREFIX ."orders
		limit 1;"
	)->fetch('min(created_at)');

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

	$timestamp_from = strtotime($_GET['date_from'] ?? '');
	$timestamp_to = strtotime($_GET['date_to'] ?? '');

	$result = database::query(
		"select
			ol.product_id, ol.quantity, ol.code, ol.name,
			o.id as order_id, o.created_at as order_created_at,
			if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) as customer_name, o.customer_country_code, o.customer_email
		from ". DB_TABLE_PREFIX ."orders_lines ol
		left join ". DB_TABLE_PREFIX ."orders o on (o.id = ol.order_id)
		where o.order_status_id in (
			select id from ". DB_TABLE_PREFIX ."order_statuses where is_sale
		)
		". (!empty($_GET['query']) ? "and (ol.product_id = '". database::input($_GET['query']) ."' or ol.code like '". database::input($_GET['query']) ."' or ol.name like '%". addcslashes(database::input($_GET['query']), '%_') ."%')" : null) ."
		and o.created_at >= '". date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $timestamp_from), date('d', $timestamp_from), date('Y', $timestamp_from))) ."'
		and o.created_at <= '". date('Y-m-d H:i:s', mktime(23, 59, 59, date('m', $timestamp_to), date('d', $timestamp_to), date('Y', $timestamp_to))) ."'
		having quantity > 0
		order by ol.name asc, o.created_at desc, customer_name asc;"
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
			<?php echo $app_icon; ?> <?php echo t('title_who_purchased', 'Who Purchased?'); ?>
		</div>
	</div>

	<?php echo functions::form_begin('filter_form', 'get'); ?>

		<?php echo functions::form_input_hidden('app'); ?>
		<?php echo functions::form_input_hidden('doc'); ?>

		<div class="card-filter">

			<div class="expandable">
				<?php echo functions::form_input_search('query', true, 'placeholder="'. functions::escape_html(t('text_item_name_or_code', 'Item name or code')) .'"'); ?>
			</div>

			<div class="input-group" style="width: 450px;">
				<?php echo functions::form_input_month('date_from'); ?>
				<span class="input-group-text">-</span>
				<?php echo functions::form_input_month('date_to'); ?>
			</div>

			<?php echo functions::form_button('filter', ['true', functions::draw_fonticon('icon-funnel') .' '. t('title_filter_now', 'Filter')]); ?>

		</div>

		<div class="card-action">
			<?php echo functions::form_button('download', ['true', functions::draw_fonticon('icon-download') .' '. t('title_download', 'Download')]); ?>
		</div>

	<?php echo functions::form_end(); ?>

	<table class="table table-striped data-table">
		<thead>
			<tr>
				<th style="width: 0;"><?php echo t('title_product_id', 'Product ID'); ?></th>
				<th><?php echo t('title_product_name', 'Product Name'); ?></th>
				<th><?php echo t('title_code', 'Code'); ?></th>
				<th><?php echo t('title_customer_name', 'Customer Name'); ?></th>
				<th><?php echo t('title_country', 'Country'); ?></th>
				<th><?php echo t('title_email', 'Email'); ?></th>
				<th style="width: 0;"><?php echo t('title_quantity', 'Quantity'); ?></th>
				<th style="width: 0;"><?php echo t('title_purchase_date', 'Purchase Date'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($rows as $row) { ?>
			<tr>
				<td class="text-center"><?php echo $row['product_id']; ?></td>
				<td><?php echo $row['name']; ?></td>
				<td><?php echo $row['code']; ?></td>
				<td><a class="link" href="<?php echo document::link(null, ['app' => 'orders', 'doc' => 'edit_order', 'order_id' => (float)$row['order_id']], false); ?>"><?php echo $row['customer_name']; ?></a></td>
				<td><?php echo reference::country($row['customer_country_code'])->name; ?></td>
				<td><?php echo $row['customer_email']; ?></td>
				<td class="text-center"><?php echo (float)$row['quantity']; ?></td>
				<td><?php echo $row['order_created_at']; ?></td>
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

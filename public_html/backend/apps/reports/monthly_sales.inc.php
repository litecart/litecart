<?php

	document::$title[] = language::translate('title_monthly_sales', 'Monthly Sales');

	breadcrumbs::add(language::translate('title_reports', 'Reports'));
	breadcrumbs::add(language::translate('title_monthly_sales', 'Monthly Sales'), document::ilink());

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

		// Table Rows
	$rows = database::query(
		"select
			group_concat(o.id) as order_ids,
			sum(o.total * o.currency_value) - sum(o.total_tax * o.currency_value) as total_sales,
			sum(o.total_tax * o.currency_value) as total_tax,
			sum(o.subtotal * o.currency_value) as total_subtotal,
			sum(otsf.amount * o.currency_value) as total_shipping_fees,
			sum(otpf.amount * o.currency_value) as total_payment_fees,
			date_format(o.date_created, '%Y-%m') as `year_month`
		from ". DB_TABLE_PREFIX ."orders o
		left join (
			select order_id, sum(amount) as amount from ". DB_TABLE_PREFIX ."orders_totals
			where module_id = 'ot_shipping_fee'
			group by order_id
		) otsf on (o.id = otsf.order_id)
		left join (
			select order_id, sum(amount) as amount from ". DB_TABLE_PREFIX ."orders_totals
			where module_id = 'ot_payment_fee'
			group by order_id
		) otpf on (o.id = otpf.order_id)
		where o.order_status_id in (
			select id from ". DB_TABLE_PREFIX ."order_statuses
			where is_sale
		)
		". (!empty($_GET['date_from']) ? "and o.date_created >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'" : "") ."
		". (!empty($_GET['date_to']) ? "and o.date_created <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'" : "") ."
		group by date_format(o.date_created, '%Y-%m')
		order by `year_month` desc;"
	)->fetch_all();

	$total = array_fill_keys(['total_sales', 'total_tax', 'total_subtotal', 'total_shipping_fees', 'total_payment_fees'], 0);

	foreach (array_keys($total) as $key) {
		if (isset($total[$key])) {
			$total[$key] = array_sum(array_column($rows, $key));
		}
	}

	if (isset($_GET['download'])) {
		header('Content-Type: application/csv; charset='. mb_http_output());
		header('Content-Disposition: filename="monthly_sales_'. date('Ymd', strtotime($_GET['date_from'])) .'-'. date('Ymd', strtotime($_GET['date_to'])) .'.csv"');
		echo functions::csv_encode($rows);
		exit;
	}
?>
<style>
form[name="filter_form"] li {
	vertical-align: middle;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_monthly_sales', 'Monthly Sales'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_begin('filter_form', 'get'); ?>
			<ul class="list-inline">
				<li>
					<div class="input-group" style="max-width: 380px;">
						<?php echo functions::form_input_date('date_from'); ?>
						<span class="input-group-text"> - </span>
						<?php echo functions::form_input_date('date_to'); ?>
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
				<th width="100%"><?php echo language::translate('title_month', 'Month'); ?></th>
				<th class="border-start text-center"><?php echo language::translate('title_subtotal', 'Subtotal'); ?></th>
				<th class="border-start text-center"><?php echo language::translate('title_shipping_fees', 'Shipping Fees'); ?></th>
				<th class="border-start text-center"><?php echo language::translate('title_payment_fees', 'Payment Fees'); ?></th>
				<th class="border-start text-center"><?php echo language::translate('title_total', 'Total'); ?></th>
				<th class="text-center"><?php echo language::translate('title_tax', 'Tax'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($rows as $row) { ?>
			<tr>
				<td><?php echo ucfirst(language::strftime('%B, %Y', strtotime($row['year_month'].'-01'))); ?></td>
				<td class="border-start text-end"><?php echo currency::format($row['total_subtotal'], false, settings::get('store_currency_code')); ?></td>
				<td class="border-start text-end"><?php echo currency::format($row['total_shipping_fees'], false, settings::get('store_currency_code')); ?></td>
				<td class="border-start text-end"><?php echo currency::format($row['total_payment_fees'], false, settings::get('store_currency_code')); ?></td>
				<td class="border-start text-end"><strong><?php echo currency::format($row['total_sales'], false, settings::get('store_currency_code')); ?></strong></td>
				<td class="text-end"><?php echo currency::format($row['total_tax'], false, settings::get('store_currency_code')); ?></td>
			</tr>
			<?php } ?>
		</tbody>

		<?php if (!empty($total)) { ?>
		<tfoot>
			<tr>
				<td class="text-end"><?php echo strtoupper(language::translate('title_total', 'Total')); ?></td>
				<td class="border-start text-end"><?php echo currency::format($total['total_subtotal'], false, settings::get('store_currency_code')); ?></td>
				<td class="border-start text-end"><?php echo currency::format($total['total_shipping_fees'], false, settings::get('store_currency_code')); ?></td>
				<td class="border-start text-end"><?php echo currency::format($total['total_payment_fees'], false, settings::get('store_currency_code')); ?></td>
				<td class="border-start text-end"><strong><?php echo currency::format($total['total_sales'], false, settings::get('store_currency_code')); ?></strong></td>
				<td class="text-end"><?php echo currency::format($total['total_tax'], false, settings::get('store_currency_code')); ?></td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>
</div>

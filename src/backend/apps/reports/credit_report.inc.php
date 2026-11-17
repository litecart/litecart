<?php

	administrator::require_login();

	document::$title[] = t('title_credit_report', 'Credit Report');

	breadcrumbs::add(t('title_reports', 'Reports'));
	breadcrumbs::add(t('title_credit_report', 'Credit Report'), document::ilink());

	// An empty date_from means no lower bound, showing all outstanding credit orders
	$date_from_input = isset($_GET['date_from']) && is_string($_GET['date_from']) ? trim($_GET['date_from']) : '';
	$date_to_input = isset($_GET['date_to']) && is_string($_GET['date_to']) ? trim($_GET['date_to']) : '';

	$_GET['date_from'] = ($date_from_input !== '' && strtotime($date_from_input) !== false) ? date('Y-m-d', strtotime($date_from_input)) : '';
	$_GET['date_to'] = ($date_to_input !== '' && strtotime($date_to_input) !== false) ? date('Y-m-d', strtotime($date_to_input)) : date('Y-m-d');

	if ($_GET['date_from'] !== '' && $_GET['date_from'] > $_GET['date_to']) {
		list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];
	}

	if ($_GET['date_to'] > date('Y-m-d')) {
		$_GET['date_to'] = date('Y-m-d');
	}

	if ($_GET['date_from'] !== '' && $_GET['date_from'] > $_GET['date_to']) {
		$_GET['date_from'] = $_GET['date_to'];
	}

	// Unpaid credit account orders within the selected period
	$orders = database::query(
		"select id, customer_id, customer_firstname, customer_lastname, customer_email, total, total_tax, created_at
		from ". DB_PREFIX ."orders
		where order_status_id in (
			select id from ". DB_PREFIX ."order_statuses
			where is_sale
		)
		and payment_transaction_id = '### CREDIT ACCOUNT - Pending ###'
		and paid_at is null
		". ($_GET['date_from'] !== '' ? "and created_at >= '". database::input(date('Y-m-d 00:00:00', strtotime($_GET['date_from']))) ."'" : "") ."
		". (!empty($_GET['date_to']) ? "and created_at <= '". database::input(date('Y-m-d 23:59:59', strtotime($_GET['date_to']))) ."'" : "") ."
		order by customer_id asc, created_at asc, id asc;"
	)->fetch_all();

	// Group the orders per customer
	$groups = [];

	foreach ($orders as $order) {

		$key = !empty($order['customer_id']) ? 'id:'. $order['customer_id'] : 'email:'. $order['customer_email'];

		if (!isset($groups[$key])) {
			$groups[$key] = [
				'customer_id' => $order['customer_id'],
				'name' => trim($order['customer_firstname'].' '.$order['customer_lastname']),
				'email' => $order['customer_email'],
				'count' => 0,
				'value' => 0,
				'tax' => 0,
				'orders' => [],
			];
		}

		$groups[$key]['count']++;
		$groups[$key]['value'] += (float)$order['total'];
		$groups[$key]['tax'] += (float)$order['total_tax'];
		$groups[$key]['orders'][] = $order;
	}

	$grand_count = 0;
	$grand_value = 0;
	$grand_tax = 0;
	$earliest_created_at = null;

	foreach ($groups as $group) {
		$grand_count += $group['count'];
		$grand_value += $group['value'];
		$grand_tax += $group['tax'];
	}

	foreach ($orders as $order) {
		if ($earliest_created_at === null || $order['created_at'] < $earliest_created_at) {
			$earliest_created_at = $order['created_at'];
		}
	}

	if (isset($_GET['download'])) {

		// One flat row per order
		$csv_rows = [];

		foreach ($orders as $order) {

			$csv_rows[] = [
				'customer_id' => $order['customer_id'],
				'customer_name' => trim($order['customer_firstname'].' '.$order['customer_lastname']),
				'customer_email' => $order['customer_email'],
				'order_id' => $order['id'],
				'created_at' => $order['created_at'],
				'total' => $order['total'],
				'total_tax' => $order['total_tax'],
			];
		}

		$date_from_file = $_GET['date_from'] !== '' ? date('Ymd', strtotime($_GET['date_from'])) : date('Ymd', strtotime($earliest_created_at ?? $_GET['date_to']));
		$date_to_file = date('Ymd', strtotime($_GET['date_to']));

		header('Content-Type: application/csv; charset='. mb_http_output());
		header('Content-Disposition: filename="credit_report_'. $date_from_file .'-'. $date_to_file .'.csv"');
		echo f::csv_encode($csv_rows);
		exit;
	}

	$currency_code = settings::get('store_currency_code');
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_credit_report', 'Credit Report') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_begin('filter_form', 'get') ?>
			<ul class="list-inline">
				<li>
					<div class="input-group" style="max-width: 380px;">
						<?= f::form_input_date('date_from') ?>
						<span class="input-group-text"> - </span>
						<?= f::form_input_date('date_to') ?>
					</div>
				</li>
				<li><?= f::form_button('filter', ['true', f::draw_fonticon('icon-funnel') .' '. t('title_filter_now', 'Filter')]) ?></li>
				<li><?= f::form_button('download', ['true', f::draw_fonticon('icon-download') .' '. t('title_download', 'Download')]) ?></li>
			</ul>
		<?= f::form_end() ?>
	</div>

	<table class="table data-table">
		<thead>
			<tr>
				<th><?= t('title_customer', 'Customer') ?></th>
				<th><?= t('title_email_address', 'Email Address') ?></th>
				<th class="text-center"><?= t('title_credit_orders', 'Credit Orders') ?></th>
				<th><?= t('title_date', 'Date') ?></th>
				<th class="text-end"><?= t('title_total', 'Total') ?></th>
				<th class="text-end"><?= t('title_tax', 'Tax') ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if (empty($groups)) { ?>
			<tr>
				<td colspan="6" class="text-center">
					<?= t('text_no_credit_orders', 'No credit orders for the selected period') ?>
				</td>
			</tr>
			<?php } ?>

			<?php foreach ($groups as $group) { ?>
			<tr>
				<td>
					<strong><?= $group['customer_id'] ? t('title_customer_id', 'Customer ID').' '. (int)$group['customer_id'] : t('title_guest', 'Guest') ?></strong>
					<?= $group['name'] ? '<br>'. f::escape_html($group['name']) : '' ?>
				</td>
				<td><?= f::escape_html($group['email']) ?></td>
				<td class="text-center"><?= $group['count'] ?></td>
				<td></td>
				<td class="text-end"><strong><?= currency::format($group['value'], false, $currency_code) ?></strong></td>
				<td class="text-end"><?= currency::format($group['tax'], false, $currency_code) ?></td>
			</tr>

				<?php foreach ($group['orders'] as $order) { ?>
			<tr>
				<td colspan="3">
					<a class="link" href="<?= document::link(null, ['app' => 'orders', 'doc' => 'edit_order', 'order_id' => (int)$order['id']], false) ?>">
						<?= t('title_order', 'Order') ?> <?= (int)$order['id'] ?>
					</a>
				</td>
				<td><?= f::datetime_format('datetime', $order['created_at']) ?></td>
				<td class="text-end"><?= currency::format((float)$order['total'], false, $currency_code) ?></td>
				<td class="text-end"><?= currency::format((float)$order['total_tax'], false, $currency_code) ?></td>
			</tr>
				<?php } ?>
			<?php } ?>
		</tbody>

		<?php if (!empty($groups)) { ?>
		<tfoot>
			<tr>
				<td class="text-end"><?= strtoupper(t('title_total', 'Total')) ?></td>
				<td></td>
				<td class="text-center"><?= $grand_count ?></td>
				<td></td>
				<td class="text-end"><strong><?= currency::format($grand_value, false, $currency_code) ?></strong></td>
				<td class="text-end"><?= currency::format($grand_tax, false, $currency_code) ?></td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>
</div>

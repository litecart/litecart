<?php

	administrator::require_login();

	document::$title[] = t('title_most_shopping_customers', 'Most Shopping Customers');

	breadcrumbs::add(t('title_reports', 'Reports'));
	breadcrumbs::add(t('title_most_shopping_customers', 'Most Shopping Customers'), document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	$_GET['date_from'] = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : null;
	$_GET['date_to'] = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : null;

	if ($_GET['date_from'] > $_GET['date_to']) {
		list($_GET['date_from'], $_GET['date_to']) = [$_GET['date_to'], $_GET['date_from']];
	}

	$date_first_order = database::query(
		"select min(created_at) as min_date
		from ". DB_PREFIX ."orders
		limit 1;"
	)->fetch(function($result){
		return isset($result['min_date']) ? date('Y-m-d', strtotime($result['min_date'])) : null;
	});

	if (empty($date_first_order)) {
		$date_first_order = date('Y-m-d');
	}

	if ($_GET['date_from'] < $date_first_order) {
		$_GET['date_from'] = $date_first_order;
	}

	if ($_GET['date_from'] > date('Y-m-d')) {
		$_GET['date_from'] = date('Y-m-d');
	}

	if ($_GET['date_to'] > date('Y-m-d')) {
		$_GET['date_to'] = date('Y-m-d');
	}

	$customers_query = database::query(
		"select
			sum(o.total - total_tax) as total_amount,
			o.customer_id as id,
			if(o.customer_company, o.customer_company, concat(o.customer_firstname, ' ', o.customer_lastname)) as name,
			customer_email as email
		from ". DB_PREFIX ."orders o
		where o.order_status_id in (
			select id from ". DB_PREFIX ."order_statuses
			where is_sale
		)
		". (!empty($_GET['date_from']) ? "and o.created_at >= '". date('Y-m-d 00:00:00', strtotime($_GET['date_from'])) ."'" : '') ."
		". (!empty($_GET['date_to']) ? "and o.created_at <= '". date('Y-m-d 23:59:59', strtotime($_GET['date_to'])) ."'" : '') ."
		group by if(o.customer_id, o.customer_id, o.customer_email)
		order by total_amount desc;"
	);

	if (isset($_GET['download'])) {
		$customers = $customers_query->fetch_all();

		header('Content-Type: application/csv; charset='. mb_http_output());
		header('Content-Disposition: filename="most_shopping_customers_'. date('Ymd', strtotime($_GET['date_from'])) .'-'. date('Ymd', strtotime($_GET['date_to'])) .'.csv"');
		echo f::csv_encode($customers);
		exit;

	} else {
		$customers = $customers_query->fetch_page(null, null, $_GET['page'], settings::get('data_table_rows_per_page'), $num_rows, $num_pages);
	}

?>
<style>
form[name="filter_form"] li {
	vertical-align: middle;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_most_shopping_customers', 'Most Shopping Customers') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_begin('filter_form', 'get') ?>
			<ul class="list-inline">
				<li>
					<div class="input-group" style="max-width: 380px;">
						<?= f::form_input_date('date_from', true) ?>
						<span class="input-group-text"> - </span>
						<?= f::form_input_date('date_to', true) ?>
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
				<th class="main"><?= t('title_email_address', 'Email Address') ?></th>
				<th class="text-center"><?= t('title_total_amount', 'Total Amount') ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($customers as $customer) { ?>
			<tr>
				<td><?= !empty($customer['id']) ? '<a href="'. document::href_ilink('customers/edit_customer', ['customer_id' => $customer['id']]) .'">'. $customer['name'] .'</a>' : $customer['name'] .' <em>('. t('title_guest', 'Guest') .')</em>' ?></td>
				<td><?= $customer['email'] ?></td>
				<td class="text-end"><?= currency::format($customer['total_amount'], false, settings::get('store_currency_code')) ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?= f::draw_pagination($num_pages) ?>
	</div>
	<?php } ?>
</div>

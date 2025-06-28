<?php

	// Table Rows
	$orders = database::query(
		"select o.*, os.color as order_status_color, os.icon as order_status_icon, json_value(os.name, '$.". database::input(language::$selected['code']) ."') as order_status_name
		from ". DB_TABLE_PREFIX ."orders o
		left join ". DB_TABLE_PREFIX ."order_statuses os on (os.id = o.order_status_id)
		where o.order_status_id
		and os.is_archived = 0
		order by o.created_at desc, o.id desc
		limit 10;"
	)->fetch_all(function(&$order) {

		if (!$order['order_status_icon']) {
			$order['order_status_icon'] = 'icon-circle-o';
		}

		if (!$order['order_status_color']) {
			$order['order_status_color'] = '#cccccc';
		}

		$order['classes'] = [];

		if (!$order['order_status_id']) {
			$order['classes'][] = 'semi-transparent';
		}

		if ($order['unread']) {
			$order['classes'][] = 'bold';
		}
	});

?>
<div id="widget-orders" class="widget card" style="padding-bottom: .5em;">
	<div class="card-header">
		<div class="card-title">
			<?php echo t('title_orders', 'Orders'); ?>
		</div>
	</div>

	<table class="table data-table">
		<thead>
			<tr>
				<th></th>
				<th><?php echo t('title_numer', 'Number'); ?></th>
				<th class="main"><?php echo t('title_customer', 'Customer'); ?></th>
				<th><?php echo t('title_country', 'Country'); ?></th>
				<th><?php echo t('title_payment_method', 'Payment Method'); ?></th>
				<th><?php echo t('title_order_status', 'Order Status'); ?></th>
				<th class="text-end"><?php echo t('title_amount', 'Amount'); ?></th>
				<th><?php echo t('title_date', 'Date'); ?></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($orders as $order) { ?>
			<tr class="<?php echo implode(' ', $order['classes']); ?>">
				<td><?php echo functions::draw_fonticon($order['order_status_icon'], 'style="color: '. $order['order_status_color'] .';"'); ?></td>
				<td class="text-center"><?php echo $order['no']; ?></td>
				<td>
					<a class="link" href="<?php echo document::href_ilink('orders/order', ['order_id' => $order['id']]); ?>">
						<?php echo functions::draw_fonticon($order['customer_company'] ? 'icon-building' : 'icon-user', 'style="opacity: .5;"'); ?>
						<?php echo $order['customer_company'] ?: $order['customer_firstname'] .' '. $order['customer_lastname']; ?>
					</a>
				</td>
				<td><?php echo ($order['customer_country_code']) ? reference::country($order['customer_country_code'])->name : ''; ?></td>
				<td><?php echo $order['payment_option_name']; ?></td>
				<td><?php echo $order['order_status_id'] ? $order['order_status_name'] : t('title_uncompleted', 'Uncompleted'); ?></td>
				<td class="text-end"><?php echo currency::format($order['total'], false, $order['currency_code'], $order['currency_value']); ?></td>
				<td class="text-end"><?php echo functions::datetime_when($order['created_at']); ?></td>
				<td class="text-end">
				<td>
					<div class="dropdown dropdown-end">
						<div class="btn btn-default btn-sm dropdown-toggle"  data-toggle="dropdown">
							<?php echo functions::draw_fonticon('icon-print'); ?>
						</div>
						<nav class="dropdown-menu">
							<a class="dropdown-item" href="<?php echo  document::href_ilink('f:printable_packing_slip', ['order_id' => $order['id'], 'public_key' => $order['public_key']]); ?>" target="_blank">
								<?php echo functions::escape_html(t('title_packing_slip', 'Packing Slip')); ?>
							</a>
							<a class="dropdown-item" href="<?php echo document::href_ilink('f:printable_order_copy', ['order_id' => $order['id'], 'public_key' => $order['public_key']]); ?>" target="_blank" title="">
								<?php echo functions::escape_html(t('title_order_copy', 'Order Copy')); ?>
							</a>
						</nav>
					</div>
				</td>
				<td class="text-end">
					<a class="btn btn-default btn-sm" href="<?php echo document::href_ilink('orders/edit_order', ['order_id' => $order['id']]); ?>" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
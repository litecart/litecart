<?php

	try {

		if (empty($_GET['order_id'])) {
			throw new Exception('No order ID provided.');
		}

		$order = new ent_order($_GET['order_id']);

		if (!$_POST) {
			$_POST = $order->data;
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	if (!empty($_POST['save'])) {

		try {

			foreach ([
				'order_status_id',
				'shipping_tracking_id',
				'shipping_tracking_url',
				'notes',
			] as $field) {
				if (isset($_POST[$field])) {
					$order->data[$field] = $_POST[$field];
				}
			}

			$order->save();

			notices::add('success', t('success_changes_saved', 'Changes saved successfully'));
			redirect(document::ilink(__APP__.'/orders'), 303);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$items = database::query(
		"select
			oi.stock_item_id,
			COALESCE(JSON_VALUE(si.name, '$.". database::input(language::$selected['code']) ."'), oi.name) as name,
			si.sku,
			si.gtin,
			sum(ol.quantity * ol.quantity) as quantity,
			si.quantity as stock_quantity,
			COALESCE(r.reserved_quantity, 0) as quantity_reserved,
			(si.quantity - COALESCE(r.reserved_quantity, 0)) as quantity_available
		from ". DB_TABLE_PREFIX ."orders_items oi
		left join ". DB_TABLE_PREFIX ."orders_lines ol on (ol.id = oi.line_id and ol.order_id = oi.order_id)
		left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = oi.stock_item_id)
		left join (
			select stock_item_id, sum(quantity) as reserved_quantity
			from ". DB_TABLE_PREFIX ."orders_items
			where order_id in (
				select id from ". DB_TABLE_PREFIX ."orders
				where order_status_id in (
					select id from ". DB_TABLE_PREFIX ."order_statuses
					where stock_action = 'reserve'
				)
				and id != '". (int)$order->data['id'] ."'
			)
			group by stock_item_id
		) r on (oi.stock_item_id = r.stock_item_id)
		where oi.order_id = '". (int)$order->data['id'] ."'
		group by oi.stock_item_id
		order by oi.priority;"
	)->fetch_all();

	// Actions
	$actions = [];

	$mod_order = new mod_order();
	if ($modules = $mod_order->actions()) {
		foreach ($modules as $module) {
			$actions[] = $module;
		}
	}

	$previous_order_id = database::query(
		"select id from ". DB_TABLE_PREFIX ."orders
		where id < ". (int)$order->data['id'] ."
		order by id desc
		limit 1;"
	)->fetch('id');

	$next_order_id = database::query(
		"select id from ". DB_TABLE_PREFIX ."orders
		where id > ". (int)$order->data['id'] ."
		order by id asc
		limit 1;"
	)->fetch('id');

?>
<style>
.rounded-rectangle {
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
	padding: 1em;
	margin-inline-start: -15px;
	margin-bottom: 3mm;
}

.form-group label {
	font-weight: 500;
}

.form-group .detail {
	margin: 0;
	padding: 0.5em 0;
}

.billing-address .value {
	margin: 0 !important;
}

#lines {
	margin-bottom: 2em;
	display: block;
}

#lines tr th:last-child, .order-total tr td:last-child {
	width: 30mm;
}

#lines .items {
	margin: 0;
	padding: 0.5em 1em;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
}

#invoice-total {
	gap: 4mm;
}

#invoice-total .summary {
	text-align: end;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
	padding: 2mm 4mm;
	margin: 0;
	min-width: 150px;
}

#grand-total {
	font-weight: bold;
	border-width: 2px !important;
}

textarea[name="notes"] {
	display: block;
	width: 100%;
	border: none;
	background: #f9f4d6;
	font-family: "Comic Sans MS", cursive, sans-serif;
	font-size: 1.2em;
	transform: rotate(2deg);
	box-shadow: 2px 2px 5px rgb(0 0 0 / 10%);
	padding: 2em;
	border-radius: 2px;
	min-height: 200px;
	overflow: hidden;
	transition: all 200ms ease-in-out;
	font-weight: 500;
}

html.dark-mode textarea[name="notes"] {
	background: #201f1a;
	color: #fff1a3;
}

textarea[name="notes"]:focus {
	overflow: auto;
	transform: rotate(0deg);
	border-radius: var(--border-radius);
	background: var(--input-background) !important;
	color: var(--input-text-color) !important;
}
</style>

<div class="card">
	<div class="card-header">
		<h1><?php echo t('title_order', 'Order'); ?> #<?php echo (int)$order->data['id']; ?></h1>
	</div>

	<?php echo f::form_begin('order_form', 'post'); ?>

	<div class="card-body">
			<div class="grid">

				<div class="col-3">

					<div class="form-group">
						<div class="form-label"><?php echo t('title_order_status', 'Order Status'); ?></div>
						<div class="detail"><?php echo f::form_select_order_status('order_status_id', true); ?></div>
					</div>

					<div class="grid">
						<div class="col-6">
							<div class="form-group">
								<div class="form-label"><?php echo t('title_order_no', 'Order No'); ?></div>
								<div class="detail"><?php echo functions::escape_html($order->data['no']); ?></div>
							</div>
						</div>

						<div class="col-6">
							<div class="form-group">
								<div class="form-label"><?php echo t('title_created_at', 'Created At'); ?></div>
								<div class="detail"><?php echo f::datetime_format('datetime', $order->data['created_at']); ?></div>
							</div>
						</div>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_order_reference', 'Order Reference'); ?></div>
						<div class="detail"><?php echo functions::escape_html($order->data['reference']); ?></div>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_ip_address', 'IP Address'); ?> / <?php echo t('title_hostname', 'Hostname'); ?></div>
						<div class="detail text-ellipsis">
							<div class="ip-address">
								<tt><?php echo $order->data['ip_address']; ?></tt>
								<?php if (!empty($order->data['ip_address'])) { ?>
								<a class="float-end btn btn-default btn-sm" href="https://ip-api.com/#<?php echo $order->data['ip_address']; ?>" target="_blank" style="margin: -.5em 0; margin-inline-start: 1em;">
									<?php echo f::draw_fonticon('icon-square-out', ''); ?>
								</a>
								<?php } ?>
							</div>
							<div class="hostname">
								<small><?php echo functions::escape_html($order->data['hostname']); ?></small>
							</div>
						</div>
					</label>
				</div>

				<div class="col-3 rounded-rectangle">

						<div class="form-group">
							<div class="form-label"><?php echo t('title_shipping_address', 'Shipping Address'); ?></div>
							<div class="detail"><?php echo nl2br(f::format_address($order->data['customer']['shipping_address'])); ?></div>
						</div>

						<div class="form-group">
							<div class="form-label"><?php echo t('title_shipping_weight', 'Shipping Weight'); ?></div>
							<div class="detail"><?php echo !empty($order->data['weight_total']) ? weight::format($order->data['weight_total'], $order->data['weight_unit'])  : '-'; ?></div>
						</div>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_shipping_option', 'Shipping Option'); ?></div>
							<?php echo f::form_input_text('shipping_option[id]', true); ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></div>
							<?php echo f::form_input_text('shipping_tracking_id', true); ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_shipping_tracking_url', 'Shipping Tracking URL'); ?></div>
							<?php echo f::form_input_text('shipping_tracking_url', true); ?>
						</label>
				</div>

				<div class="col-3 rounded-rectangle">

					<div class="form-group">
						<div class="form-label"><?php echo t('title_billing_address', 'Billing Address'); ?></div>
						<div class="detail"><?php echo nl2br(f::format_address($order->data['customer'])); ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?php echo t('title_tax_id', 'Tax ID'); ?></div>
						<div class="detail"><?php echo $order->data['customer']['tax_id']; ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?php echo t('title_payment_option', 'Payment Option'); ?></div>
						<div class="detail"><?php echo $order->data['payment_option']['name'] ?? '-'; ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?php echo t('title_transaction_number', 'Transaction Number'); ?></div>
						<div class="detail"><?php echo $order->data['payment_transaction_id'] ?? '-'; ?></div>
					</div>

				</div>

				<div class="col-3" style="padding-top: 1em;">
					<div class="form-group">
						<a class="btn btn-default btn-block" href="<?php echo document::ilink('f:printable_order_copy', ['order_no' => (int)$order->data['no'], 'public_key' => $order->data['public_key']]); ?>" target="_blank">
							<?php echo f::draw_fonticon('icon-print'); ?> <?php echo t('title_order_copy', 'Order Copy'); ?>
						</a>
					</div>

					<div class="form-group">
						<a class="btn btn-default btn-block" href="<?php echo document::ilink('f:printable_packing_slip', ['order_no' => (int)$order->data['no'], 'public_key' => $order->data['public_key']]); ?>" target="_blank">
							<?php echo f::draw_fonticon('icon-print'); ?> <?php echo t('title_packing_slip', 'Packing Slip'); ?>
						</a>
					</div>

					<?php foreach ($actions as $action) { ?>
					<div class="form-group">
						<a class="btn btn-default btn-block" href="<?php echo document::ilink($action['doc'], $action['params']); ?>" target="_blank" title="<?php echo f::escape_html($action['description']); ?>">
							<?php echo f::draw_fonticon($action['icon']); ?>
							<?php echo $action['title']; ?>
						</a>
					</div>
					<?php } ?>

					<label class="form-group">
						<div class="detail"><?php echo f::form_textarea('notes', true, 'style="height: 100px;" placeholder="'. f::escape_html(t('title_notes', 'Notes')) .'..." spellcheck="false"'); ?></div>
					</label>
				</div>
			</div>

		</div>

		<div class="card-action">
			<?php echo f::form_button_predefined('save', t('title_save', 'Save'), 'submit'); ?>
			<?php echo f::form_button_predefined('cancel', t('title_cancel', 'Cancel'), 'cancel', 'btn-default'); ?>
		</div>
		<?php echo f::form_end(); ?>
</div>

<div class="card">
	<div class="card-header">
		<h2><?php echo t('title_Lines', 'Lines'); ?></h2>
	</div>

	<table id="lines" class="table data-table">
		<thead>
			<tr>
				<th class="main"><?php echo t('title_item', 'Item'); ?></th>
				<th><?php echo t('title_code', 'Code'); ?></th>
				<th><?php echo t('title_qty', 'Qty'); ?></th>
				<th class="text-end"><?php echo t('title_unit_price', 'Unit Price'); ?></th>
				<th class="text-end"><?php echo t('title_discount', 'Discount'); ?></th>
				<th class="text-end"><?php echo t('title_tax', 'Tax'); ?> </th>
				<th class="text-end"><?php echo t('title_sum', 'Sum'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($order->data['lines'] as $line) { ?>
			<tr>
				<td colspan="3" style="white-space: normal;"><?php echo f::escape_html($line['name']); ?></td>
				<td><?php echo f::escape_html($line['code']); ?></td>
				<td><?php echo ($line['quantity'] > 1) ? '<strong>'. (float)$line['quantity'].'</strong>' : (float)$line['quantity']; ?></td>
				<td class="text-end"><?php echo currency::format($line['price'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
				<td class="text-end"><?php echo currency::format($line['discount'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
				<td class="text-end"><?php echo currency::format($line['sum_tax'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
				<td class="text-end"><?php echo currency::format($line['sum'] + $line['sum_tax'], false, $order->data['currency_code'], $order->data['currency_value']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<div class="card-body">
		<div id="invoice-total" class="flex flex-columns">

			<div id="subtotal" class="summary">
				<div class="title"><?php echo t('title_subtotal', 'Subtotal'); ?></div>
				<div class="amount"><?php echo currency::format($_POST['discount'] ?? 0, true, $order->data['currency_code'], $order->data['currency_value']); ?></div>
			</div>

			<div id="total-discount" class="summary">
				<div class="title"><?php echo t('title_total_discount', 'Total Discount'); ?></div>
				<div class="amount"><?php echo currency::format($_POST['discount'] ?? 0, true, $order->data['currency_code'], $order->data['currency_value']); ?></div>
			</div>

			<div id="total-tax" class="summary">
				<div class="title"><?php echo t('title_total_tax', 'Total Tax'); ?></div>
				<div class="amount"><?php echo currency::format($_POST['total_tax'] ?? 0, true, $order->data['currency_code'], $order->data['currency_value']); ?></div>
			</div>

			<div id="grand-total" class="summary">
				<div class="title"><?php echo t('title_grand_total', 'Grand Total'); ?></div>
				<div class="amount"><?php echo currency::format_html($_POST['total'] ?? 0, true, $order->data['currency_code'], $order->data['currency_value']); ?></div>
			</div>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-header">
		<h2><?php echo t('text_stock_items_in_this_order', 'Stock items in this order'); ?></h2>
	</div>

		<table id="lines" class="table data-table">
			<thead>
				<tr>
					<th class="main"><?php echo t('title_item', 'Item'); ?></th>
					<th><?php echo t('title_qty', 'Qty'); ?></th>
					<th><?php echo t('title_sku', 'SKU'); ?></th>
					<th><?php echo t('title_gtin', 'GTIN'); ?></th>
					<th><?php echo t('title_in_stock', 'In Stock'); ?></th>
					<th><?php echo t('title_available', 'Available'); ?></th>
					<th><?php echo t('title_reserved', 'Reserved'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($items as $item) { ?>
				<tr>
					<td style="white-space: normal;"><?php echo $item['name']; ?></td>
					<td><?php echo (float)$item['quantity']; ?></td>
					<td class="text-end"><?php echo f::escape_html($item['sku']); ?></td>
					<td class="text-end"><?php echo f::escape_html($item['gtin']); ?></td>
					<td class="text-end"><?php echo f::escape_html($item['stock_quantity']); ?></td>
					<td class="text-end"><?php echo f::escape_html($item['quantity_available']); ?></td>
					<td class="text-end"><?php echo f::escape_html($item['quantity_reserved']); ?></td>
				</tr>
				<?php } ?>
		</tbody>
	</table>

</div>

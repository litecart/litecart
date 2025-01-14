<style>
.logotype {
	max-width: 320px;
	max-height: 70px;
}

h1 {
	margin: 0;
	border: none;
}

.addresses .row > :not(.billing-address) {
	margin-top: 4mm;
}

.rounded-rectangle {
	border: 1px solid #000;
	border-radius: var(--border-radius);
	padding: 4mm;
	margin-inline-start: -15px;
	margin-bottom: 3mm;
}
.billing-address .value {
	margin: 0 !important;
}

.items tr th:last-child, .order-total tr td:last-child {
	width: 30mm;
}

.page .label {
	font-weight: bold;
	margin-bottom: 3pt;
}
.page .value {
	margin-bottom: 3mm;
}
.page .footer .row {
	margin-bottom: 0;
}
</style>

<section class="page" data-size="A4" dir="<?php echo $text_direction; ?>">
	<header class="header">
		<div class="grid">
			<div class="col-6">
				<?php echo functions::draw_image('storage://images/logotype.png', 0, 0, null, 'class="logotype" alt="'. functions::escape_attr(settings::get('store_name')) .'"'); ?>
			</div>

			<div class="col-6 text-end">
				<h1><?php echo language::translate('title_order_copy', 'Order Copy'); ?></h1>
				<div><?php echo language::translate('title_order', 'Order'); ?> <?php echo $order['no']; ?></div>
				<div><?php echo !empty($order['date_created']) ? date(language::$selected['raw_date'], strtotime($order['date_created'])) : date(language::$selected['raw_date']); ?></div>
			</div>
		</div>
	</header>

	<main class="content">
		<div class="addresses">
			<div class="grid">
				<div class="col-3 shipping-address">
					<div class="label"><?php echo language::translate('title_shipping_address', 'Shipping Address'); ?></div>
					<div class="value"><?php echo nl2br(reference::country($order['customer']['shipping_address']['country_code'])->format_address($order['customer']['shipping_address'])); ?></div>
				</div>

				<div class="col-3">
					<div class="label"><?php echo language::translate('title_shipping_weight', 'Shipping Weight'); ?></div>
					<div class="value"><?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_unit'])  : '-'; ?></div>

					<div class="label"><?php echo language::translate('title_tax_id', 'Tax ID'); ?></div>
					<div class="value"><?php echo $order['customer']['tax_id']; ?></div>
				</div>

				<div class="col-6 billing-address">
					<div class="rounded-rectangle">
						<div class="label"><?php echo language::translate('title_billing_address', 'Billing Address'); ?></div>
						<div class="value"><?php echo nl2br(reference::country($order['customer']['country_code'])->format_address($order['customer'])); ?></div>
					</div>
				</div>
			</div>
		</div>

		<div class="grid">
			<div class="col-6">
				<div class="label"><?php echo language::translate('title_shipping_option', 'Shipping Option'); ?></div>
				<div class="value"><?php echo fallback($order['shipping_option']['name'], '-'); ?></div>

				<div class="label"><?php echo language::translate('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></div>
				<div class="value"><?php echo fallback($order['shipping_tracking_id'], '-'); ?></div>
			</div>

			<div class="col-6">
				<div class="label"><?php echo language::translate('title_payment_option', 'Payment Option'); ?></div>
				<div class="value"><?php echo fallback($order['payment_option']['name'], '-'); ?></div>

				<div class="label"><?php echo language::translate('title_transaction_number', 'Transaction Number'); ?></div>
				<div class="value"><?php echo fallback($order['payment_transaction_id'], '-'); ?></div>
			</div>
		</div>

		<table class="items table table-striped data-table">
			<thead>
				<tr>
					<th><?php echo language::translate('title_qty', 'Qty'); ?></th>
					<th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
					<th><?php echo language::translate('title_gtin', 'GTIN'); ?></th>
					<th class="text-end"><?php echo language::translate('title_unit_price', 'Unit Price'); ?></th>
					<th class="text-end"><?php echo language::translate('title_tax', 'Tax'); ?> </th>
					<th class="text-end"><?php echo language::translate('title_sum', 'Sum'); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach (array_slice($order['items'], 0, $max_first_page_items) as $item) { ?>
				<tr>
					<td><?php echo ($item['quantity'] > 1) ? '<strong>'. (float)$item['quantity'].'</strong>' : (float)$item['quantity']; ?></td>
					<td style="white-space: normal;"><?php echo $item['name']; ?></td>
					<td><?php echo $item['gtin']; ?></td>
					<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price'], false, $order['currency_code'], $order['currency_value']); ?></td>
					<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['discount'] + $item['discount_tax'] : $item['discount'], false, $order['currency_code'], $order['currency_value']); ?></td>
					<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['sum'] + $item['sum_tax'] : $item['sum'], false, $order['currency_code'], $order['currency_value']); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<?php if (count($order['items']) <= $max_first_page_items) { ?>
		<table class="order-total table data-table">
			<tbody>
				<tr>
					<td class="text-end"><strong><?php echo language::translate('title_subtotal', 'Subtotal'); ?>:</strong></td>
					<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $order['subtotal'] + $order['subtotal_tax'] : $order['subtotal'], false, $order['currency_code'], $order['currency_value']); ?></td>
				</tr>

				<?php foreach ($order['order_total'] as $row) { ?>
				<?php if (!empty($order['display_prices_including_tax'])) { ?>
				<tr>
					<td class="text-end"><?php echo $row['title']; ?>:</td>
					<td class="text-end"><?php echo currency::format($row['amount'] + $row['tax'], false, $order['currency_code'], $order['currency_value']); ?></td>
				</tr>
				<?php } else { ?>
				<tr>
					<td class="text-end"><?php echo $row['title']; ?>:</td>
					<td class="text-end"><?php echo currency::format($row['amount'], false, $order['currency_code'], $order['currency_value']); ?></td>
				</tr>
				<?php } ?>
				<?php } ?>

				<?php if (!empty($order['total_tax']) && $order['total_tax'] != 0) { ?>
				<tr>
					<td class="text-end"><?php echo !empty($order['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
					<td class="text-end"><?php echo currency::format($order['total_tax'], false, $order['currency_code'], $order['currency_value']); ?></td>
				</tr>
				<?php } ?>

				<tr>
					<td class="text-end"><strong><?php echo language::translate('title_grand_total', 'Grand Total'); ?>:</strong></td>
					<td class="text-end"><strong><?php echo currency::format($order['total'], false, $order['currency_code'], $order['currency_value']); ?></strong></td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</main>

	<footer class="footer">

		<hr>

		<div class="flex">
			<div class="column">
				<div class="label"><?php echo language::translate('title_address', 'Address'); ?></div>
				<div class="value"><?php echo nl2br(settings::get('store_postal_address')); ?></div>
			</div>

			<div class="column">
				<?php if (settings::get('store_phone')) { ?>
				<div class="label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
				<div class="value"><?php echo settings::get('store_phone'); ?></div>
				<?php } ?>

				<?php if (settings::get('store_tax_id')) { ?>
				<div class="label"><?php echo language::translate('title_vat_registration_id', 'VAT Registration ID'); ?></div>
				<div class="value"><?php echo settings::get('store_tax_id'); ?></div>
				<?php } ?>
			</div>

			<div class="column">
				<div class="label"><?php echo language::translate('title_email', 'Email'); ?></div>
				<div class="value"><?php echo settings::get('store_email'); ?></div>

				<div class="label"><?php echo language::translate('title_website', 'Website'); ?></div>
				<div class="value"><?php echo document::ilink(''); ?></div>
			</div>

			<div class="column">
			</div>

			<div class="column">
			</div>
		</div>
	</footer>

</section>

<?php if (!empty($action_menu)) { ?>
<div id="actions">
	<ul class="list-unstyled">
		<li>
			<button name="print" class="btn btn-default btn-lg">
				<?php echo functions::draw_fonticon('icon-print'); ?> <?php echo language::translate('title_print', 'Print'); ?>
			</button>
		</li>
	</ul>
</div>

<script>
	$('#actions button[name="print"]').on('click', function() {
		window.print()
	})
</script>
<?php } ?>
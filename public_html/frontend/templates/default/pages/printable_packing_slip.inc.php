<?php
	$page_breaks = [20, 50, 80, 110, 140];
?>
<style>
.logotype {
	max-width: 320px;
	max-height: 70px;
}

h1 {
	margin: 0;
	border: none;
}

.addresses .row > :not(.shipping-address) {
	margin-top: 4mm;
}

.rounded-rectangle {
	border: 1px solid #000;
	border-radius: var(--border-radius);
	padding: 4mm;
	margin-inline-start: -15px;
	margin-bottom: 3mm;
}
.rounded-rectangle .value {
	margin: 0 !important;
}

.items tr th:last-child {
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

table.items tbody tr:nth-child(11) {
	page-break-before: always;
}
</style>

<section class="page" data-size="A4">
	<header class="header">
		<div class="grid">
			<div class="col-6">
				<?php echo functions::draw_image('storage://images/logotype.png', 0, 0, null, 'class="logotype" alt="'. functions::escape_attr(settings::get('store_name')) .'"'); ?>
			</div>

			<div class="col-6 text-end">
				<h1><?php echo t('title_packing_slip', 'Packing Slip'); ?></h1>
				<div><?php echo t('title_order', 'Order'); ?> <?php echo $order['no']; ?></div>
				<div><?php echo !empty($order['created_at']) ? date(language::$selected['raw_date'], strtotime($order['created_at'])) : date(language::$selected['raw_date']); ?></div>
			</div>
		</div>
	</header>

	<main class="content">

		<div class="addresses">
			<div class="grid">
				<div class="col-6">
					<div class="label"><?php echo t('title_shipping_option', 'Shipping Option'); ?></div>
					<div class="value"><?php echo fallback($order['shipping_option']['name'], '-'); ?></div>

					<div class="label"><?php echo t('title_shipping_tracking_id', 'Shipping Tracking ID'); ?></div>
					<div class="value"><?php echo fallback($order['shipping_tracking_id'], '-'); ?></div>

					<div class="label"><?php echo t('title_shipping_weight', 'Shipping Weight'); ?></div>
					<div class="value"><?php echo !empty($order['weight_total']) ? weight::format($order['weight_total'], $order['weight_unit'])  : '-'; ?></div>
				</div>

				<div class="col-6 shipping-address">
					<div class="rounded-rectangle">
						<div class="label"><?php echo t('title_shipping_address', 'Shipping Address'); ?></div>
						<div class="value"><?php echo nl2br(functions::format_address($order['customer']['shipping_address'])); ?></div>
					</div>

					<div class="label"><?php echo t('title_email', 'Email'); ?></div>
					<div class="value"><?php echo fallback($order['customer']['email'], '-'); ?></div>

					<div class="label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
					<div class="value"><?php echo fallback($order['customer']['shipping_address']['phone'], '-'); ?></div>
				</div>
			</div>
		</div>

		<table class="items table data-table">
			<thead>
				<tr>
					<th><?php echo t('title_qty', 'Qty'); ?></th>
					<th><?php echo t('title_sku', 'SKU'); ?></th>
					<th class="main"><?php echo t('title_item', 'Item'); ?></th>
				</tr>
			</thead>

<?php
	$i = 0;
	foreach ($order['items'] as $item) {
		if (in_array($i++, $page_breaks)) {
?>
			</tbody>
		</table>
	</main>
</section>

<section class="page" data-size="A4">
	<header class="header">
	</header>

	<main class="content">
		<table class="items table data-table">
			<thead>
				<tr>
					<th><?php echo t('title_qty', 'Qty'); ?></th>
					<th><?php echo t('title_sku', 'SKU'); ?></th>
					<th class="main"><?php echo t('title_item', 'Item'); ?></th>
				</tr>
			</thead>
<?php
		}
?>
			<tbody>
				<tr>
					<td><?php echo ($item['quantity'] > 1) ? '<strong>'. (float)$item['quantity'].'</strong>' : (float)$item['quantity']; ?></td>
					<td><?php echo $item['sku']; ?></td>
					<td style="white-space: normal;"><?php echo $item['name']; ?></td>
				</tr>
<?php
	}
?>
			</tbody>
		</table>

	</main>

	<footer class="footer">

		<hr>

		<div class="grid">
			<div class="col-3">
				<div class="label"><?php echo t('title_address', 'Address'); ?></div>
				<div class="value"><?php echo nl2br(settings::get('store_postal_address')); ?></div>
			</div>

			<div class="col-3">
				<?php if (settings::get('store_phone')) { ?>
				<div class="label"><?php echo t('title_phone_number', 'Phone Number'); ?></div>
				<div class="value"><?php echo settings::get('store_phone'); ?></div>
				<?php } ?>

				<?php if (settings::get('store_tax_id')) { ?>
				<div class="label"><?php echo t('title_vat_registration_id', 'VAT Registration ID'); ?></div>
				<div class="value"><?php echo settings::get('store_tax_id'); ?></div>
				<?php } ?>
			</div>

			<div class="col-3">
				<div class="label"><?php echo t('title_email', 'Email'); ?></div>
				<div class="value"><?php echo settings::get('store_email'); ?></div>

				<div class="label"><?php echo t('title_website', 'Website'); ?></div>
				<div class="value"><?php echo document::ilink(''); ?></div>
			</div>

			<div class="col-3">
			</div>
		</div>
	</footer>
</section>

<?php if (!empty($action_menu)) { ?>
<div id="actions">
	<ul class="list-unstyled">
		<li>
			<button name="print" class="btn btn-default btn-lg">
				<?php echo functions::draw_fonticon('icon-print'); ?> <?php echo t('title_print', 'Print'); ?>
			</button>
		</li>
	</ul>
</div>

<script>
	$('#actions button[name="print"]').on('click', function() {
		window.print();
	});
</script>
<?php } ?>
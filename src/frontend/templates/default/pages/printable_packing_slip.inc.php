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
	font-size: .8em;
	font-weight: 700;
	margin-bottom: 3pt;
}

.page .value {
	margin-bottom: 3mm;
}

.page .footer .row {
	margin-bottom: 0;
}

table.items tbody tr:nth-child(11) {
	break-before: always;
}
</style>

<section class="page" data-size="A4">
	<header class="header">
		<div class="grid">
			<div class="col-6">
				<?= f::draw_image('storage://images/logotype.png', 0, 0, 'fit', 'class="logotype" alt="'. f::escape_attr(settings::get('store_name')) .'"') ?>
			</div>

			<div class="col-6 text-end">
				<h1><?= t('title_packing_slip', 'Packing Slip') ?></h1>
				<div><?= t('title_order', 'Order') ?> <?= $order['no'] ?></div>
				<div><?= !empty($order['created_at']) ? date(language::$selected['raw_date'], strtotime($order['created_at'])) : date(language::$selected['raw_date']) ?></div>
			</div>
		</div>
	</header>

	<main class="content">

		<div class="addresses">
			<div class="grid">
				<div class="col-6">
					<div class="label"><?= t('title_shipping_option', 'Shipping Option') ?></div>
					<div class="value"><?= $order['shipping_option']['name'] ?? '-' ?></div>

					<div class="label"><?= t('title_shipping_tracking_id', 'Shipping Tracking ID') ?></div>
					<div class="value"><?= $order['shipping_tracking_id'] ?? '-' ?></div>

					<div class="label"><?= t('title_shipping_weight', 'Shipping Weight') ?></div>
					<div class="value"><?= !empty($order['weight_total']) ? f::format_weight($order['weight_total'], $order['weight_unit']) : '-' ?></div>
				</div>

				<div class="col-6 shipping-address">
					<div class="rounded-rectangle">
						<div class="label"><?= t('title_shipping_address', 'Shipping Address') ?></div>
						<div class="value"><?= nl2br(f::escape_html(f::format_address($order['customer']['shipping_address']))) ?></div>
					</div>

					<div class="label"><?= t('title_email', 'Email') ?></div>
					<div class="value"><?= isset($order['customer']['email']) ? f::escape_html($order['customer']['email']) : '-' ?></div>

					<div class="label"><?= t('title_phone_number', 'Phone Number') ?></div>
					<div class="value"><?= isset($order['customer']['shipping_address']['phone']) ? f::escape_html($order['customer']['shipping_address']['phone']) : '-' ?></div>
				</div>
			</div>
		</div>

		<table class="items table data-table">
			<thead>
				<tr>
					<th><?= t('title_qty', 'Qty') ?></th>
					<th class="main"><?= t('title_item', 'Item') ?></th>
					<th><?= t('title_sku', 'SKU') ?></th>
					<th><?= t('title_gtin', 'GTIN') ?></th>
					<th><?= t('title_taric', 'TARIC') ?></th>
				</tr>
			</thead>

<?php
	$i = 0;
	foreach ($items as $item) {
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
					<th><?= t('title_qty', 'Qty') ?></th>
					<th class="main"><?= t('title_item', 'Item') ?></th>
					<th><?= t('title_sku', 'SKU') ?></th>
					<th><?= t('title_gtin', 'GTIN') ?></th>
					<th><?= t('title_taric', 'TARIC') ?></th>
				</tr>
			</thead>
<?php
		}
?>
			<tbody>
				<tr>
					<td><?= ($item['total_quantity'] > 1) ? '<strong>'. (float)$item['total_quantity'].'</strong>' : (float)$item['total_quantity'] ?></td>
					<td style="white-space: normal;"><?= $item['name'] ?></td>
					<td><?= $item['sku'] ?></td>
					<td><?= $item['gtin'] ?></td>
					<td><?= $item['taric'] ?></td>
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
				<div class="label"><?= t('title_address', 'Address') ?></div>
				<div class="value"><?= nl2br(settings::get('store_postal_address')) ?></div>
			</div>

			<div class="col-3">
				<?php if (settings::get('store_phone')) { ?>
				<div class="label"><?= t('title_phone_number', 'Phone Number') ?></div>
				<div class="value"><?= settings::get('store_phone') ?></div>
				<?php } ?>

				<?php if (settings::get('store_tax_id')) { ?>
				<div class="label"><?= t('title_vat_registration_id', 'VAT Registration ID') ?></div>
				<div class="value"><?= settings::get('store_tax_id') ?></div>
				<?php } ?>
			</div>

			<div class="col-3">
				<div class="label"><?= t('title_email', 'Email') ?></div>
				<div class="value"><?= settings::get('store_email') ?></div>

				<div class="label"><?= t('title_website', 'Website') ?></div>
				<div class="value"><?= document::ilink('') ?></div>
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
				<?= f::draw_fonticon('icon-print') ?> <?= t('title_print', 'Print') ?>
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
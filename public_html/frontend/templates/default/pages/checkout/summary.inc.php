<style>
#box-checkout-summary tr td:first-child {
	width: 75px;
}
.comments {
	position: relative;
}
.comments .remaining {
	position: absolute;
	bottom: .5em;
	inset-inline-end: .5em;
	color: #999;
}
</style>

<section id="box-checkout-summary">

	<h2 class="title">
		<?php echo language::translate('title_order_summary', 'Order Summary'); ?>
	</h2>

	<table class="table data-table">
		<thead>
			<tr>
				<th class="text-start"><?php echo language::translate('title_item', 'Item'); ?></td>
				<th class="text-end"><?php echo language::translate('title_price', 'Price'); ?></td>
				<th class="text-end"><?php echo language::translate('title_discount', 'Discount'); ?></td>
				<th class="text-end"><?php echo language::translate('title_sum', 'Sum'); ?></td>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($order['items'] as $item) { ?>
			<tr>
				<td class="text-start"><?php echo (float)$item['quantity']; ?> x <?php echo $item['name']; ?></td>
				<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['price'] + $item['tax'] : $item['price'], false, $order['currency_code'], $order['currency_value']); ?></td>
				<td class="text-end">-<?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['discount'] + $item['discount_tax'] : $item['discount'], false, $order['currency_code'], $order['currency_value']); ?></td>
				<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $item['sum'] + $item['sum_tax'] : $item['sum'], false, $order['currency_code'], $order['currency_value']); ?></td>
			</tr>
			<?php } ?>

			<tr>
				<td colspan="3" class="text-end"><strong><?php echo language::translate('title_subtotal', 'Subtotal'); ?>:</strong></td>
				<td class="text-end"><?php echo currency::format(!empty($order['display_prices_including_tax']) ? $order['subtotal'] + $order['subtotal_tax'] : $order['subtotal'], false, $order['currency_code'], $order['currency_value']); ?></td>
			</tr>

			<?php if ($order['total_tax']) { ?>
			<tr>
				<td colspan="3" class="text-end text-muted"><?php echo !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>:</td>
				<td class="text-end text-muted"><?php echo currency::format($order['total_tax'], false, $order['currency_code'], $order['currency_value']); ?></td>
			</tr>
			<?php } ?>
		</tbody>

		<tfoot>
			<tr>
				<td colspan="3" class="text-end"><strong><?php echo language::translate('title_total', 'Payment Due'); ?>:</strong></td>
				<td class="text-end" style="width: 25%;"><strong><?php echo currency::format_html($order['total'], false, $order['currency_code'], $order['currency_value']); ?></strong></td>
			</tr>
		</tfoot>
	</table>

	<div class="comments form-group">
		<label><?php echo language::translate('title_comments', 'Comments'); ?></label>
		<?php echo functions::form_textarea('comments', true, 'maxlength="250" rows="2"'); ?>
		<small class="remaining"></small>
	</div>

	<div class="confirm">
		<?php if ($error) { ?>
		<div class="alert alert-danger">{{error|escape}}</div>
		<?php } ?>

		<?php if (!$error && $consent) { ?>
		<div class="consent text-center" style="font-size: 1.25em; margin-top: 0.5em;">
			<?php echo '<label>'. functions::form_checkbox('terms_agreed', ['1', $consent], true, 'required') .'</label>'; ?>
		</div>
		<?php } ?>

		<?php if (!$error) { ?>
		<button class="btn btn-block btn-lg btn-success" type="submit" name="confirm" value="true"<?php if (!empty($error)) echo ' disabled'; ?>>{{confirm}}</button>
		<?php } ?>
	</div>

</section>

<script>
	$('textarea[name="comments"][maxlength]').on('input', function() {
		let remaining = $(this).attr('maxlength') - $(this).val().length;
		$(this).closest('.comments').find('.remaining').text(remaining);
	});

	$('#box-checkout-summary button[name="confirm"]').on('click', function(e) {
		if ($('box-checkout-customer').prop('changed')) {
			e.preventDefault();
			alert("<?php echo language::translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>");
		}
	});

	$('form[name="checkout_form"]').submit(function(e) {
		let new_button = '<div class="btn btn-block btn-default btn-lg disabled"><?php echo functions::draw_fonticon('icon-spinner'); ?> <?php echo functions::escape_js(language::translate('text_please_wait', 'Please wait')); ?>&hellip;</div>';
		$('#box-checkout-summary button[name="confirm"]').css('display', 'none').before(new_button);
	});
</script>
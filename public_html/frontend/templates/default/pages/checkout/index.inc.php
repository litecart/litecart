<?php
	echo f::draw_style('app://frontend/templates/'. settings::get('template') .'/css/checkout.css');
?>
<style>
h2 {
	margin-top: 0;
}

.sticky-top {
	position: sticky;
	top: 1em;
}

.form-details {
	border: 1px solid var(--default-border-color);
	padding: var(--input-padding-y) var(--input-padding-x);
	border-radius: var(--border-radius);
}

.consent a {
	text-decoration: underline;
}

#order-total {
	margin-bottom: 2em;
}
#order-total th:first-child {
	width: 100%;
}
#order-total th:first-child, #order-total td:first-child {
	padding-inline-start: 0;
}
#order-total th:last-child, #order-total td:last-child {
	padding-inline-end: 0;
}
#order-total tbody:nth-child(2) {
	border-top: 2px solid #000;
}
</style>

<main id="main" class="container">
	{{notices}}

	<div id="content">

		<?php echo f::form_begin('checkout_form', 'post', false, false, ['aria-label' => f::escape_attr(t('title_checkout', 'Checkout'))]); ?>

			<div class="grid">

				<div class="col-md-8">

					<section id="customer-details" class="card" aria-label="<?php echo f::escape_attr(t('title_customer_details', 'Customer Details')); ?>">

						<div class="card-body">

							<h2 class="hidden"><?php echo t('title_customer_details', 'Customer Details'); ?></h2>

							<div class="row">
								<div class="col-md-6">
									<h2><?php echo t('title_billing_details', 'Billing Details'); ?></h2>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_address', 'Address'); ?></div>
										<div class="form-details" style="min-height: 120px;" aria-label="<?php echo f::escape_attr(t('title_billing_address', 'Billing Address')); ?>">
											<?php echo nl2br(f::escape_html(f::format_address($customer))); ?>
										</div>
									</div>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_email', 'Email'); ?></div>
										<div class="form-details"><?php echo f::escape_html($customer['email'] ?: '-'); ?></div>
									</div>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_phone', 'Phone'); ?></div>
										<div class="form-details"><?php echo f::escape_html($customer['phone'] ?: '-'); ?></div>
									</div>
								</div>

								<div class="col-md-6">
									<h2><?php echo t('title_shipping_details', 'Shipping Details'); ?></h2>

									<?php if (!empty($customer['different_shipping_address'])) { ?>
									<div class="form-group">
										<div class="form-label"><?php echo t('title_address', 'Address'); ?></div>
										<div class="form-details" style="min-height: 120px;" aria-label="<?php echo f::escape_attr(t('title_shipping_address', 'Shipping Address')); ?>">
											<?php echo nl2br(f::escape_html(f::format_address($customer['shipping_address']))); ?>
										</div>
									</div>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_email', 'Email'); ?></div>
										<div class="form-details"><?php echo f::escape_html($customer['shipping_address']['email'] ?: '-'); ?></div>
									</div>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_phone', 'Phone'); ?></div>
										<div class="form-details"><?php echo f::escape_html($customer['shipping_address']['phone'] ?: '-'); ?></div>
									</div>

									<?php } else { ?>
									<div class="form-group">
										<div><em>(<?php echo t('text_same_as_billing', 'Same as billing'); ?>)</em></div>
									</div>
									<?php } ?>

								</div>
							</div>

							<div class="form-group">
								<a href="<?php echo document::href_ilink('checkout/customer'); ?>" class="btn btn-default" data-toggle="lightbox" data-seamless="true">
									<?php echo t('title_change', 'Change'); ?>
								</a>
							</div>
						</div>
					</section>

					<?php if (!empty($shipping_options)) { ?>
					<section id="shipping-options" class="card" aria-label="<?php echo f::escape_attr(t('text_how_would_you_like_to_receive_items', 'How would you like to receive your items?')); ?>">

						<div class="card-header">
							<h2 class="card-title">
								<?php echo t('text_how_would_you_like_to_receive_items', 'How would you like to receive your items?'); ?>
							</h2>
						</div>

						<div class="card-body">

							<div class="grid" role="radiogroup" aria-label="<?php echo f::escape_attr(t('text_how_would_you_like_to_receive_items', 'How would you like to receive your items?')); ?>">

								<?php foreach ($shipping_options as $option) { ?>
								<div class="col-md-4">
									<div class="option form-group">
										<label class="form-check">
											<?php echo f::form_radio_button('shipping_option[id]', $option['id'], true, 'class="form-check-input"'); ?>
											<span class="form-check-label">

												<img src="<?php echo f::escape_html($option['icon']); ?>" alt="" class="option-icon" aria-hidden="true">

												<strong><?php echo $option['name']; ?></strong>

												<?php if ($option['description']) { ?>
												<br><small><?php echo $option['description']; ?></small>
												<?php } ?>

												<?php if ($option['fields']) { ?>
												<br><?php echo $option['fields']; ?>
												<?php } ?>

												<?php if ($option['fee'] != '') { ?>
												<br><strong><?php echo currency::format($option['fee']); ?></strong>
												<?php } ?>

											</span>
										</label>
									</div>
								</div>
								<?php } ?>

							</div>
						</div>
					</section>
					<?php } ?>

					<?php if (!empty($payment_options)) { ?>
					<section id="payment-options" class="card" aria-label="<?php echo f::escape_attr(t('text_how_would_you_like_to_pay', 'How would you like to pay?')); ?>">

						<div class="card-header">
							<h2 class="card-title">
								<?php echo t('text_how_would_you_like_to_pay', 'How would you like to pay?'); ?>
							</h2>
						</div>

						<div class="card-body">

							<div class="grid" role="radiogroup" aria-label="<?php echo f::escape_attr(t('text_how_would_you_like_to_pay', 'How would you like to pay?')); ?>">

								<?php foreach ($payment_options as $option) { ?>
								<div class="col-md-4">
									<div class="option form-group">
										<label class="form-check">
											<?php echo f::form_radio_button('payment_option[id]', $option['id'], true, 'class="form-check-input"'); ?>
											<span class="form-check-label">
												<strong><?php echo $option['name']; ?></strong>

												<?php if ($option['description']) { ?>
												<br><small><?php echo $option['description']; ?></small>
												<?php } ?>

												<?php if ($option['fields']) { ?>
												<br><?php echo $option['fields']; ?>
												<?php } ?>

												<?php if ($option['fee'] != '') { ?>
												<br><strong><?php echo currency::format($option['fee']); ?></strong>
												<?php } ?>

											</span>
										</label>
									</div>
								</div>
								<?php } ?>

							</div>
						</div>
					</section>
					<?php } ?>

				</div>

				<div class="col-md-4">

					<div class="sticky-top">
						<section id="order-summary" class="card" aria-label="<?php echo f::escape_attr(t('title_order_summary', 'Order Summary')); ?>">

							<div class="card-header">
								<h2 class="card-title"><?php echo t('title_order_summary', 'Order Summary'); ?></h2>
							</div>

							<div class="card-body">

								<table id="order-total" class="table data-table">
									<caption class="hidden"><?php echo t('title_order_summary', 'Order Summary'); ?></caption>
									<thead>
										<tr>
											<th scope="col"><?php echo t('title_description', 'Description'); ?></th>
											<th scope="col" class="text-end"><?php echo t('title_amount', 'Amount'); ?></th>
										</tr>
									</thead>

									<tbody>
										<?php foreach ($items as $item) { ?>
										<tr>
											<td><?php echo (float)$item['quantity']; ?> x <a href="<?php echo document::href_ilink('f:product', ['product_id' => $item['product_id']]); ?>" aria-label="<?php echo f::escape_attr($item['name']); ?>"><?php echo f::escape_html($item['name']); ?></a></td>
											<td class="text-end formatted-value"><?php echo currency::format($item['final_price']['display_value'] * $item['quantity']); ?></td>
										</tr>
										<?php } ?>
									</tbody>

									<tbody>
										<tr>
											<th scope="row" class="text-end"><?php echo t('title_subtotal', 'Subtotal'); ?>:</th>
											<td class="text-end formatted-value"><?php echo currency::format($subtotal['display_value']); ?></td>
										</tr>

										<?php if ($total_discount['value'] != 0) { ?>
										<tr>
											<th scope="row" class="text-end"><?php echo t('title_discount', 'Discount'); ?>:</th>
											<td class="text-end formatted-value">-<?php echo currency::format($total_discount['display_value']); ?></td>
										</tr>
										<?php } ?>


										<?php if ($shipping['value'] != 0) { ?>
										<tr>
											<th scope="row" class="text-end"><?php echo t('title_shipping', 'Shipping'); ?>:</th>
											<td class="text-end formatted-value"><?php echo currency::format($shipping['display_value']); ?></td>
										</tr>
										<?php } ?>

										<?php if ($total['tax'] != 0) { ?>
										<tr>
											<th scope="row" class="text-end"><?php echo !empty($display_prices_including_tax) ? t('title_tax_included', 'Tax (included)') : t('title_tax', 'Tax'); ?>:</th>
											<td class="text-end formatted-value"><?php echo currency::format($total['tax']); ?></td>
										</tr>
										<?php } ?>

										<tr style="font-size: 1.2em;">
											<th scope="row" class="text-end"><strong><?php echo t('title_total', 'Total'); ?>:</strong></th>
											<td class="text-end formatted-value"><strong><?php echo currency::format_html($total['value']); ?></strong></td>
										</tr>
									</tbody>
								</table>

								<label class="form-group">
									<div class="form-label"><?php echo t('title_comments', 'Comments'); ?></div>
									<?php echo f::form_textarea('comments', true, 'maxlength="250" rows="2"'); ?>
									<small class="remaining"></small>
								</label>

								<?php if ($error) { ?>
								<div class="notices" role="alert">
									<div class="notice notice-danger"><?php echo f::escape_html($error); ?></div>
								</div>
								<?php } ?>

								<?php if (!$error && $consent) { ?>
								<div class="form-group consent">
									<?php echo f::form_checkbox('terms_agreed', ['1', $consent], true, ['required' => '']); ?>
								</div>
								<?php } ?>

								<div class="form-group">
									<button class="btn btn-block btn-lg btn-success" type="submit" name="confirm_purchase" value="true"<?php echo !empty($error) ? ' disabled' : ''; ?> aria-label="<?php echo f::escape_attr(t('title_confirm_purchase', 'Confirm Purchase')); ?>"><?php echo t('title_confirm_purchase', 'Confirm Purchase'); ?></button>
								</div>
							</div>

						</section>
					</div>

				</div>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</main>

<script>

	$('input[name="shipping_option[id]"], input[name="payment_option[id]"]').on('change', function() {
		let formData = $(this).closest('form').serialize() + '&update=true';
		$.ajax({
			type: 'post',
			data: formData,
			cache: false,
			async: true,
			success: function(response) {
				let orderTotal = $('#order-total', response).html();
				$('#order-total').html(orderTotal);
			},
		});
	});

</script>
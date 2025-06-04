<style>
.detail {
	padding: var(--input-padding-y) var(--input-padding-x);
	border: 1px solid #ddd;
	border-radius: var(--border-radius);
}
.detail .form-label {
	font-weight: bold;
}
</style>

<main id="box-checkout">
	<?php echo functions::form_begin('checkout_form', 'post', '', false, 'autocomplete="off"'); ?>

		{{notices}}

		<div class="grid">

			<div class="left-wrapper col-md-6">
				<div class="left">

					<div class="grid">
						<div class="col-md-6">
							<img class="logotype" src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>">
						</div>

						<div class="col-md-6 text-end">
							<a class="navigate-back btn btn-default" href="<?php echo document::ilink(''); ?>" >
								<?php echo functions::draw_fonticon('icon-arrow-left'); ?> <?php echo language::translate('title_back_to_store', 'Back To Store'); ?>
							</a>
						</div>
					</div>

					<section id="box-checkout-customer">

						<div class="card-header">
							<div class="float-end">
								<a href="<?php echo document::ilink('checkout/customer'); ?>" class="btn btn-default" style="margin-inline-start: 1em;">
									<?php echo functions::draw_fonticon('icon-pencil'); ?> <?php echo language::translate('title_change', 'Change'); ?>
								</a>
							</div>
							<h2 class="card-title">
								<?php echo language::translate('title_customer_details', 'Customer Details'); ?>
							</h2>
						</div>

						<div class="card-body addresses">

							<div class="grid">

								<div class="col-md-6 detail">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_buyer', 'Buyer'); ?></div>
										<div class="billing-address"><?php echo nl2br(reference::country($order['customer']['country_code'])->format_address($order['customer'])); ?></div>
									</label>

									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
										<div><?php echo fallback($order['customer']['email'], '&nbsp;'); ?></div>
									</label>

									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_phone_number', 'Phone Number'); ?></div>
										<div><?php echo fallback($order['customer']['phone'], '&nbsp;'); ?></div>
									</label>
								</div>

								<div class="col-md-6 detail">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_deliver_to', 'Deliver To'); ?></div>
										<div class="shipping-address"><?php echo nl2br(reference::country($order['customer']['shipping_address']['country_code'])->format_address($order['customer']['shipping_address'])); ?></div>
									</label>
								</div>
							</div>

						</div>
					</section>

			 </div>
			</div>

			<div class="right-wrapper col-md-6">
				<div class="right">

					<div class="shipping wrapper">
						<section id="box-checkout-shipping">

							<h2>
								<?php echo language::translate('text_select_a_shipping_option', 'Select a shipping option'); ?>
							</h2>

							<div class="options">

								<?php foreach ($shipping_options as $option) { ?>
								<label class="option">

									<input name="shipping_option[id]" value="<?php echo $option['id']; ?>" type="radio" hidden <?php if (!empty($selected) && $selected['id'] == $option['id']) echo ' checked'; ?><?php if (!empty($option['error'])) echo ' disabled'; ?>>

									<div class="header row" style="margin: 0;">
										<div class="col-3">
											<?php echo functions::draw_thumbnail($option['icon'], 160, 80, 'fit'); ?>
										</div>

										<div class="col-9" style="align-self: center;">
											<div class="name"><?php echo $option['name']; ?></div>

											<?php if (!empty($option['description'])) { ?>
											<div class="description"><?php echo $option['description']; ?></div>
											<?php } ?>

											<div class="price"><?php echo (empty($option['error']) && $option['fee'] != 0) ? '+ ' . currency::format(tax::get_price($option['fee'], $option['tax_class_id'])) : language::translate('text_no_fee', 'No fee'); ?></div>
											<?php if (!empty($option['error'])) { ?>
											<div class="error"><?php echo $option['error']; ?></div>
											<?php } ?>
										</div>
									</div>

									<?php if (empty($option['error']) && !empty($option['fields'])) { ?>
									<div class="content">
										<hr>
										<div class="fields text-start"><?php echo $option['fields']; ?></div>
									</div>
									<?php } ?>

								</label>
								<?php } ?>

							</div>
						</section>
					</div>

					<div class="payment wrapper">
						<section id="box-checkout-payment">
							<div class="card-header">
								<h2 class="card-title"><?php echo language::translate('text_choose_how_you_would_like_to_pay', 'Choose how you would like to pay'); ?></h2>
							</div>

							<div class="card-body">
								<div class="options">

									<?php foreach ($payment_options as $option) { ?>
									<label class="option">

										<input name="payment_option[id]" value="<?php echo $option['id']; ?>" type="radio" hidden <?php if (!empty($selected) && $selected['id'] == $option['id']) echo ' checked'; ?><?php if (!empty($option['error'])) echo ' disabled'; ?>>

										<div class="header row" style="margin: 0;">
											<div class="col-3" style="margin: 0;">
												<?php echo functions::draw_thumbnail('storage://' . $option['icon'], 160, 80, 'fit'); ?>
											</div>

											<div class="col-9" style="align-self: center;">
												<div class="name"><?php echo $option['name']; ?></div>

												<?php if (!empty($option['description'])) { ?>
												<div class="description"><?php echo $option['description']; ?></div>
												<?php } ?>

												<div class="price"><?php if (empty($option['error']) && $option['fee'] != 0) echo '+ ' . currency::format(tax::get_price($option['fee'], $option['tax_class_id'])); ?></div>
												<?php if (!empty($option['error'])) { ?>
												<div class="error"><?php echo $option['error']; ?></div>
												<?php } ?>
											</div>
										</div>

										<?php if (empty($option['error']) && !empty($option['fields'])) { ?>
										<div class="content">
											<hr>
											<div class="fields text-start"><?php echo $option['fields']; ?></div>
										</div>
										<?php } ?>

									</label>
									<?php } ?>
								</div>

							</div>
						</section>
					</div>

					<div class="summary wrapper">
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
					</div>

				</div>
			</div>
		</div>

	<?php echo functions::form_end(); ?>
</main>

<script>
	// Queue Handler
	$checkout = $('#box-checkout');

	$checkout.data('updateQueue', []);
	$checkout.prop('updateLock', false);

	$checkout.on('update', function(e, task) {

		var updateQueue = $(this).data('updateQueue');

		if (task && task.component) {
			updateQueue = jQuery.grep(updateQueue, function(tasks) {
				return (tasks.component == task.component) ? false : true;
			});

			updateQueue.push(task);

			$(this).data('updateQueue', updateQueue);
		}

		if ($(this).prop('updateLock')) return;
		if ($(this).data('updateQueue').length == 0) return;

		$(this).prop('updateLock', true);

		var task = updateQueue.shift();
		$(this).data('updateQueue', updateQueue);

		if (!$('body > .loader-wrapper').length) {

			var $loader = $([
				'<div class="loader-wrapper">',
				'  <div class="loader" style="width: 256px; height: 256px;"></div>',
				'</div>',
			].join('\n'));

			$('body').append($loader);
		}

		if (task.refresh) {
			$('#box-checkout .' + task.component + '.wrapper').fadeTo('fast', 0.15);
		}

		if (task.data === true) {
			switch (task.component) {

				case 'shipping':
					task.data = $('.shipping.wrapper :input').serialize();
					break;

				case 'payment':
					task.data = $('.payment.wrapper :input').serialize();
					break;

				case 'summary':
					task.data = $('.summary.wrapper :input').serialize();
					break;

				default:
					console.error('Invalid component ' + task.component + ' while updating checkout');
					break;
			}
		}

		if (task.component == 'summary') {
			var comments = $(':input[name="comments"]').val();
			var terms_agreed = $(':input[name="terms_agreed"]').prop('checked');
		}

		$.ajax({
			type: task.data ? 'post' : 'get',
			data: task.data,
			dataType: 'html',

			error: function(jqXHR, textStatus, errorThrown) {
				$('#box-checkout .' + task.component + '.wrapper').html('An unexpected error occurred, try reloading the page.');
			},

			success: function(html) {

				if (task.refresh) {

					html = $('.'+task.component+'.wrapper', html).html();

					if (!html) {
						$('#box-checkout .' + task.component + '.wrapper').html('An unexpected error occurred, try reloading the page.');
						return;
					}

					$('#box-checkout .' + task.component + '.wrapper').html(html).fadeTo('fast', 1);
				}

				if (task.component == 'summary') {
					$(':input[name="comments"]').val(comments);
					$(':input[name="terms_agreed"]').prop('checked', terms_agreed);
				}
			},

			complete: function(html) {
				if ($checkout.data('updateQueue').length == 0) {
					$('body > .loader-wrapper').fadeOut('fast', function() {
						$(this).remove();
					});
				}
				$checkout.prop('updateLock', false);
				$checkout.trigger('update');
			}
		});

	}).trigger('update');

	// Shipping
	$(':input[name="shipping_option[id]"]:not(:checked) + .option :input').prop('disabled', true);

	$('input[name="shipping_option[id]"]').on('change', function(e) {

		$('input[name="shipping_option[id]"]:not(:checked) + .option :input').prop('disabled', true);
		$(this).next('.option').find(':input').prop('disabled', false);

		let formdata = $(this).closest('.option-wrapper :input').serialize();

		$checkout
			.trigger('update', [{component: 'shipping', data: formdata + '&select_shipping=true', refresh: false}])
			.trigger('update', [{component: 'payment', refresh: true}])
			.trigger('update', [{component: 'summary', refresh: true}]);
	});

	// Payment
	$(':input[name="payment_option[id]"]:not(:checked) .option :input').prop('disabled', true);

	$(':input[name="payment_option[id]"]').on('change', function(e) {

		$('input[name="payment_option[id]"]:not(:checked) + .option :input').prop('disabled', true);
		$(this).next('.option').find(':input').prop('disabled', false);

		let formdata = $(this).closest('.option-wrapper :input').serialize();

		$checkout
			.trigger('update', [{component: 'payment', data: formdata + '&select_payment=true', refresh: false}])
			.trigger('update', [{component: 'summary', refresh: true}]);
	});

	// Display remaining characters
	$('textarea[name="comments"][maxlength]').on('input', function() {
		let remaining = $(this).attr('maxlength') - $(this).val().length;
		$(this).closest('.comments').find('.remaining').text(remaining);
	});

	// Replace submit button with spinner when form is submitting
	$('form[name="checkout_form"]').submit(function(e) {
		let new_button = '<div class="btn btn-block btn-default btn-lg disabled"><?php echo functions::draw_fonticon('icon-spinner'); ?> <?php echo functions::escape_js(language::translate('text_please_wait', 'Please wait')); ?>&hellip;</div>';
		$('#box-checkout-summary button[name="confirm"]').css('display', 'none').before(new_button);
	});
</script>
<main id="main" class="container">
	{{notices}}

	<div id="content">
		<div class="row layout">

			<div class="col-md-8">

				<section id="box-shopping-cart" class="card">

					<?php echo functions::form_begin('shopping_cart_form', 'post'); ?>

						<div class="card-header">
							<h2 class="card-title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></h2>
						</div>

						<div class="card-body">

							<ul class="items list-unstyled">
								<?php foreach ($items as $key => $item) { ?>
								<li class="item" data-id="<?php echo $item['product_id']; ?>" data-sku="<?php echo $item['sku']; ?>" data-name="<?php echo functions::escape_html($item['name']); ?>" data-price="<?php echo currency::format_raw($item['price']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">
									<div class="row">
										<div class="col-8">

											<div class="row">
												<div class="col-4 col-md-3">
													<a href="<?php echo functions::escape_html($item['link']); ?>" class="float-start" style="max-width: 96px; margin-inline-end: 1em;">
														<?php echo functions::draw_thumbnail($item['image']['original'], 96, 0, 'product'); ?>
													</a>
												</div>

												<div class="col-8 col-md-9">
													<div class="row">
														<div class="col-md-6">
															<div><strong><a href="<?php echo functions::escape_html($item['link']); ?>" style="color: inherit;"><?php echo $item['name']; ?></a></strong></div>
															<?php if (!empty($item['sku'])) echo '<div class="sku">'. $item['sku'] .'</div>'; ?>
															<?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
														</div>

														<div class="col-md-6 text-center">
															<div style="display: inline-flex;">
																<?php if (!empty($item['quantity_unit']->name)) { ?>
																<div class="input-group" style="max-width: 150px;">
																	<?php echo !empty($item['quantity_unit']->decimals) ? functions::form_input_decimal('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']->decimals, 'min="0" max="'. ($item['quantity_max'] ?: '') .'" step="'. ($item['quantity_step'] ?: '') .'"') : functions::form_input_number('item['.$key.'][quantity]', $item['quantity'], 'min="0" max="'. ($item['quantity_max'] ?: '') .'" step="'. ($item['quantity_step'] ?: '') .'"'); ?>
																	<?php echo $item['quantity_unit_name']; ?>
																</div>
																<?php } else { ?>
																<?php echo !empty($item['quantity_unit']->decimals) ? functions::form_input_decimal('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']->decimals, 'min="0"') : functions::form_input_number('item['.$key.'][quantity]', $item['quantity'], 'min="0" style="width: 125px;"'); ?>
																<?php } ?>
																<?php echo functions::form_button('update_cart_item', [$key, functions::draw_fonticon('icon-refresh')], 'submit', 'title="'. functions::escape_attr(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-inline-start: 0.5em;"'); ?>
															</div>
														</div>
													</div>
												</div>
											</div>

										</div>

										<div class="col-2 text-end">
											<?php if ($item['price'] != $item['final_price']) { ?>
											<del class="regular-price"><?php echo currency::format($item['price'] * $item['quantity']); ?></del> <strong class="final-price"><?php echo currency::format($item['final_price'] * $item['quantity']); ?></strong>
											<?php } else { ?>
											<span class="price"><?php echo currency::format($item['price'] * $item['quantity']); ?></span>
											<?php } ?>
										</div>

										<div class="col-2 text-end">
											<td><?php echo functions::form_button('remove_cart_item', [$key, functions::draw_fonticon('icon-trash')], 'submit', 'class="btn btn-danger" title="'. functions::escape_attr(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></td>
										</div>
									</div>
								</li>
								<?php } ?>
							</ul>

							<div class="subtotal text-lg text-end">
								<?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <strong class="formatted-value"><?php echo !empty(customer::$data['display_prices_including_tax']) ?  currency::format($subtotal['value'] + $subtotal['tax']) : currency::format($subtotal['value']); ?></strong>
							</div>
						</div>

					<?php echo functions::form_end(); ?>
				</section>

			</div>

			<div class="col-md-4">

				<section id="box-shopping-cart" class="card">

					<?php echo functions::form_begin('shopping_cart_form', 'post'); ?>

						<div class="card-header">
							<h2 class="card-title"><?php echo language::translate('title_checkout', 'Checkout'); ?></h2>
						</div>

						<div class="card-body">

							<div class="row">
								<div class="form-group col-4">
									<small><?php echo language::translate('title_language', 'Language'); ?></small>
									<div style="line-height: 2;"><?php echo language::$selected['name']; ?></div>
								</div>

								<div class="form-group col-4">
									<small><?php echo language::translate('title_currency', 'Currency'); ?></small>
									<div style="line-height: 2;"><?php echo currency::$selected['code']; ?></div>
								</div>

								<div class="form-group col-4">
									<a class="btn btn-default change" href="<?php echo document::href_ilink('regional_settings', ['redirect_url' => document::link()]); ?>" data-toggle="lightbox" data-seamless="true"><?php echo language::translate('title_change', 'Change'); ?></a>
								</div>
							</div>

							<div class="row">
								<div class="form-group col-8">
									<small><?php echo language::translate('title_country', 'Country'); ?></small>
									<div style="line-height: 2;"><?php echo functions::form_select_country('country_code', true); ?></div>
								</div>

								<div class="form-group col-4">
									<small><?php echo language::translate('title_postcode', 'Postal Code'); ?></small>
									<div style="line-height: 2;"><?php echo functions::form_input_text('postcode'); ?></div>
								</div>
							</div>

							<div class="form-group">
								<label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
								<?php echo functions::form_input_email('billing_address[email]', true, 'required'. (!empty($shopping_cart->data['customer']['id']) ? ' readonly' : '')); ?>
							</div>

							<div>
								<?php echo functions::form_button('checkout', language::translate('title_continue_to_checkout', 'Continue To Checkout') .' '. functions::draw_fonticon('icon-arrow-right'), 'submit', 'class="btn btn-success btn-block btn-lg"'); ?>
							</div>

							<div class="strikethrough-divider">
								<span><?php echo language::translate('text_or_checkout_with', 'Or checkout with'); ?></span>
							</div>

							<div id="express-checkout">
								<a class="option btn btn-default btn-lg btn-block" href=""><?php echo functions::draw_fonticon('icon-paypal'); ?> Paypal</a>
							</div>

						</div>

					<?php echo functions::form_end(); ?>

				</section>

			</div>
		</div>
	</div>
</main>

<script>
	<?php if (!empty(notices::$data['errors'])) { ?>
	alert("<?php echo functions::escape_js(notices::$data['errors'][0]); notices::$data['errors'] = []; ?>")
	<?php } ?>

	$('input[name="billing_address[type]"]').on('change', () => {
		if ($(this).val() == 'business') {
			$('.business-details :input').prop('disabled', false)
			$('.business-details').slideDown('fast')
		} else {
			$('.business-details :input').prop('disabled', true)
			$('.business-details').slideUp('fast')
		}
	}).first().trigger('change')

	if ($('select[name="billing_address[country_code]"] option:selected').data('tax-id-format')) {
		$('input[name="billing_address[tax_id]"]').attr('pattern', $('select[name="billing_address[country_code]"] option:selected').data('tax-id-format'))
	} else {
		$('input[name="billing_address[tax_id]"]').removeAttr('pattern')
	}

	if ($('select[name="billing_address[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="billing_address[postcode]"]').attr('pattern', $('select[name="billing_address[country_code]"] option:selected').data('postcode-format'))
	} else {
		$('input[name="billing_address[postcode]"]').removeAttr('pattern')
	}

	if ($('select[name="billing_address[country_code]"] option:selected').data('phone-code')) {
		$('input[name="billing_address[phone]"]').attr('placeholder', '+' + $('select[name="billing_address[country_code]"] option:selected').data('phone-code'))
	} else {
		$('input[name="billing_address[phone]"]').removeAttr('placeholder')
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('postcode-format')) {
		$('input[name="shipping_address[postcode]"]').attr('pattern', $('select[name="shipping_address[country_code]"] option:selected').data('postcode-format'))
	} else {
		$('input[name="shipping_address[postcode]"]').removeAttr('pattern')
	}

	if ($('select[name="shipping_address[country_code]"] option:selected').data('phone-code')) {
		$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $('select[name="shipping_address[country_code]"] option:selected').data('phone-code'))
	} else {
		$('input[name="shipping_address[phone]"]').removeAttr('placeholder')
	}

	$('input[name="sign_up"]:checkbox').trigger('change')

	// Toggles

	$('#box-customer-details input[name="billing_address[different_shipping_address]"]').on('change', (e) => {
		if (this.checked == true) {
			$('#box-customer-details .shipping-address fieldset').prop('disabled', false).slideDown('fast')
		} else {
			$('#box-customer-details .shipping-address fieldset').prop('disabled', true).slideUp('fast')
		}
	})

	$('#box-customer-details input[name="sign_up"]').on('change', () => {
		if (this.checked == true) {
			$('#box-customer-details .account fieldset').prop('disabled', false).slideDown('fast')
		} else {
			$('#box-customer-details .account fieldset').prop('disabled', true).slideUp('fast')
		}
	})

	// Get Address

	$('#box-customer-details .billing-address :input').on('change', () => {
		if ($(this).val() == '') return
		console.log('Get address (Trigger: '+ $(this).attr('name') +')')
		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $('.billing-address :input').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: (data) => {
				if (data['alert']) alert(data['alert'])
				$.each(data, (key, value) => {
					if ($('.billing-address :input[name="billing_address['+key+']"]').length && $('.billing-address :input[name="billing_address['+key+']"]').val() == '') {
						$('.billing-address :input[name="billing_address['+key+']"]').val(value).trigger('input')
					}
				})
			},
		})
	})

	$('#box-customer-details .shipping-address :input').on('change', () => {
		if ($(this).val() == '') return
		console.log('Get address (Trigger: '+ $(this).attr('name') +')')
		$.ajax({
			url: '<?php echo document::ilink('ajax/get_address.json'); ?>?trigger='+$(this).attr('name'),
			type: 'post',
			data: $('.shipping-address :input').serialize(),
			cache: false,
			async: true,
			dataType: 'json',
			success: (data) => {
				if (data['alert']) alert(data['alert'])
				$.each(data, (key, value) => {
					if ($('.shipping-address :input[name="shipping_address['+key+']"]').length && $('.shipping-address :input[name="shipping_address['+key+']"]').val() == '') {
						$('.shipping-address :input[name="shipping_address['+key+']"]').val(value).trigger('input')
					}
				})
			},
		})
	})

	// Fields

	$('#box-customer-details select[name="billing_address[country_code]"]').on('input', (e) => {

		if ($(this).find('option:selected').data('tax-id-format')) {
			$('input[name="billing_address[tax_id]"]').attr('pattern', $(this).find('option:selected').data('tax-id-format'))
		} else {
			$('input[name="billing_address[tax_id]"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="billing_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'))
		} else {
			$('input[name="billing_address[postcode]"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="billing_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'))
		} else {
			$('input[name="billing_address[phone]"]').removeAttr('placeholder')
		}

		<?php if (settings::get('customer_field_zone')) { ?>

		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: (data) => {
				$('select[name="billing_address[zone_code]"]').html('')
				if (data.length) {
					$('select[name="billing_address[zone_code]"]').prop('disabled', false)
					$.each(data, (i, zone) => {
						$('select[name="billing_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>')
					})
				} else {
					$('select[name="billing_address[zone_code]"]').prop('disabled', true)
				}
			}
		})
		<?php } ?>
	})

	$('#box-customer-details select[name="shipping_address[country_code]"]').on('input', (e) => {

		if ($(this).find('option:selected').data('postcode-format')) {
			$('input[name="shipping_address[postcode]"]').attr('pattern', $(this).find('option:selected').data('postcode-format'))
		} else {
			$('input[name="shipping_address[postcode]"]').removeAttr('pattern')
		}

		if ($(this).find('option:selected').data('phone-code')) {
			$('input[name="shipping_address[phone]"]').attr('placeholder', '+' + $(this).find('option:selected').data('phone-code'))
		} else {
			$('input[name="shipping_address[phone]"]').removeAttr('placeholder')
		}

		<?php if (settings::get('customer_field_zone')) { ?>

		$.ajax({
			url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
			type: 'get',
			cache: true,
			async: false,
			dataType: 'json',
			success: (data) => {
				$('select[name="shipping_address[zone_code]"]').html('')
				if (data.length) {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', false)
					$.each(data, (i, zone) => {
						$('select[name="shipping_address[zone_code]"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>')
					})
				} else {
					$('select[name="shipping_address[zone_code]"]').prop('disabled', true)
				}
			}
		})
		<?php } ?>
	})

	// Checksum

	$('#box-customer-details').data('checksum', $('#box-customer-details :input').serialize())

	$('#box-customer-details :input').on('input change', (e) => {
		if ($('#box-customer-details :input').serialize() != $('#box-customer-details').data('checksum')) {
			$('#box-customer-details').prop('changed', true)
			$('#box-customer-details button[name="save_customer_details"]').prop('disabled', false)
		} else {
			$('#box-customer-details').prop('changed', false)
			$('#box-customer-details button[name="save_customer_details"]').prop('disabled', true)
		}
	})

	// Prevent losing form focus when clicking the label of a checkbox
	$('#box-customer-details .form-check').on('click', (e) => {
		$(this).find(':checkbox').trigger('focusin').trigger('focus')
	})

	// Auto-Save

	let timerSubmitCustomer

	$('#box-customer-details').on('focusout', () => {
		timerSubmitCustomer = setTimeout(function() {
			if ($(this).not(':focus')) {
				if ($('#box-customer-details').prop('changed')) {

					console.log('Autosaving customer details')

					let formdata = $('#box-customer-details :input').serialize() + '&autosave=true'

					$('#box-checkout')
						.trigger('update', [{component: 'customer', data: formdata, refresh: true}])
						.trigger('update', [{component: 'shipping', refresh: true}])
						.trigger('update', [{component: 'payment', refresh: true}])
						.trigger('update', [{component: 'summary'}])

					$('#box-customer-details').data('checksum', $('#box-customer-details :input').serialize())
					$('#box-customer-details :input').first().trigger('input')
				}
			}
		}, 200)
	})

	$('#box-customer-details').on('focusin', () => {
		clearTimeout(timerSubmitCustomer)
	})

	// Process Data

	$('#box-customer-details button[name="save_customer_details"]').on('click', (e) => {
		e.preventDefault()

		let formdata = $('#box-customer-details :input').serialize() + '&save_customer_details=true'

		$('#box-checkout')
			.trigger('update', [{component: 'customer', data: formdata, refresh: true}])
			.trigger('update', [{component: 'shipping', refresh: true}])
			.trigger('update', [{component: 'payment', refresh: true}])
			.trigger('update', [{component: 'summary'}])

		$('#box-customer-details').data('checksum', $('#box-customer-details :input').serialize())
		$('#box-customer-details :input').first().trigger('input')
	})

</script>
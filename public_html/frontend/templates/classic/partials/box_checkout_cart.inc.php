<section id="box-checkout-cart" class="box">

	<h2 class="title">{{translate "title_shopping_cart", "Shopping Cart"}}</h2>

	<div class="headings row hidden-xs">
		<div class="col-3 col-sm-2 col-md-1">
		</div>

		<div class="col-9 col-sm-10 col-md-11">
			<div class="row">
				<div class="col-sm-8">
					{{translate "title_item", "Item"}}
				</div>

				<div class="hidden-xs col-sm-2 text-end">
					{{translate "title_price", "Price"}}
				</div>

				<div class="col-sm-2 text-end">
					{{translate "title_sum", "Sum"}}
				</div>
			</div>
		</div>
	</div>

	<ul class="items list-unstyled">
		<?php foreach ($items as $key => $item) { ?>
		<li class="item" data-id="{{item.product_id}}" data-sku="{{item.sku}}" data-name="{{$item.name}}" data-price="<?php echo currency::format_raw($item['price']); ?>" data-quantity="<?php echo currency::format_raw($item['quantity']); ?>">

			<div class="row">
				<div class="col-3 col-sm-2 col-md-1">
					<a href="{{$item.link}}" class="thumbnail float-start" style="margin-inline-end: 1em;">
						<img class="responsive" src="<?php echo document::href_rlink($item['image']['thumbnail']); ?>" alt="">
					</a>
				</div>

				<div class="col-9 col-sm-10 col-md-11">

					<div class="row">
						<div class="col-sm-4">

							<div class="name"><a href="{{$item.link}}" style="color: inherit;">{{item.name}}</a></div>

							<?php if (!empty($item['data'])) echo '<div class="options">'. implode('<br>', $item['data']) .'</div>'; ?>
							<?php if (!empty($item['error'])) echo '<div class="error">'. $item['error'] .'</div>'; ?>
						</div>

						<div class="col-sm-4">
							<div style="display: inline-flex;">
								<div class="input-group" style="max-width: 150px;">
								<?php if (!empty($item['quantity_unit']['name'])) { ?>
									<?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_input_decimal('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 'min="0"') : functions::form_input_number('item['.$key.'][quantity]', $item['quantity'], 'min="0"'); ?>
									{{item.quantity_unit.name}}
								<?php } else { ?>
									<?php echo !empty($item['quantity_unit']['decimals']) ? functions::form_input_decimal('item['.$key.'][quantity]', $item['quantity'], $item['quantity_unit']['decimals'], 'min="0"') : functions::form_input_number('item['.$key.'][quantity]', $item['quantity'], 'min="0" style="width: 125px;"'); ?>
								<?php } ?>
								</div>
								<?php echo functions::form_button('update_cart_item', array($key, functions::draw_fonticon('icon-refresh')), 'submit', 'title="'. functions::escape_attr(language::translate('title_update', 'Update')) .'" formnovalidate style="margin-inline-start: 0.5em;"'); ?>

								<div style="margin-inline-start: 1em;"><?php echo functions::form_button('remove_cart_item', array($key, functions::draw_fonticon('icon-trash')), 'submit', 'class="btn btn-danger" title="'. functions::escape_attr(language::translate('title_remove', 'Remove')) .'" formnovalidate'); ?></div>
							</div>
						</div>

						<div class="hidden-xs col-sm-2">
							<div class="unit-price text-end">
								<?php echo currency::format($item['display_price']); ?>
							</div>
						</div>

						<div class="col-sm-2">
							<div class="total-price text-end">
								<?php echo currency::format($item['display_price'] * $item['quantity']); ?>
							</div>
						</div>
					</div>
				</div>
			</div>

		</li>
		<?php } ?>
	</ul>

	<div class="subtotal text-end">
		{{translate "title_subtotal", "Subtotal"}}: <strong class="formatted-value"><?php echo !empty(customer::$data['display_prices_including_tax']) ?  currency::format(cart::$total['value'] + cart::$total['tax']) : currency::format_html(cart::$total['value']); ?></strong>
	</div>

</section>

<script>
	$('#box-checkout-cart button[name="remove_cart_item"]').on('click', function(e){
		e.preventDefault();
		let data = [
			'token=' + $(':input[name="token"]').val(),
			$(this).closest('td').find(':input').serialize(),
			'remove_cart_item=' + $(this).val(),
		].join('&');
		queueUpdateTask('cart', data, true);
		queueUpdateTask('customer', true, true);
		queueUpdateTask('shipping', true, true);
		queueUpdateTask('payment', true, true);
		queueUpdateTask('summary', true, true);
	});

	$('#box-checkout-cart button[name="update_cart_item"]').on('click', function(e){
		e.preventDefault();
		let data = [
			'token=' + $(':input[name="token"]').val(),
			$(this).closest('td').find(':input').serialize(),
			'update_cart_item=' + $(this).val(),
		].join('&');
		queueUpdateTask('cart', data, true);
		queueUpdateTask('customer', true, true);
		queueUpdateTask('shipping', true, true);
		queueUpdateTask('payment', true, true);
		queueUpdateTask('summary', true, true);
	});
</script>
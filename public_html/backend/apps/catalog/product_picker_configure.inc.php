<?php

	document::$layout = 'ajax';

	try {

		if (empty($_GET['product_id'])) {
			throw new Exception('Missing product ID');
		}

		if (isset($_GET['collect']) && in_array('price', $_GET['collect']) && empty($_GET['customer'])) {
			throw new Exception('Missing customer');
		}

		if (empty($_GET['language_code'])) {
			$_GET['language_code'] = language::$selected['code'];
		}

		if (empty($_GET['currency_code'])) {
			$_GET['currency_code'] = settings::get('store_currency_code');
		}

		if (empty($_GET['currency_value'])) {
			$_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];
		}

		if (empty($_GET['customer']['id'])) {
			$_GET['customer']['id'] = 0;
		}

		if (empty($_GET['customer'])) {
			$_GET['customer'] = [
				'country_code' => settings::get('default_country_code'),
				'zone_code' => settings::get('default_zone_code'),
				'shipping_address' => [
					'country_code' => settings::get('default_country_code'),
					'zone_code' => settings::get('default_zone_code'),
				],
			];
		}

		$product = reference::product($_GET['product_id'], $_GET['language_code'], $_GET['currency_code'], $_GET['customer']['id']);

		if (empty($product->id)) {
			throw new Exception('Invalid product');
		}

		if (!$_POST) {

			foreach ([
				'name',
				'price',
				'tax',
			] as $field) {
				if (isset($product->$field)) {
					$_POST[$field] = $product->$field;
				}
			}

			$price = $product->final_price;
			$_POST['price'] = currency::format_raw($price, $_GET['currency_code'], $_GET['currency_value']);
			$_POST['tax'] = tax::get_tax($_POST['price'], $product->tax_class_id, $_GET['customer']);
		}

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

?>

<div id="modal-add-order-item" class="modal fade" style="max-width: 720px;">

	<h2><?php echo language::translate('title_add_product', 'Add Product'); ?></h2>

	<div class="modal-body">

		<?php echo functions::form_begin('form_add_product', 'post'); ?>
			<?php echo functions::form_input_hidden('product_id', $product->id); ?>

			<div class="grid">
				<div class="col-md-4">
					<?php echo functions::draw_thumbnail('storage://images/' . $product->image, 320, 0, 'product'); ?>
				</div>

				<div class="col-md-8">

					<div class="grid">
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
								<?php echo functions::form_input_text('name', true, (!isset($_GET['collect']) || !in_array('name', $_GET['collect'])) ? 'readonly' : ''); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<?php if (isset($_GET['collect']) && in_array('quantity', $_GET['collect'])) { ?>
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_quantity', 'Quantity'); ?></div>
								<?php echo functions::form_input_decimal('quantity', 1); ?>
							</label>
						</div>
						<?php } ?>

						<?php if (isset($_GET['collect']) && in_array('price', $_GET['collect'])) { ?>
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_price', 'Price'); ?></div>
								<?php echo functions::form_input_money('price', $_GET['currency_code'], true); ?>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo language::translate('title_tax', 'Tax'); ?></div>
								<?php echo functions::form_input_money('tax', $_GET['currency_code'], true); ?>
							</label>
						</div>
						<?php } ?>
					</div>

					<?php if (isset($_GET['collect']) && in_array('stock_option', $_GET['collect'])) { ?>
					<label class="form-group">
						<?php if (!empty($product->stock_options)) { ?>
						<table class="table data-table">
							<thead>
								<tr>
									<th></th>
									<th><?php echo language::translate('title_stock_option', 'Stock Option'); ?></th>
									<th><?php echo language::translate('title_sku', 'SKU'); ?></th>
									<th class="text-end"><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($product->stock_options as $stock_option) { ?>
								<tr>
									<td><?php echo functions::form_radio_button('stock_item_id', $stock_option['stock_item_id'], true, 'required'); ?></td>
									<td><?php echo $stock_option['name']; ?></td>
									<td><?php echo $stock_option['sku']; ?></td>
									<td class="text-end"><?php echo (float)$stock_option['quantity']; ?></td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="99" class="text-end">
										<strong><?php echo language::translate('title_total', 'Total'); ?>: </strong><?php echo (float)$product->quantity; ?>
									</td>
								</tr>
							</tfoot>
						</table>
						<?php } ?>
					</label>
					<?php } ?>

					<div class="card-action">
						<?php echo functions::form_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
						<?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.litebox.close();"', 'cancel'); ?>
					</div>

				</div>
			</div>

		<?php echo functions::form_end(); ?>
	</div>

</div>

<script>
	$('form[name="form_add_product"] button[name="ok"]').off('click').on('click', function(e) {
		e.preventDefault();

		let $form = $(this).closest('form'),
			callback = $.litebox.current().$currentTarget.data('callback');
			item = {
				id: '',
				product_id: $form.find(':input[name="product_id"]').val(),
				stock_option_id: $form.find(':input[name="stock_option_id"]').val(),
				name: $form.find(':input[name="name"]').val(),
				sku: $form.find(':input[name="sku"]').val(),
				gtin: $form.find(':input[name="gtin"]').val(),
				taric: $form.find(':input[name="taric"]').val(),
				weight: parseFloat($form.find(':input[name="weight"]').val() || 0),
				weight_unit: $form.find(':input[name="weight_unit"]').val(),
				length: parseFloat($form.find(':input[name="length"]').val() || 0),
				width: parseFloat($form.find(':input[name="width"]').val() || 0),
				height: parseFloat($form.find(':input[name="height"]').val() || 0),
				length_unit: $form.find(':input[name="length_unit"]').val(),
				quantity: parseFloat($form.find(':input[name="quantity"]').val() || 0),
				price: parseFloat($form.find(':input[name="price"]').val() || 0),
				tax: parseFloat($form.find(':input[name="tax"]').val() || 0)
			};

		window[callback](product);
		$.litebox.close();
	});
</script>
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

	<h2><?php echo t('title_add_product', 'Add Product'); ?></h2>

	<div class="modal-body">

		<?php echo f::form_begin('form_add_product', 'post'); ?>
			<?php echo f::form_input_hidden('product_id', $product->id); ?>

			<div class="grid">
				<div class="col-md-4">
					<?php echo f::draw_thumbnail('storage://images/' . $product->image, 320, 0, 'product'); ?>
				</div>

				<div class="col-md-8">

					<div class="grid">
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
								<?php echo f::form_input_text('name', true, (!isset($_GET['collect']) || !in_array('name', $_GET['collect'])) ? 'readonly' : ''); ?>
							</label>
						</div>
					</div>

					<div class="grid">
						<?php if (isset($_GET['collect']) && in_array('quantity', $_GET['collect'])) { ?>
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_quantity', 'Quantity'); ?></div>
								<?php echo f::form_input_decimal('quantity', 1); ?>
							</label>
						</div>
						<?php } ?>

						<?php if (isset($_GET['collect']) && in_array('price', $_GET['collect'])) { ?>
						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_price', 'Price'); ?></div>
								<?php echo f::form_input_money('price', $_GET['currency_code'], true); ?>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_tax', 'Tax'); ?></div>
								<?php echo f::form_input_money('tax', $_GET['currency_code'], true); ?>
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
									<th><?php echo t('title_stock_option', 'Stock Option'); ?></th>
									<th><?php echo t('title_sku', 'SKU'); ?></th>
									<th class="text-end"><?php echo t('title_in_stock', 'In Stock'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($product->stock_options as $stock_option) { ?>
								<tr>
									<td><?php echo f::form_radio_button('stock_item_id', $stock_option['stock_item_id'], true, 'required'); ?></td>
									<td><?php echo $stock_option['name']; ?></td>
									<td><?php echo $stock_option['sku']; ?></td>
									<td class="text-end"><?php echo (float)$stock_option['quantity']; ?></td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="99" class="text-end">
										<strong><?php echo t('title_total', 'Total'); ?>: </strong><?php echo (float)$product->quantity; ?>
									</td>
								</tr>
							</tfoot>
						</table>
						<?php } ?>
					</label>
					<?php } ?>

					<div class="card-action">
						<?php echo f::form_button('ok', t('title_ok', 'OK'), 'button', '', 'ok'); ?>
						<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="$.litebox.close();"', 'cancel'); ?>
					</div>

				</div>
			</div>

		<?php echo f::form_end(); ?>
	</div>

</div>

<script>
	$('form[name="form_add_product"] button[name="ok"]').off('click').on('click', function(e) {
		e.preventDefault();

		let $form = this.closest('form'),
			callback = $.litebox.current().$currentTarget.data('callback');
			item = {
				id: '',
				product_id: $(':input[name="product_id"]', $form).val(),
				stock_option_id: $(':input[name="stock_option_id"]', $form).val(),
				name: $(':input[name="name"]', $form).val(),
				sku: $(':input[name="sku"]', $form).val(),
				gtin: $(':input[name="gtin"]', $form).val(),
				taric: $(':input[name="taric"]', $form).val(),
				weight: parseFloat($(':input[name="weight"]', $form).val() || 0),
				weight_unit: $(':input[name="weight_unit"]', $form).val(),
				length: parseFloat($(':input[name="length"]', $form).val() || 0),
				width: parseFloat($(':input[name="width"]', $form).val() || 0),
				height: parseFloat($(':input[name="height"]', $form).val() || 0),
				length_unit: $(':input[name="length_unit"]', $form).val(),
				quantity: parseFloat($(':input[name="quantity"]', $form).val() || 0),
				price: parseFloat($(':input[name="price"]', $form).val() || 0),
				tax: parseFloat($(':input[name="tax"]', $form).val() || 0)
			};

		window[callback](product);
		$.litebox.close();
	});
</script>
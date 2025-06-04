<style>
form[name="buy_now_form"] .dropdown-menu {
	left: 0;
	right: 0;
}
form[name="buy_now_form"] .dropdown-menu .image {
	border-radius: var(--border-radius);
	border: 1px solid var(--default-border-color);
	vertical-align: middle;
}
</style>

<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div id="content">

		<article id="box-product" data-id="{{product_id}}" data-name="{{name|escape}}" data-price="<?php echo currency::format_raw($campaign_price ?: $regular_price); ?>">

			<div class="grid" style="margin-bottom: 0;">
				<div class="col-md-6">
					<div class="images row">

						<div class="col-12">
							<a class="main-image" href="<?php echo document::href_rlink($image); ?>" data-toggle="lightbox" data-gallery="product">
								<?php echo functions::draw_thumbnail($image, 720, 0, 'product', 'alt="'. functions::escape_attr($name) .'"'); ?>
								{{sticker}}
							</a>
						</div>

						<?php foreach ($extra_images as $extra_image) { ?>
						<div class="col-4">
							<a class="extra-image" href="<?php echo document::href_rlink($extra_image); ?>" data-toggle="lightbox" data-gallery="product">
								<?php echo functions::draw_thumbnail($extra_image, 320, 0, 'product', 'alt="'. functions::escape_attr($name) .'"'); ?>
							</a>
						</div>
						<?php } ?>

					</div>
				</div>

				<div class="col-md-6">
					<h1 class="title">{{name}}</h1>

					<?php if ($short_description) { ?>
					<p class="short-description">
						{{short_description}}
					</p>
					<?php } ?>

					<?php if (!empty($brand)) { ?>
					<div class="brand">
						<a href="<?php echo functions::escape_html($brand['link']); ?>">
							<?php if ($brand['image']) { ?>
							<?php echo functions::draw_thumbnail($brand['image'], 0, 40, '', 'style="margin: 0; max-height: 80px; margin-inline-start: 0;"'); ?>
							<?php } else { ?>
							<h3><?php echo $brand['name']; ?></h3>
							<?php } ?>
						</a>
					</div>
					<?php } ?>

					<?php if ($recommended_price) { ?>
					<div class="recommended-price" style="margin: 1em 0;">
						<?php echo language::translate('title_recommended_price', 'Recommended Price'); ?>:
						<span class="value">{{recommended_price|money}}</span>
					</div>
					<?php } ?>

					<?php if ($cheapest_shipping_fee !== null) { ?>
					<div class="cheapest-shipping" style="margin: 1em 0;">
						<?php echo functions::draw_fonticon('icon-truck'); ?> <?php echo strtr(language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">%price</strong>'), ['%price' => currency::format($cheapest_shipping_fee)]); ?>
					</div>
					<?php } ?>

					<?php if ($sku || $mpn || $gtin) { ?>
					<div class="codes" style="margin: 1em 0;">
						<?php if ($sku) { ?>
						<div class="sku">
							<?php echo language::translate('title_sku', 'SKU'); ?>:
							<span class="value">{{sku}}</span>
						</div>
						<?php } ?>

						<?php if ($mpn) { ?>
						<div class="mpn">
							<?php echo language::translate('title_mpn', 'MPN'); ?>:
							<span class="value">{{mpn}}</span>
						</div>
						<?php } ?>

						<?php if ($gtin) { ?>
						<div class="gtin">
							<?php echo language::translate('title_gtin', 'GTIN'); ?>:
							<span class="value">{{gtin}}</span>
						</div>
						<?php } ?>
					</div>
					<?php } ?>

					<?php if (isset($quantity_available)) { ?>
					<div class="stock-status" style="margin: 1em 0;">
						<?php if ($quantity_available > 0) { ?>
						<div class="stock-available">
							<?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
							<span class="value">{{stock_status}}</span>
						</div>

						<?php if ($delivery_status) { ?>
						<div class="stock-delivery">
							<?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>:
							<span class="value"><?php echo $delivery_status['name']; ?></span>
						</div>
						<?php } ?>

						<?php } else { ?>
						<?php if ($sold_out_status) { ?>
							<div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>">
								<?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
								<span class="value"><?php echo $sold_out_status['name']; ?></span>
							</div>

							<?php } else { ?>
							<div class="stock-unavailable">
								<?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
								<span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span>
							</div>
							<?php } ?>
							<?php } ?>
					</div>
					<?php } ?>

					<?php if (isset($final_price)) { ?>
					<?php echo functions::form_begin('buy_now_form', 'post'); ?>

						<fieldset style="margin: 2em 0;">

							<legend><?php echo language::translate('title_purchase_now', 'Purchase Now'); ?></legend>

							<?php echo functions::form_input_hidden('product_id', $product_id); ?>

							<?php if (count($stock_options) > 1) { ?>
							<div class="form-group">
								<div class="form-label"><?php echo language::translate('text_select_desired_option', 'Select desired option'); ?></div>
								<?php echo form_select_product_stock_option('stock_option_id', $product_id, true); ?>
							</div>
							<?php } else if (count($stock_options) == 1) { ?>
							<?php echo functions::form_input_hidden('stock_option_id', $stock_options[0]['stock_option_id']); ?>
							<?php } ?>

							<div class="grid" style="margin-bottom: 0;">

								<div class="col-xl-8">
									<?php if (!settings::get('catalog_only_mode')) { ?>
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_quantity', 'Quantity'); ?></div>
										<div style="display: flex">
											<div class="input-group" style="flex: 0 1 150px;">
												<?php echo !empty($quantity_unit['decimals']) ? functions::form_input_decimal('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 'min="'. ($quantity_min ?: '1') .'" max="'. ($quantity_max ?: '') .'" step="'. ($quantity_step ?: '') .'"') : functions::form_input_number('quantity', isset($_POST['quantity']) ? true : 1, 'min="'. ($quantity_min ?: '1') .'" max="'. ($quantity_max ?: '') .'" step="'. ($quantity_step ?: '') .'"'); ?>
												<?php if (!empty($quantity_unit['name'])) echo '<div class="input-group-text">'. $quantity_unit['name'] .'</div>'; ?>
											</div>

											<div style="flex: 1 0 auto; padding-inline-start: 1em;">
												<?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity_available <= 0 && !$orderable) ? ' disabled' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
											</div>
										</div>
									</label>
									<?php } ?>
								</div>

								<div class="col-xl-4">
									<br>
									<?php echo functions::draw_price_tag($regular_price, $final_price, currency::$selected['code']); ?>

									<?php if ($tax_class_id) { ?>
									<?php if ($total_tax) { ?>
									<div class="tax" style="margin: 0 0 1em 0;">
									<?php if ($tax_rates) { ?>
										<?php echo $including_tax ? language::translate('text_tax_included', 'Tax included') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <span class="total-tax">{{total_tax|money}}</span>
									<?php } else { ?>
										<?php echo language::translate('title_no_tax_included', 'No tax included'); ?>
									<?php } ?>
									</div>
									<?php } ?>
									<?php } ?>
								</div>
							</div>

						</fieldset>

					<?php echo functions::form_end(); ?>
					<?php } ?>

					<h3><?php echo language::translate('title_share_this_product', 'Share This Product'); ?>:</h3>

					<div class="social-bookmarks">

						<a class="link btn btn-default" href="#" title="<?php echo functions::escape_html(language::translate('text_share_link', 'Share Link')); ?>">
							<?php echo functions::draw_fonticon('icon-link', 'style="color: #333;"'); ?>
						</a>

						<a class="btn btn-default" href="<?php echo 'mailto:user@email.com?', http_build_query(['subject' => language::translate('text_is_this_a_product_for_you', 'Is this a product for you?'), 'body' => document::ilink()]); ?>" title="<?php echo functions::escape_html(language::translate('text_share_via_email', 'Share via Email')); ?>">
							<?php echo functions::draw_fonticon('icon-envelope', 'style="color: #333;"'); ?>
						</a>

						<?php /* Requires appId
						<a class="x btn btn-default" href="<?php echo document::href_link('fb-messenger://share/', ['link' => $link]); ?>" target="_blank" title="<?php echo functions::escape_html(strtr(language::translate('text_share_via_s', 'Share via %s'), ['%s' => 'Messenger'])); ?>">
							<?php echo functions::draw_fonticon('icon-brand-messenger', 'style="color: #000;"'); ?>
						</a>
							*/ ?>

						<a class="x btn btn-default" href="<?php echo document::href_link('https://wa.me/', ['text' => $name .' - '. $link]); ?>" target="_blank" title="<?php echo functions::escape_html(strtr(language::translate('text_share_via_s', 'Share via %s'), ['%s' => 'WhatsApp'])); ?>">
							<?php echo functions::draw_fonticon('icon-brand-whatsapp', 'style="color: #000;"'); ?>
						</a>

						<a class="facebook btn btn-default" href="<?php echo document::href_link('https://www.facebook.com/sharer.php', ['u' => $link]); ?>" target="_blank" title="<?php echo functions::escape_html(strtr(language::translate('text_share_on_s', 'Share on %s'), ['%s' => 'Facebook'])); ?>">
							<?php echo functions::draw_fonticon('icon-brand-facebook', 'style="color: #3b5998;"'); ?>
						</a>

						<a class="x btn btn-default" href="<?php echo document::href_link('https://x.com/intent/tweet/', ['text' => $name .' - '. $link]); ?>" target="_blank" title="<?php echo functions::escape_html(strtr(language::translate('text_share_on_s', 'Share on %s'), ['%s' => 'X'])); ?>">
							<?php echo functions::draw_fonticon('icon-brand-x', 'style="color: #000;"'); ?>
						</a>

						<a class="pinterest btn btn-default" href="<?php echo document::href_link('https://pinterest.com/pin/create/button/', ['url' => $link]); ?>" target="_blank" title="<?php echo functions::escape_html(strtr(language::translate('text_share_on_s', 'Share on %s'), ['%s' => 'Pinterest'])); ?>">
							<?php echo functions::draw_fonticon('icon-brand-pinterest', 'style="color: #bd081c;"'); ?>
						</a>

					</div>

				</div>
			</div>

			<?php if (is_ajax_request()) { ?>
			<?php if ($description || $technical_data) { ?>
			<div class="card" style="margin: var(--gutter-y) 0;">
				<div class="card-body">
					<div class="grid" style="margin-bottom: 0;">

						<?php if ($description) { ?>
						<div class="col-md-<?php echo ($technical_data) ? 6 : 12; ?>">
							<h2 style="margin-top: 0;"><?php echo language::translate('title_description', 'Description'); ?></h2>

							<div class="description">
								{{description}}
							</div>
						</div>
						<?php } ?>

						<?php if ($technical_data) { ?>
						<div class="col-md-<?php echo ($description) ? 6 : 12; ?>">
							<h2 style="margin-top: 0;"><?php echo language::translate('title_technical_data', 'Technical Data'); ?></h2>

							<div class="technical-data" <?php if (!$description) echo 'style="columns: 2 auto;"'; ?>>
								<table class="table">
<?php
	foreach ($technical_data as $line) {

		if (preg_match('#[:\t]#', $line)) {

			@list($key, $value) = preg_split('#([:\t]+)#', $line, -1, PREG_SPLIT_NO_EMPTY);

			echo implode(PHP_EOL, [
				'  <tr>',
				'    <td>'. trim($key) .'</td>',
				'    <td>'. trim($value) .'</td>',
				'  </tr>',
			]);

		} else if (trim($line) != '') {
			echo implode(PHP_EOL, [
				'  <thead>',
				'    <tr>',
				'      <th colspan="2">'. $line .'</th>',
				'    </tr>',
				'  </thead>',
				'  <tbody>',
			]);

		} else {
			echo implode(PHP_EOL, [
				' </tbody>',
				'</table>',
				'<table class="table">',
			]);
		}
	}
?>
									</table>
								</div>
							</div>
						<?php } ?>
					</div>

				</div>
			</div>
			<?php } ?>
			<?php } ?>

		</article>

		<?php include 'app://frontend/partials/box_similar_products.inc.php'; ?>

		<?php include 'app://frontend/partials/box_also_purchased_products.inc.php'; ?>

	</div>

</main>

<script>
	$('#box-product[data-id="<?php echo $product_id; ?>"] form[name="buy_now_form"]').on('input', function(e) {

		var regular_price = <?php echo currency::format_raw($regular_price); ?>,
			final_price = <?php echo currency::format_raw($final_price); ?>,
			tax = <?php echo currency::format_raw($total_tax); ?>;

		if (regular_price == 0) {
			return;
		}

		$(this).find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function() {
			if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
			if ($(this).data('price-adjust')) final_price += $(this).data('price-adjust');
			if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
		});

		$(this).find('select option:checked').each(function() {
			if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
			if ($(this).data('price-adjust')) final_price += $(this).data('price-adjust');
			if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
		});

		$(this).find('input[type!="radio"][type!="checkbox"]').each(function() {
			if ($(this).val() != '') {
				if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
				if ($(this).data('price-adjust')) final_price += $(this).data('price-adjust');
				if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
			}
		});

		$(this).find('.regular-price').text(regular_price.toMoney());
		$(this).find('.final-price').text(final_price.toMoney());
		$(this).find('.price').text(final_price.toMoney());
		$(this).find('.total-tax').text(tax.toMoney());
	});

	$('#box-product form[name="buy_now_form"] .options :input').on('change', function() {

		$.ajax({
			type: 'post',
			url: '<?php echo document::ilink('ajax/product_options_stock.json'); ?>',
			data: $(this).closest('form').serialize(),
			dataType: 'json',
			cache: false,
			success: function(data) {
				if (data.status == 'ok') {
					$('.stock-notice').text(data.notice).removeClass('warning');
				} else if (data.status == 'warning') {
					$('.stock-notice').text(data.notice).addClass('warning');
				} else {
					$('.stock-notice').html('');
				}
			}
		});
	});

	$('#box-product[data-id="{{product_id}}"] .social-bookmarks .link').off().on('click', function(e) {
		e.preventDefault();
		prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '{{link}}');
	});
</script>
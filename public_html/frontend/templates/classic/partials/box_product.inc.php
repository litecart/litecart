<article id="box-product" data-id="<?php echo $product_id; ?>" data-sku="<?php echo functions::escape_html($sku); ?>" data-name="<?php echo functions::escape_html($name); ?>" data-price="<?php echo currency::format_raw($final_price); ?>">

	<div class="card">
		<div class="card-body">
			<div class="row" style="margin: 0;">
				<div class="col-md-6">
					<div class="images row">

						<div class="col-xs-12">
							<a class="main-image" href="<?php echo document::href_rlink($image); ?>" data-toggle="lightbox" data-gallery="product">
								<?php echo functions::draw_thumbnail($image, 1024, 0, 'product', 'title="'. functions::escape_attr($name) .'"'); ?>
								<?php echo $sticker; ?>
							</a>
						</div>

						<?php foreach ($extra_images as $extra_image) { ?>
						<div class="col-xs-4">
							<a class="extra-image" href="<?php echo document::href_rlink($extra_image); ?>" data-toggle="lightbox" data-gallery="product">
								<?php echo functions::draw_thumbnail($image, 250, 0, 'product', 'title="'. functions::escape_attr($name) .'"'); ?>
							</a>
						</div>
						<?php } ?>

					</div>
				</div>

				<div class="col-md-6">
					<h1 class="title"><?php echo $name; ?></h1>

					<?php if ($short_description) { ?>
					<p class="short-description">
						<?php echo $short_description; ?>
					</p>
					<?php } ?>

					<?php if (!empty($brand)) { ?>
					<div class="brand">
						<a href="<?php echo functions::escape_html($brand['link']); ?>">
							<?php if ($brand['image']) { ?>
							<img src="<?php echo document::href_rlink($brand['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink($brand['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink($brand['image']['thumbnail_2x']); ?> 2x" alt="<?php echo functions::escape_html($brand['name']); ?>" title="<?php echo functions::escape_html($brand['name']); ?>">
							<?php } else { ?>
							<h3><?php echo $brand['name']; ?></h3>
							<?php } ?>
						</a>
					</div>
					<?php } ?>

					<?php if ($cheapest_shipping_fee !== null) { ?>
					<div class="cheapest-shipping" style="margin: 1em 0;">
						<?php echo functions::draw_fonticon('fa-truck'); ?> <?php echo strtr(language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">%price</strong>'), ['%price' => currency::format($cheapest_shipping_fee)]); ?>
					</div>
					<?php } ?>

					<?php if ($sku || $mpn || $gtin) { ?>
					<div class="codes" style="margin: 1em 0;">
						<?php if ($sku) { ?>
						<div class="sku">
							<?php echo language::translate('title_sku', 'SKU'); ?>: <span class="value"><?php echo $sku; ?></span>
						</div>
						<?php } ?>

						<?php if ($mpn) { ?>
						<div class="mpn">
							<?php echo language::translate('title_mpn', 'MPN'); ?>: <span class="value"><?php echo $mpn; ?></span>
						</div>
						<?php } ?>

						<?php if ($gtin) { ?>
						<div class="gtin">
							<?php echo language::translate('title_gtin', 'GTIN'); ?>: <span class="value"><?php echo $gtin; ?></span>
						</div>
						<?php } ?>
					</div>
					<?php } ?>

					<?php if (isset($quantity_available)) { ?>
					<div class="stock-status" style="margin: 1em 0;">

					 <?php if ($quantity_available) { ?>
						<div class="stock-available">
							<?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
							<span class="value"><?php echo $stock_status; ?></span>
						</div>

						<?php if ($delivery_status) { ?>
						<div class="stock-delivery">
							<?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>: <span class="value"><?php echo $delivery_status['name']; ?></span>
							<?php if (!empty($delivery_status['description'])) { ?>
							<div class="description"><?php echo $delivery_status['description']; ?></div>
							<?php } ?>
						</div>
						<?php } ?>

					 <?php } else { ?>
						<?php if ($sold_out_status) { ?>
							<div class="<?php echo empty($sold_out_status['orderable']) ? 'stock-partly-available' : 'stock-unavailable'; ?>">
								<?php echo language::translate('title_stock_status', 'Stock Status'); ?>: <span class="value"><?php echo $sold_out_status['name']; ?></span>
								<?php if (!empty($sold_out_status['description'])) { ?>
								<div class="description"><?php echo $sold_out_status['description']; ?></div>
								<?php } ?>
							</div>
						<?php } else { ?>
							<div class="stock-unavailable">
								<?php echo language::translate('title_stock_status', 'Stock Status'); ?>: <span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span>
								<?php if (!empty($sold_out_status['description'])) { ?>
								<div class="description"><?php echo $sold_out_status['description']; ?></div>
								<?php } ?>
							</div>
						<?php } ?>
					 <?php } ?>

					</div>
					<?php } ?>

					<?php if ($recommended_price) { ?>
					<div class="recommended-price" style="margin: 1em 0;">
						<?php echo language::translate('title_recommended_price', 'Recommended Price'); ?>: <span class="value"><?php echo currency::format($recommended_price); ?></span>
					</div>
					<?php } ?>

					<div class="buy_now" style="margin: 1em 0;">
						<?php echo functions::form_begin('buy_now_form', 'post'); ?>
						<?php echo functions::form_input_hidden('product_id', $product_id); ?>

						<?php if (count($stock_options) > 1) { ?>
						<div class="form-group">
							<label><?php echo language::translate('text_select_desired_option', 'Select desired option'); ?></label>
							<?php echo form_select_product_stock_option('stock_option_id', $product_id, true); ?>
						</div>
						<?php } else if (count($stock_options) == 1) { ?>
						<?php echo functions::form_input_hidden('stock_option_id', $stock_options[0]['stock_option_id']); ?>
						<?php } ?>

						<div class="price-wrapper">
							<?php if ($campaign_price) { ?>
							<del class="regular-price"><?php echo currency::format($regular_price); ?></del> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
							<?php } else if ($recommended_price) { ?>
							<del class="recommended-price"><?php echo currency::format($recommended_price); ?></del> <strong class="price"><?php echo currency::format($regular_price); ?></strong>
							<?php } else { ?>
							<span class="price"><?php echo currency::format($final_price); ?></span>
							<?php } ?>
						</div>

						<?php if ($tax_rates) { ?>
						<div class="tax" style="margin-bottom: 1em;">
							<?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <span class="total-tax"><?php echo currency::format($total_tax); ?></span>
						</div>
						<?php } ?>

						<?php if (!empty($campaign['end_date'])) { ?>
						<div class="offer-expires" style="margin-bottom: 1em;">
							<?php echo strtr(language::translate('text_offer_expires_on_date', 'The offer expires on %datetime.'), ['%datetime' => language::strftime(language::$selected['format_datetime'], strtotime($campaign_price_end_date))]); ?>
						</div>
						<?php } ?>

						<?php if (!settings::get('catalog_only_mode') && (!isset($quantity_available) || $quantity_available > 0 || empty($sold_out_status) || !empty($sold_out_status['orderable']))) { ?>
						<div class="form-group" style="margin-bottom: 0;">
							<label><?php echo language::translate('title_quantity_available', 'Quantity Available'); ?></label>
							<div style="display: flex">
						<div class="input-group" style="flex: 0 1 150px;">
							<?php echo !empty($quantity_unit['decimals']) ? functions::form_input_decimal('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 'min="'. (float)$quantity_min .'"' . ($quantity_max ? 'max="'. (float)$quantity_max .'"' : '') . ($quantity_step ? 'step="'. (float)$quantity_step .'"' : '')) : functions::form_input_number('quantity', isset($_POST['quantity']) ? true : 1, 'min="'. (int)$quantity_min .'"'. ($quantity_max ? 'max="'. (int)$quantity_max .'"' : '') . ($quantity_step ? 'step="'. (int)$quantity_step .'"' : '')); ?>
									<?php echo !empty($quantity_unit['name']) ? '<div class="input-group-text">'. $quantity_unit['name'] .'</div>' : ''; ?>
								</div>

								<div style="padding-inline-start: 1em;">
									<?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity_available <= 0 && !$orderable) ? ' disabled' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
								</div>
							</div>
						</div>
						<?php } ?>

						<div class="stock-notice"></div>

						<?php echo functions::form_end(); ?>
					</div>

					<?php if (isset($quantity_available) && $quantity_available <= 0 && !empty($sold_out_status) && empty($sold_out_status['orderable'])) { ?>
					<div class="out-of-stock-notice">
						<?php echo language::translate('description_item_is_out_of_stock', 'This item is currently out of stock and cannot be purchased.'); ?>
					</div>
					<?php } ?>

					<div class="social-bookmarks">
						<a class="link" href="#"><?php echo functions::draw_fonticon('fa-link', 'style="color: #333;"'); ?></a>
						<a class="twitter" href="<?php echo document::href_link('https://twitter.com/intent/tweet/', ['text' => $name .' - '. $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
						<a class="facebook" href="<?php echo document::href_link('https://www.facebook.com/sharer.php', ['u' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
						<a class="pinterest" href="<?php echo document::href_link('https://pinterest.com/pin/create/button/', ['url' => $link]); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
					</div>

				</div>
			</div>
		</div>
	</div>

	<?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') { ?>
	<div class="card">
		<div class="card-body">
			<div class="row">

				<?php if ($description) { ?>
				<div class="col-md-<?php echo ($description && $technical_data) ? 6 : 12; ?>">
					<h3><?php echo language::translate('title_description', 'Description'); ?></h3>
					<div class="description">
						<?php echo $description; ?>
					</div>
				</div>
				<?php } ?>

				<?php if ($technical_data) { ?>
				<div class="col-md-<?php echo ($description && $technical_data) ? 6 : 12; ?>">
					<h3><?php echo language::translate('title_technical_data', 'Technical Data'); ?></h3>
					<div class="technical-data">
						<table class="table table-striped table-hover">
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
				'<table class="table table-striped table-hover">',
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

</article>

<script>
	$('#box-product[data-id="<?php echo $product_id; ?>"] form[name="buy_now_form"]').on('input', function(e) {

		var regular_price = <?php echo currency::format_raw($regular_price); ?>;
		var sales_price = <?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>;
		var tax = <?php echo currency::format_raw($total_tax); ?>;

		$(this).find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function(){
			if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
			if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
			if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
		});

		$(this).find('select option:checked').each(function(){
			if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
			if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
			if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
		});

		$(this).find('input[type!="radio"][type!="checkbox"]').each(function(){
			if ($(this).val() != '') {
				if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
				if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
				if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
			}
		});

		$(this).find('.regular-price').text(regular_price.toMoney());
		$(this).find('.campaign-price').text(sales_price.toMoney());
		$(this).find('.price').text(sales_price.toMoney());
		$(this).find('.total-tax').text(tax.toMoney());
	});

	$('#box-product form[name="buy_now_form"] .options :input').change(function(){

		$.ajax({
			type: 'post',
			url: '<?php echo document::ilink('ajax/product_options_stock.json'); ?>',
			data: $(this).closest('form').serialize(),
			dataType: 'json',
			cache: false,

			error: function(jqXHR, textStatus, errorThrown) {
				console.log('error', errorThrown);
			},

			success: function(data){
				if (data.status == 'ok') {
					$('.stock-notice').text(data.notice).removeClass('warning').removeClass('notice');
				} else if (data.status == 'warning') {
					$('.stock-notice').text(data.notice).addClass('warning').removeClass('notice');
				} else if (data.status == 'notice') {
					$('.stock-notice').text(data.notice).addClass('notice').removeClass('warning');
				} else {
					$('.stock-notice').html('');
				}
			}
		});
	});

	$('#box-product[data-id="<?php echo $product_id; ?>"] .social-bookmarks .link').off().click(function(e){
		e.preventDefault();
		prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '<?php echo $link; ?>');
	});
</script>

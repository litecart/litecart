<article class="product" data-id="{{product_id}}" data-sku="{{sku|escape}}" data-name="{{name|escape}}" data-price="<?php echo currency::format_raw($campaign_price ?: $regular_price); ?>">
	<a class="link" href="<?php echo functions::escape_html($link) ?>">

		<div class="image-wrapper">
			<?php echo functions::draw_thumbnail($image, 320, 0, 'product', 'loading="lazy" alt="'. functions::escape_attr($name) .'"'); ?>
			{{sticker}}
		</div>

		<div class="info">

			<div class="name">
				{{name}}
			</div>

			<div class="short-description">
				{{short_description}}
			</div>

			<?php echo functions::draw_price_tag($regular_price, $final_price, currency::$selected['code']); ?>
		</div>
	</a>

	<div class="actions">
		<button class="preview btn btn-default btn-sm" data-toggle="lightbox" data-gallery="products" data-target="<?php echo functions::escape_html($link) ?>" data-seamless="true" data-require-window-width="768" data-max-width="980" title="<?php echo language::translate('title_view_product', 'View Product'); ?>">
			<?php echo functions::draw_fonticon('icon-zoom-in'); ?>
		</button>

		<?php if (!$num_stock_options) { ?>
		<button class="add-to-cart btn btn-success btn-sm" name="add_cart_product" title="<?php echo language::translate('title_add_to_cart', 'Add To Cart'); ?>">
			<?php echo functions::draw_fonticon('icon-shopping-cart'); ?>
		</button>
		<?php } ?>

		<?php if ($num_stock_options) { ?>
		<a class="view-options btn btn-default btn-sm" title="<?php echo language::translate('title_view_options', 'View Options'); ?>">
			<?php echo functions::draw_fonticon('icon-copy'); ?>
			<?php //echo $num_stock_options; ?>
		</a>
		<?php } ?>
	</div>
</article>

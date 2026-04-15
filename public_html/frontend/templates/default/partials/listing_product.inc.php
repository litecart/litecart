<article class="product" data-id="<?php echo (int)$product_id; ?>" data-sku="<?php echo f::escape_attr($sku); ?>" data-name="<?php echo f::escape_attr($name); ?>" data-price="<?php echo currency::format_raw($final_price); ?>">
	<a class="link" href="<?php echo f::escape_html($link) ?>">

		<div class="image-wrapper">
			<?php echo f::draw_thumbnail($image, 320, 0, 'product', 'loading="lazy" alt="'. f::escape_attr($name) .'"'); ?>
			<?php echo $sticker; ?>
		</div>

		<div class="info">

			<div class="name">
				<?php echo f::escape_html($name); ?>
			</div>

      <?php if ($rating) { ?>
      <div class="rating" style="font-size: .75em;">
        <?php echo functions::draw_rating($rating); ?>
      </div>
      <?php } ?>

			<div class="short-description">
				<?php echo f::escape_html($short_description); ?>
			</div>

			<?php echo f::draw_price_tag($regular_price, $final_price, currency::$selected['code']); ?>
		</div>
	</a>

	<div class="actions">
		<button class="preview btn btn-default btn-sm" data-toggle="lightbox" data-gallery="products" data-target="<?php echo f::escape_html($link) ?>" data-seamless="true" data-require-window-width="768" data-max-width="980" title="<?php echo t('title_view_product', 'View Product'); ?>">
			<?php echo f::draw_fonticon('icon-zoom-in'); ?>
		</button>

		<?php if ($is_favourite) { ?>
		<button class="favourites btn btn-default btn-sm" name="remove_from_favourites" title="<?php echo t('title_remove_favourites', 'Remove From Favourites'); ?>">
			<?php echo functions::draw_fonticon('icon-heart', 'style="color: #c00;"'); ?>
		</button>
		<?php } else { ?>
		<button class="favourites btn btn-default btn-sm" name="add_to_favourites" title="<?php echo t('title_add_to_favourites', 'Add To Favourites'); ?>">
			<?php echo functions::draw_fonticon('icon-heart-o'); ?>
		</button>
		<?php } ?>

		<?php if (!$num_stock_options) { ?>
		<button class="add-to-cart btn btn-success btn-sm" name="add_cart_product" title="<?php echo t('title_add_to_cart', 'Add To Cart'); ?>">
			<?php echo f::draw_fonticon('icon-shopping-cart'); ?>
		</button>
		<?php } ?>

		<?php if ($num_stock_options) { ?>
		<a class="view-options btn btn-default btn-sm" href="<?php echo f::escape_html($link) ?>" title="<?php echo t('title_view_options', 'View Options'); ?>">
			<?php echo f::draw_fonticon('icon-copy'); ?>
			<?php //echo $num_stock_options; ?>
		</a>
		<?php } ?>
	</div>
</article>

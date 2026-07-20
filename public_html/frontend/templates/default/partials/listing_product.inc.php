<article class="product" data-id="<?php echo (int)$product_id; ?>" data-sku="<?php echo f::escape_attr($sku); ?>" data-name="<?php echo f::escape_attr($name); ?>" data-price="<?php echo currency::format_raw($final_price); ?>" aria-label="<?php echo f::escape_attr($name); ?>">
	<a class="link" href="<?php echo f::escape_html($link) ?>" aria-label="<?php echo f::escape_attr($name); ?>">

		<div class="image-wrapper">
			<?php echo f::draw_thumbnail($image, 320, 0, 'product', 'loading="lazy" alt="'. f::escape_attr($name) .'"'); ?>
			<?php echo $sticker; ?>
		</div>

		<div class="info">

			<div class="name">
				<?php echo f::escape_html($name); ?>
			</div>

			<?php if ($rating) { ?>
			<div class="rating" style="font-size: .75em;" aria-label="<?php echo f::escape_attr(t('title_rating', 'Rating') .': '. round($rating * 2) .'/10'); ?>">
				<?php echo f::draw_rating($rating); ?>
			</div>
			<?php } ?>

			<div class="short-description">
				<?php echo f::escape_html($short_description); ?>
			</div>

			<?php echo f::draw_price_tag($regular_price, $final_price, currency::$selected['code']); ?>
		</div>
	</a>

	<div class="actions">
		<button type="button" class="preview btn btn-default btn-sm" data-toggle="lightbox" data-gallery="products" data-target="<?php echo f::escape_html($link) ?>" data-seamless="true" data-require-window-width="768" data-max-width="980" title="<?php echo f::escape_attr(t('title_view_product', 'View Product')); ?>" aria-label="<?php echo f::escape_attr(t('title_view_product', 'View Product')); ?>">
			<?php echo f::draw_fonticon('icon-zoom-in', 'aria-hidden="true"'); ?>
		</button>

		<?php if ($is_favourite) { ?>
		<button type="submit" class="favorites btn btn-default btn-sm" name="remove_from_favorites" title="<?php echo f::escape_attr(t('title_remove_favorites', 'Remove From favorites')); ?>" aria-label="<?php echo f::escape_attr(t('title_remove_favorites', 'Remove From favorites')); ?>" aria-pressed="true">
			<?php echo f::draw_fonticon('icon-heart', 'style="color: #c00;" aria-hidden="true"'); ?>
		</button>
		<?php } else { ?>
		<button type="submit" class="favorites btn btn-default btn-sm" name="add_to_favorites" title="<?php echo f::escape_attr(t('title_add_to_favorites', 'Add To Favourites')); ?>" aria-label="<?php echo f::escape_attr(t('title_add_to_favorites', 'Add To Favourites')); ?>" aria-pressed="false">
			<?php echo f::draw_fonticon('icon-heart-o', 'aria-hidden="true"'); ?>
		</button>
		<?php } ?>

		<?php if (!$num_stock_options) { ?>
		<button type="submit" class="add-to-cart btn btn-success btn-sm" name="add_cart_product" title="<?php echo f::escape_attr(t('title_add_to_cart', 'Add To Cart')); ?>" aria-label="<?php echo f::escape_attr(t('title_add_to_cart', 'Add To Cart') .': '. $name); ?>">
			<?php echo f::draw_fonticon('icon-shopping-cart', 'aria-hidden="true"'); ?>
		</button>
		<?php } ?>

		<?php if ($num_stock_options) { ?>
		<a class="view-options btn btn-default btn-sm" href="<?php echo f::escape_html($link) ?>" title="<?php echo f::escape_attr(t('title_view_options', 'View Options')); ?>" aria-label="<?php echo f::escape_attr(t('title_view_options', 'View Options') .': '. $name); ?>">
			<?php echo f::draw_fonticon('icon-copy', 'aria-hidden="true"'); ?>
			<?php //echo $num_stock_options; ?>
		</a>
		<?php } ?>
	</div>
</article>

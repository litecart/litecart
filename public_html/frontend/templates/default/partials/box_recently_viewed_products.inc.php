<section id="box-recently-viewed-products" class="hidden-xs">

	<h2 class="title"><?php echo language::translate('title_recently_viewed', 'Recently Viewed'); ?></h2>

	<div class="products">

		<?php foreach ($products as $product) { ?>
		<div class="product">
			<a class="link" href="<?php echo functions::escape_html($product['link']); ?>" title="<?php echo functions::escape_html($product['name']); ?>">
				<?php echo functions::draw_thumbnail($product['image'], 64, 0, 'product', 'alt=""'); ?>
			</a>
		</div>
		<?php } ?>

	</div>

</section>
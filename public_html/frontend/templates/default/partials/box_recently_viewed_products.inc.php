<section id="box-recently-viewed-products" class="hidden-xs" aria-label="<?php echo f::escape_attr(t('title_recently_viewed', 'Recently Viewed')); ?>">

	<h2 class="title"><?php echo t('title_recently_viewed', 'Recently Viewed'); ?></h2>

	<ul class="products" role="list">

		<?php foreach ($products as $product) { ?>
		<li class="product" role="listitem">
			<a class="link" href="<?php echo f::escape_html($product['link']); ?>" aria-label="<?php echo f::escape_attr($product['name']); ?>">
				<?php echo f::draw_thumbnail($product['image'], 64, 0, 'product', 'alt="'. f::escape_attr($product['name']) .'"'); ?>
			</a>
		</li>
		<?php } ?>

	</ul>

</section>
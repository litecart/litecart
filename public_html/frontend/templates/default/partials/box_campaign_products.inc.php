<section id="box-campaign-products" class="card" aria-label="<?php echo f::escape_attr(t('title_campaign_products', 'Campaign Products')); ?>">

	<div class="card-header">
		<h2 class="card-title"><?php echo t('title_campaign_products', 'Campaign Products'); ?></h2>
	</div>

	<div data-toggle="momentum-scroll">
		<ul class="listing products columns scroll-content" role="list">
			<?php foreach ($products as $product) echo '<li role="listitem">' . f::draw_listing_product($product) . '</li>'; ?>
		</ul>
	</div>

</section>
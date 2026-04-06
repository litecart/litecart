<section id="box-campaign-products" class="card">

	<div class="card-header">
		<h2 class="card-title"><?php echo t('title_campaign_products', 'Campaign Products'); ?></h2>
	</div>

	<div data-toggle="momentum-scroll">
		<div class="listing products columns scroll-content">
			<?php foreach ($products as $product) echo f::draw_listing_product($product); ?>
		</div>
	</div>

</section>
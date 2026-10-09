<section id="box-latest-products" class="card" aria-label="<?= f::escape_attr(t('title_latest_products', 'Latest Products')) ?>">

	<div class="card-header">
		<h2 class="card-title"><?= t('title_latest_products', 'Latest Products') ?></h2>
	</div>

	<div data-toggle="momentum-scroll">
		<ul class="listing products columns scroll-content" role="list">
			<?php foreach ($products as $product) echo '<li role="listitem">' . f::draw_listing_product($product) . '</li>'; ?>
		</ul>
	</div>

</section>
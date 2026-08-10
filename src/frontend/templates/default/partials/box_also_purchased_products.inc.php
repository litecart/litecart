<section id="box-also-purchased-products" class="card" aria-label="<?php echo f::escape_attr(t('title_also_purchased_products', 'Also Purchased Products')); ?>">

	<div class="card-header">
		<h2 class="card-title"><?php echo t('title_also_purchased_products', 'Also Purchased Products'); ?></h2>
	</div>

	<div class="card-body">
		<ul class="listing products mini-columns" role="list">
			<?php foreach ($products as $product) echo '<li role="listitem">' . f::draw_listing_product($product) . '</li>'; ?>
		</ul>
	</div>

</section>
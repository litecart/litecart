<section id="box-favorites" class="card" aria-label="<?= f::escape_attr(t('text_dont_forget_favorites', 'Don\'t forget your favorites')) ?>">

	<div class="card-header">
		<h2 class="card-title"><?= t('text_dont_forget_favorites', 'Don\'t forget your favorites') ?></h2>
	</div>

	<div class="card-body">
		<ul class="listing products mini-columns" role="list">
			<?php foreach ($products as $product) echo '<li role="listitem">' . f::draw_listing_product($product) . '</li>'; ?>
		</ul>
	</div>

</section>
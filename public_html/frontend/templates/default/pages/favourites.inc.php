<main id="main" class="container">
	<div class="card" aria-label="<?php echo f::escape_attr(t('title_favourites', 'Favourites')); ?>">
		<div class="card-header">
			<h1 class="card-title"><?php echo t('title_favourites', 'Favourites'); ?></h1>
		</div>
		<div class="card-body">

			<?php if (empty($products)) { ?>

			<p><?php echo t('text_you_have_not_added_any_favourites', 'You have not added any favourites.'); ?></p>

			<?php } else { ?>

			<ul class="listing products columns" role="list" aria-label="<?php echo f::escape_attr(t('title_favourite_products', 'Favourite Products')); ?>">
				<?php foreach ($products as $listing_product) echo '<li role="listitem">' . f::draw_listing_product($listing_product) . '</li>'; ?>
			</ul>

			<?php } ?>

		</div>
	</div>
</main>

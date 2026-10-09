<main id="main" class="container">
	<div class="card" aria-label="<?= f::escape_attr(t('title_favorites', 'Favorites')) ?>">
		<div class="card-header">
			<h1 class="card-title"><?= t('title_favorites', 'Favorites') ?></h1>
		</div>
		<div class="card-body">

			<?php if (empty($products)) { ?>

			<p><?= t('text_you_have_not_added_any_favorites', 'You have not added any favorites.') ?></p>

			<?php } else { ?>

			<ul class="listing products columns" role="list" aria-label="<?= f::escape_attr(t('title_favourite_products', 'Favourite Products')) ?>">
				<?php foreach ($products as $listing_product) echo '<li role="listitem">' . f::draw_listing_product($listing_product) . '</li>'; ?>
			</ul>

			<?php } ?>

		</div>
	</div>
</main>

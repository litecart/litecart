<main id="main" class="container">
	<div class="card">
		<div class="card-header">
			<h1 class="card-title"><?php echo t('title_favourites', 'Favourites'); ?></h1>
		</div>
		<div class="card-body">

			<?php if (empty($products)) { ?>

			<p><?php echo t('text_you_have_not_added_any_favourites', 'You have not added any favourites.'); ?></p>

			<?php } else { ?>

			<div class="listing products columns">
				<?php foreach ($products as $listing_product) echo f::draw_listing_product($listing_product); ?>
			</div>

			<?php } ?>

		</div>
	</div>
</main>

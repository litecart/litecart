<section id="box-categories">

	<h1><?php echo language::translate('title_browse_by_category', 'Browse By Category'); ?></h1>

	<div class="listing categories">
		<?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
	</div>

</section>
<style>
#box-categories {
	margin-top: 3em;
}
</style>

<section id="box-categories">

	<h2><?php echo language::translate('title_browse_by_category', 'Browse By Category'); ?></h2>

	<div class="listing categories">
		<?php foreach ($categories as $category) echo functions::draw_listing_category($category); ?>
	</div>

</section>
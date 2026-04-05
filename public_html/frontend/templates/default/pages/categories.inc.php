<style>
#box-categories {
	margin-top: 3em;
}
</style>

<main id="main" class="container">
	<div id="content">
		{{breadcrumbs}}
		{{notices}}

		<section id="box-categories">

			<h2><?php echo t('title_browse_by_category', 'Browse By Category'); ?></h2>

			<div class="listing categories">
				<?php foreach ($categories as $category) echo f::draw_listing_category($category); ?>
			</div>

		</section>
	</div>
</main>

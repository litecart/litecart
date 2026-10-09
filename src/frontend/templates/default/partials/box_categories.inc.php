<style>
#box-categories {
	margin-top: 3em;
}
</style>

<section id="box-categories" aria-label="<?= f::escape_attr(t('title_browse_by_category', 'Browse By Category')) ?>">

	<h2><?= t('title_browse_by_category', 'Browse By Category') ?></h2>

	<ul class="listing categories" role="list">
		<?php foreach ($categories as $category) echo '<li role="listitem">' . f::draw_listing_category($category) . '</li>'; ?>
	</ul>

</section>
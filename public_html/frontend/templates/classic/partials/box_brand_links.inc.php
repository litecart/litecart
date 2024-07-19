<section id="box-brand-links" class="box">

	<h2 class="title"><?php echo language::translate('title_brands', 'Brands'); ?></h2>

	<nav class="nav nav-stacked nav-pills">
		<?php foreach ($brands as $brand) { ?>
		<a class="nav-link<?php if (!empty($brand['active'])) echo ' active'; ?>" href="<?php echo functions::escape_html($brand['link']); ?>"><?php echo $brand['name']; ?></a>
		<?php } ?>
	</nav>

</section>

<section id="box-brand-links" aria-label="<?php echo f::escape_attr(t('title_brands', 'Brands')); ?>">

	<h2 class="title"><?php echo t('title_brands', 'Brands'); ?></h2>

	<nav class="pills" aria-label="<?php echo f::escape_attr(t('title_brand_navigation', 'Brand navigation')); ?>">
		<?php foreach ($brands as $brand) { ?>
		<a class="pill-item<?php if (!empty($brand['active'])) echo ' active'; ?>" href="<?php echo f::escape_html($brand['link']); ?>"<?php if (!empty($brand['active'])) echo ' aria-current="page"'; ?>>
			<?php echo $brand['name']; ?>
		</a>
		<?php } ?>
	</nav>

</section>

<section id="box-brand-links">

	<h2 class="title"><?php echo t('title_brands', 'Brands'); ?></h2>

	<nav class="pills">
		<?php foreach ($brands as $brand) { ?>
		<a class="pill-item<?php if (!empty($brand['active'])) echo ' active'; ?>" href="<?php echo functions::escape_html($brand['link']); ?>">
			<?php echo $brand['name']; ?>
		</a>
		<?php } ?>
	</nav>

</section>

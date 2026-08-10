<style>
#box-brand-logotypes .card-body a {
	display: inline-block;
}
</style>

<section id="box-brand-logotypes" class="card hidden-xs hidden-sm" style="margin-bottom: 2em;" aria-label="<?php echo f::escape_attr(t('title_brands', 'Brands')); ?>">
	<div class="card-body text-center">
		<?php foreach ($brands as $brand) { ?>
		<a href="<?php echo f::escape_html($brand['link']); ?>" aria-label="<?php echo f::escape_attr($brand['name']); ?>">
			<?php echo f::draw_thumbnail($brand['image'], 240, 80, '', 'alt="'. f::escape_attr($brand['name']) .'" style="margin: 0px 15px;"'); ?>
		</a>
		<?php } ?>
	</div>
</section>

<script>
	$('.rightArrow').on('click', function () {
		let leftPos = $('.innerWrapper').scrollLeft();
		$('.innerWrapper').animate({scrollLeft: leftPos + 200}, 800);
	});
</script>

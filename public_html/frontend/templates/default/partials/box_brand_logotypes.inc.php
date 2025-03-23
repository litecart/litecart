<style>
#box-brand-logotypes .card-body a {
	display: inline-block;
}
</style>

<section id="box-brand-logotypes" class="card hidden-xs hidden-sm" style="margin-bottom: 2em;">
	<div class="card-body text-center">
		<?php foreach ($brands as $brand) { ?>
		<a href="<?php echo functions::escape_html($brand['link']); ?>">
			<?php echo functions::draw_thumbnail($brand['image'], 240, 80, '', 'alt="'. functions::escape_attr($brand['name']) .'" style="margin: 0px 15px;"'); ?>
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

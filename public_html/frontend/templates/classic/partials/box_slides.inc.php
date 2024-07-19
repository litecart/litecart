<section id="box-slides" class="box carousel slide" data-ride="carousel">

	<div class="carousel-inner">
<?php
	$output = [];

	foreach ($slides as $key => $slide) {
		$output[] = '<div class="item'. (($key == 0) ? ' active' : '') .'">';

		if ($slide['link']) {
			$output[] = '<a href="'. functions::escape_attr($slide['link']) .'">';
		}

		$output[] = '<img src="'. document::href_rlink($slide['image']) .'" alt="'. functions::escape_attr($slide['name']) .'" style="width: 100%;">';

		if (!empty($slide['caption'])) {
			$output[] = '<div class="carousel-caption">'. $slide['caption'] .'</div>';
		}

		if ($slide['link']) {
			$output[] = '</a>';
		}

		$output[] = '</div>';
	}

	echo implode(PHP_EOL, $output);
?>
	</div>

	<?php if (count($slides) > 1) { ?>
	<ol class="carousel-indicators">
		<?php foreach ($slides as $key => $slide) echo '<li data-target="#box-slides" data-slide-to="'.  $key .'"'. (($key == 0) ? ' class="active"' : '') .'></li>'; ?>
	</ol>

	<a class="left carousel-control" href="#box-slides" data-slide="prev">
		<span class="icon-prev"><?php echo functions::draw_fonticon('fa-chevron-left'); ?></span>
	</a>
	<a class="right carousel-control" href="#box-slides" data-slide="next">
		<span class="icon-next"><?php echo functions::draw_fonticon('fa-chevron-right'); ?></span>
	</a>
	<?php } ?>
</section>

<div class="carousel slide" data-ride="carousel" aria-roledescription="carousel" aria-label="<?php echo f::escape_attr(t('title_carousel', 'Featured carousel')); ?>">

	<div class="carousel-inner" aria-live="polite">
		<?php foreach ($items as $key => $item) echo '<div class="item'. (($key == 0) ? ' active' : '') .'"'. (($key == 0) ? ' aria-current="true"' : '') .' role="group" aria-roledescription="slide" aria-label="'. f::escape_attr(t('title_slide', 'Slide') .' '. ($key + 1) .' '. t('title_of', 'of') .' '. count($items)) .'">'. $item. '</div>'; ?>
	</div>

	<?php if (count($items) > 1) { ?>
	<ol class="carousel-indicators" role="tablist" aria-label="<?php echo f::escape_attr(t('title_carousel_indicators', 'Carousel indicators')); ?>">
		<?php foreach ($items as $key => $item) echo '<li data-target="#box-jumbotron" data-slide-to="'. $key .'"'. (($key == 0) ? ' class="active"' : '') .' role="tab"'. (($key == 0) ? ' aria-selected="true"' : ' aria-selected="false"') .' aria-label="'. f::escape_attr(t('title_slide', 'Slide') .' '. ($key + 1)) .'"></li>'; ?>
	</ol>

	<a class="left carousel-control" href="#" data-slide="prev" role="button" aria-label="<?php echo f::escape_attr(t('title_previous', 'Previous')); ?>">
		<span class="icon-prev" aria-hidden="true"><?php echo f::draw_fonticon('icon-chevron-left'); ?></span>
	</a>
	<a class="right carousel-control" href="#" data-slide="next" role="button" aria-label="<?php echo f::escape_attr(t('title_next', 'Next')); ?>">
		<span class="icon-next" aria-hidden="true"><?php echo f::draw_fonticon('icon-chevron-right'); ?></span>
	</a>
	<?php } ?>
	</div>

<div class="carousel slide" data-ride="carousel">

	<div class="carousel-inner">
		<?php foreach ($items as $key => $item) echo '<div class="item'. (($key == 0) ? ' active' : '') .'">'. $item. '</div>'; ?>
	</div>

	<?php if (count($items) > 1) { ?>
	<ol class="carousel-indicators">
		<?php foreach ($items as $key => $item) echo '<li data-target="#box-jumbotron" data-slide-to="'. $key .'"'. (($key == 0) ? ' class="active"' : '') .'></li>'; ?>
	</ol>

	<a class="left carousel-control" href="#" data-slide="prev">
		<span class="icon-prev"><?php echo functions::draw_fonticon('icon-chevron-left'); ?></span>
	</a>
	<a class="right carousel-control" href="#" data-slide="next">
		<span class="icon-next"><?php echo functions::draw_fonticon('icon-chevron-right'); ?></span>
	</a>
	<?php } ?>
	</div>

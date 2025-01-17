<article class="category" data-id="{{category_id}}" data-name="{{name|escape}}">
	<a class="link" href="{{link|escape}}">
		<?php echo functions::draw_thumbnail($image, 480, 0, 'category', 'loading="lazy" alt="'. functions::escape_attr($name) .'"'); ?>
		<div class="caption">
			<div>{{name}}</div>
		</div>
	</a>
</article>
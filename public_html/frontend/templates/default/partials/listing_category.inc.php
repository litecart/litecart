<article class="category" data-id="{{category_id}}" data-name="{{name|escape}}">
	<a class="link" href="{{link|escape}}">
	<div class="flex flex-nogap">
		<div class="">
			<?php echo f::draw_thumbnail($image, 480, 0, 'category', 'loading="lazy" alt="'. f::escape_attr($name) .'"'); ?>
		</div>
		<div class="flex-grow caption">
			{{name}}
		</div>
	</div>
</a>
</article>
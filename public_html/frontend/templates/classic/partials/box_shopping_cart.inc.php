<a id="cart" class="text-center" href="<?php echo functions::escape_html($link); ?>">
	<div class="navbar-icon"><?php echo functions::draw_fonticon('icon-shopping-basket'); ?></div>
	<small class="hidden-xs"><?php echo language::translate('title_cart', 'Cart'); ?></small>
	<div class="badge quantity"><?php if ($num_items) echo $num_items; ?></div>
</a>
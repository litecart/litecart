<div id="cart" class="dropdown dropdown-end" aria-label="<?php echo f::escape_attr(t('title_shopping_cart', 'Shopping Cart')); ?>">

	<a href="{{link|escape}}" aria-label="<?php echo f::escape_attr(t('title_shopping_cart', 'Shopping Cart') .' - '. ($num_items ? $num_items .' '. t('title_items', 'items') : t('title_empty', 'empty'))); ?>">
		<img class="image" src="{{template_path}}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="">
		<div class="badge quantity" aria-hidden="true"><?php if ($num_items) echo $num_items; ?></div>
	</a>

	<ul class="dropdown-menu" role="menu" aria-label="<?php echo f::escape_attr(t('title_shopping_cart_items', 'Shopping Cart Items')); ?>">

		<li class="dropdown-item" role="presentation">
			<h2><?php echo t('title_shopping_cart', 'Shopping Cart'); ?></h2>
		</li>

		<?php foreach ($items as $key => $item) { ?>
		<li class="dropdown-item item" role="none">
			<div class="grid">

				<div class="col-2">
					<?php echo f::draw_thumbnail($item['image'], 64, 0, 'product', 'alt="'. f::escape_attr($item['name']) .'"'); ?>
				</div>

				<div class="col-8">
					<div>
						<a href="<?php echo f::escape_html($item['link']); ?>" class="name" role="menuitem">
							<?php echo f::escape_html($item['name']); ?>
						</a>
					</div>

					<div class="price">
						<?php echo currency::format($item['final_price']); ?>
					</div>
				</div>

				<div class="col-2 text-end">
					<button class="btn btn-danger btn-sm" name="remove_cart_item" type="submit" value="<?php echo f::escape_html($key); ?>" aria-label="<?php echo f::escape_attr(t('title_remove', 'Remove') .': '. $item['name']); ?>"><?php echo f::draw_fonticon('delete', 'aria-hidden="true"'); ?></button>
				</div>
			</div>

		</li>
		<?php } ?>

		<li class="new-item" aria-hidden="true"></li>

		<li class="dropdown-item text-end" role="presentation">
			<?php echo t('title_subtotal', 'Subtotal'); ?>: <?php echo currency::format(cart::$total['amount']); ?>
		</li>

		<li role="separator"><hr></li>

		<li style="margin-top: 2em;" role="presentation">
			<a class="btn btn-success btn-lg" href="{{link|escape}}" role="menuitem">
				<?php echo t('title_go_to_chekout', 'Go To Checkout'); ?>
			</a>
		</li>
	</ul>
</div>

<script>
	$('#cart > a').on('click', function(e) {
		e.preventDefault();
		$('body').toggleClass('cart-open');
	});
</script>

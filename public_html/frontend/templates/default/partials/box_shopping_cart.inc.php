<div id="cart" class="dropdown">

	<a href="{{link|escape}}">
		<img class="image" src="{{template_path}}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="">
		<div class="badge quantity"><?php if ($num_items) echo $num_items; ?></div>
	</a>

	<ul class="dropdown-menu dropdown-menu-right">

		<li>
			<h2><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></h2>
		</li>

		<?php foreach ($items as $key => $item) { ?>
		<li class="item">
			<div class="row">

				<div class="col-3">
					<?php echo functions::draw_thumbnail($item['image'], 64, 0, 'product', 'alt="'. functions::escape_attr($item['name']) .'"'); ?>
				</div>

				<div class="col-8">
					<div>
						<a href="<?php echo functions::escape_html($item['link']); ?>" class="name">
							<?php echo functions::escape_html($item['name']); ?>
						</a>
					</div>

					<div class="price">
						<?php echo currency::format($item['final_price']); ?>
					</div>
				</div>

				<div class="col-1 text-end">
					<button class="btn btn-danger btn-sm" name="remove_cart_item" type="submit" value="<?php echo functions::escape_html($key); ?>"><?php echo functions::draw_fonticon('delete'); ?></button>
				</div>
			</div>

		</li>
		<?php } ?>

		<li class="new-item"></li>
		<li class="text-end"><?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <?php echo currency::format(cart::$total['amount']); ?></li>

		<li><hr></li>

		<li><a class="btn btn-success btn-lg" href="{{link|escape}}"><?php echo language::translate('title_go_to_chekout', 'Go To Checkout'); ?></a></li>
	</ul>
</div>

<script>
	$('#cart > a').on('click', function(e){
		e.preventDefault();
		$('body').toggleClass('cart-open');
	});
</script>

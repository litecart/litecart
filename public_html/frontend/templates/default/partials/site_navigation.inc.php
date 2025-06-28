<div class="container">

	<?php if ($important_notice) { ?>
	<div id="important-notice">
		<?php echo functions::escape_html($important_notice); ?>
	</div>
	<?php } ?>

	<div id="site-navigation" class="navbar">

		<div class="navbar-brand">
			<a href="<?php echo document::href_ilink(''); ?>">
				<img src="<?php echo document::href_rlink('storage://images/symbol.svg'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>">
				<?php echo settings::get('store_name'); ?>
			</a>
		</div>

		<nav class="navbar-menu">
			<ul class="navbar-nav">

				<?php if ($categories) { ?>
				<li class="categories dropdown">
					<div class="navbar-item" data-toggle="dropdown">
						<?php echo t('title_catalog', 'Catalog'); ?>
					</div>

					<ul class="dropdown-menu">
						<?php foreach ($categories as $item) { ?>
						<li>
							<a class="navbar-item" href="<?php echo functions::escape_html($item['link']); ?>">
								<?php echo $item['title']; ?>
							</a>
						</li>
						<?php } ?>
					</ul>
				</li>
				<?php } ?>

				<?php /*if ($brands) { ?>
				<li class="brands dropdown">
					<a class="navbar-item" href="<?php echo document::href_ilink('brands'); ?>">
						<?php echo t('title_brands', 'Brands'); ?>
					</a>
				</li>
				<?php }*/ ?>
			</ul>

			<div class="navbar-search" data-hint="<?php echo functions::escape_html(''); ?>">
				<?php echo functions::form_begin('search_form', 'get', document::ilink('search')); ?>
				<div class="navbar-link dropdown">
					<?php echo functions::form_input_search('query', true, 'autocomplete="off" placeholder="'. t('title_search', 'Search') .'&hellip;"'); ?>
					<ul class="dropdown-menu" style="left: 0; right: 0;">
					</ul>
				</div>
				<?php echo functions::form_end(); ?>
			</div>

			<ul class="navbar-nav">

				<?php if ($pages) { ?>
				<li class="information dropdown">
					<div class="navbar-item" data-toggle="dropdown">
						<?php echo t('title_information', 'Information'); ?>
					</div>

					<ul class="dropdown-menu">
						<?php foreach ($pages as $item) { ?>
						<li>
							<a class="navbar-item" href="<?php echo functions::escape_html($item['link']); ?>">
								<?php echo $item['title']; ?>
							</a>
						</li>
						<?php } ?>
					</ul>
				</li>
				<?php } ?>

				<li class="contact">
					<a class="navbar-item" href="<?php echo document::href_ilink('contact'); ?>">
						<?php echo functions::draw_fonticon('icon-envelope hidden-xs hidden-sm hidden-md hidden-lg'); ?> <?php echo t('title_contact', 'Contact'); ?>
					</a>
				</li>

				<?php if (settings::get('accounts_enabled')) { ?>
				<?php if (customer::check_login()) { ?>
				<li class="account dropdown dropdown-end">

					<div class="navbar-item" data-toggle="dropdown">
						<?php echo functions::draw_fonticon('icon-user hidden-xs hidden-sm hidden-md hidden-lg'); ?>
						<span class="hidden-sm"><?php echo customer::check_login() ? customer::$data['firstname'] : t('title_sign_in', 'Sign In'); ?></span>
					</div>

					<ul class="dropdown-menu">

						<li>
							<a class="navbar-item" href="<?php echo document::href_ilink('account/edit'); ?>">
								<?php echo t('title_edit_account', 'Edit Account'); ?>
							</a>
						</li>

						<li>
							<a class="navbar-item" href="<?php echo document::href_ilink('account/addresses'); ?>">
								<?php echo t('title_addresses', 'Addresses'); ?>
							</a>
						</li>

						<li>
							<a class="navbar-item" href="<?php echo document::href_ilink('account/order_history'); ?>">
								<?php echo t('title_order_history', 'Order History'); ?>
							</a>
						</li>

						<li>
							<a class="navbar-item" href="<?php echo document::href_ilink('account/sign_out'); ?>">
								<?php echo t('title_sign_out', 'Sign Out'); ?>
							</a>
						</li>

					</ul>
				</li>
				<?php } else { ?>
				<li class="account">
					<a class="navbar-item" href="<?php echo document::href_ilink('account/sign_in'); ?>">
						<?php echo functions::draw_fonticon('icon-user hidden-xs hidden-sm hidden-md hidden-lg'); ?> <?php echo t('title_sign_in', 'Sign In'); ?>
					</a>
				</li>
				<?php } ?>
				<?php } ?>

				<?php if (!empty($regional_settings)) { ?>
				<li class="regional-settings">
					<a class="navbar-item" href="<?php echo functions::escape_attr($regional_settings['link']); ?>">
					<?php echo functions::draw_fonticon('icon-world hidden-xs hidden-sm hidden-md hidden-lg'); ?> <?php echo $regional_settings['title']; ?>
					</a>
				</li>
				<?php } ?>

				<li class="shopping-cart<?php if (!empty($shopping_cart['items'])) echo ' filled'; ?> dropdown dropdown-end">
					<div class="navbar-item" data-toggle="dropdown">
						<img class="img-responsive hidden-xs" src="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template') .'/images/'. (!empty($shopping_cart['items']) ? 'cart_filled.svg' : 'cart.svg')); ?>">
						<span class="hidden-sm hidden-md hidden-lg hidden-xl hidden-xxl"><?php echo t('title_shopping_cart', 'Shopping Cart'); ?></span>
						<span class="badge"><?php echo $shopping_cart['num_items']; ?></span>
					</div>

					<div class="dropdown-content" style="min-width: 275px; max-width: 480px;">

						<ul class="list-unstyled items">
							<?php foreach ($shopping_cart['items'] as $key => $item) { ?>
								<li class="item">

								<div class="grid">
									<div class="col-2">
										<?php echo functions::draw_thumbnail($item['image'], 64, 0, 'product', 'alt="'. functions::escape_attr($item['name']) .'"'); ?>
									</div>

									<div class="col-7">
										<div>
											<span class="quantity"><?php echo $item['quantity']; ?></span> &times;
											<a class="name" href="<?php echo document::href_ilink('product', ['product_id' => $item['product_id']]); ?>">
												<?php echo $item['name']; ?>
											</a>
										</div>
										<div class="price">
											<?php echo currency::format($item['price']); ?>
										</div>
									</div>

									<div class="col-3 text-end">
										<?php echo functions::form_button('remove_cart_item', [$key, functions::draw_fonticon('delete',)], 'submit', 'class="btn btn-danger btn-sm"'); ?>
									</div>
								</div>

							</li>
							<?php } ?>
						</ul>

						<div class="dropdown-item empty text-center">
							<span><?php echo t('text_your_shopping_cart_is_empty'), 'Your shopping cart is empty'; ?></span>
						</div>

						<div class="checkout" style="margin-top: 2em;">
							<a class="btn btn-success btn-block btn-lg" href="<?php echo document::href_ilink('shopping_cart'); ?>">
								<?php echo t('title_go_to_checkout', 'Go To Checkout'); ?> <?php echo functions::draw_fonticon('icon-arrow-right'); ?>
							</a>
						</div>

					</div>
				</li>
			</ul>
		</nav>

		<div class="navbar-toggle">
			<button type="button" class="btn btn-default navbar-toggler hidden-md hidden-lg hidden-xl hidden-xxl" data-toggle="offcanvas" data-target="#offcanvas">
				<span class="navbar-toggler-bar"></span>
				<span class="navbar-toggler-bar"></span>
				<span class="navbar-toggler-bar"></span>
			</button>
		</div>
	</div>

</div>

<script>
	$('.navbar .navbar-toggle').on('click', function() {
		$(this).closest('.navbar').toggleClass('expanded');
	});

	$('#site-navigation .search').on('click', function() {
		$(this).find('input[name="query"]').trigger('focus');
	});
</script>
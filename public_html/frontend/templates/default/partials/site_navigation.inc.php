<div id="site-navigation" class="navbar" role="navigation" aria-label="<?php echo f::escape_attr(t('title_primary_navigation', 'Primary navigation')); ?>">

	<div class="navbar-brand">
		<a href="<?php echo document::href_ilink(''); ?>" aria-label="<?php echo f::escape_attr(settings::get('store_name') .' - '. t('title_home', 'Home')); ?>">
			<img src="<?php echo document::href_rlink('storage://images/symbol.svg'); ?>" alt="<?php echo f::escape_attr(settings::get('store_name')); ?>" title="<?php echo f::escape_attr(settings::get('store_name')); ?>">
			<?php echo settings::get('store_name'); ?>
		</a>
	</div>

	<nav class="navbar-menu" aria-label="<?php echo f::escape_attr(t('title_primary_navigation', 'Primary navigation')); ?>">
		<ul class="navbar-nav">

			<?php if ($categories) { ?>
			<li class="categories dropdown">
				<div class="navbar-item" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php echo t('title_catalog', 'Catalog'); ?>
				</div>

				<ul class="dropdown-menu" role="menu">
					<?php foreach ($categories as $item) { ?>
					<li role="none">
						<a class="navbar-item" role="menuitem" href="<?php echo f::escape_html($item['link']); ?>">
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

		<div class="navbar-search" data-hint="<?php echo f::escape_html(''); ?>" role="search">
			<?php echo f::form_begin('search_form', 'get', document::ilink('search'), false, ['role' => 'search', 'aria-label' => f::escape_attr(t('title_search', 'Search'))]); ?>
			<div class="navbar-link dropdown">
				<label for="search-query" class="visually-hidden"><?php echo t('title_search', 'Search'); ?></label>
				<?php echo f::form_input_search('query', true, ['id' => 'search-query', 'autocomplete' => 'off', 'placeholder' => t('title_search', 'Search') . '…', 'aria-label' => f::escape_attr(t('title_search', 'Search'))]); ?>
				<ul class="dropdown-menu" style="left: 0; right: 0;" role="listbox" aria-label="<?php echo f::escape_attr(t('title_search_suggestions', 'Search suggestions')); ?>">
				</ul>
			</div>
			<?php echo f::form_end(); ?>
		</div>

		<ul class="navbar-nav">

			<?php if ($pages) { ?>
			<li class="information dropdown">
				<div class="navbar-item" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php echo t('title_information', 'Information'); ?>
				</div>

				<ul class="dropdown-menu" role="menu">
					<?php foreach ($pages as $item) { ?>
					<li role="none">
						<a class="navbar-item" role="menuitem" href="<?php echo f::escape_html($item['link']); ?>">
							<?php echo $item['title']; ?>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<?php } ?>

			<li class="contact">
				<a class="navbar-item" href="<?php echo document::href_ilink('contact'); ?>">
					<?php echo f::draw_fonticon('icon-envelope hidden-xs hidden-sm hidden-md hidden-lg', 'aria-hidden="true"'); ?> <?php echo t('title_contact', 'Contact'); ?>
				</a>
			</li>

			<?php if (settings::get('accounts_enabled')) { ?>
			<?php if (customer::check_login()) { ?>
			<li class="account dropdown dropdown-end">

				<div class="navbar-item" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<?php echo f::draw_fonticon('icon-user hidden-xs hidden-sm hidden-md hidden-lg', 'aria-hidden="true"'); ?>
					<span class="hidden-sm"><?php echo customer::check_login() ? customer::$data['firstname'] : t('title_sign_in', 'Sign In'); ?></span>
				</div>

				<ul class="dropdown-menu" role="menu">

					<li role="none">
						<a class="navbar-item" role="menuitem" href="<?php echo document::href_ilink('account/edit'); ?>">
							<?php echo t('title_edit_account', 'Edit Account'); ?>
						</a>
					</li>

					<li role="none">
						<a class="navbar-item" role="menuitem" href="<?php echo document::href_ilink('account/addresses'); ?>">
							<?php echo t('title_addresses', 'Addresses'); ?>
						</a>
					</li>

					<li role="none">
						<a class="navbar-item" role="menuitem" href="<?php echo document::href_ilink('account/order_history'); ?>">
							<?php echo t('title_order_history', 'Order History'); ?>
						</a>
					</li>

					<li role="none">
						<a class="navbar-item" role="menuitem" href="<?php echo document::href_ilink('account/sign_out'); ?>">
							<?php echo t('title_sign_out', 'Sign Out'); ?>
						</a>
					</li>

				</ul>
			</li>
			<?php } else { ?>
			<li class="account">
				<a class="navbar-item" href="<?php echo document::href_ilink('account/sign_in'); ?>">
					<?php echo f::draw_fonticon('icon-user hidden-xs hidden-sm hidden-md hidden-lg', 'aria-hidden="true"'); ?> <?php echo t('title_sign_in', 'Sign In'); ?>
				</a>
			</li>
			<?php } ?>
			<?php } ?>

			<?php if (!empty($regional_settings)) { ?>
			<li class="regional-settings">
				<a class="navbar-item" href="<?php echo f::escape_attr($regional_settings['link']); ?>">
				<?php echo f::draw_fonticon('icon-world hidden-xs hidden-sm hidden-md hidden-lg', 'aria-hidden="true"'); ?> <?php echo $regional_settings['title']; ?>
				</a>
			</li>
			<?php } ?>

			<li class="favourites<?php if (!empty($favourites['items'])) echo ' filled'; ?> dropdown dropdown-end">
				<a class="navbar-item" href="<?php echo document::href_ilink('favourites'); ?>" aria-label="<?php echo f::escape_attr(t('title_favourites', 'Favourites')); ?>">
					<img class="img-responsive hidden-xs" src="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template') .'/images/'. (!empty($favourites['items']) ? 'favourites_filled.svg' : 'favourites.svg')); ?>" alt="">
					<span class="hidden-sm hidden-md hidden-lg hidden-xl hidden-xxl"><?php echo t('title_favourites', 'Favourites'); ?></span>
					<span class="badge" aria-label="<?php echo f::escape_attr(t('title_num_items', '{num} items', ['{num}' => (int)$favourites['num_items']])); ?>"><?php echo $favourites['num_items']; ?></span>
				</a>
			</li>

			<li class="shopping-cart<?php if (!empty($shopping_cart['items'])) echo ' filled'; ?> dropdown dropdown-end">
				<div class="navbar-item" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<img class="img-responsive hidden-xs" src="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template') .'/images/'. (!empty($shopping_cart['items']) ? 'cart_filled.svg' : 'cart.svg')); ?>" alt="">
					<span class="hidden-sm hidden-md hidden-lg hidden-xl hidden-xxl"><?php echo t('title_shopping_cart', 'Shopping Cart'); ?></span>
					<span class="badge" aria-label="<?php echo f::escape_attr(t('title_num_items', '{num} items', ['{num}' => (int)$shopping_cart['num_items']])); ?>"><?php echo $shopping_cart['num_items']; ?></span>
				</div>

				<div class="dropdown-content" style="min-width: 275px; max-width: 480px;">

					<ul class="list-unstyled items">
						<?php foreach ($shopping_cart['items'] as $key => $item) { ?>
							<li class="item">

							<div class="grid">
								<div class="col-2">
									<?php echo f::draw_thumbnail($item['image'], 64, 0, 'product', 'alt="'. f::escape_attr($item['name']) .'"'); ?>
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
									<?php echo f::form_button('remove_cart_item', [$key, f::draw_fonticon('delete',)], 'submit', ['class' => 'btn btn-danger btn-sm']); ?>
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
							<?php echo t('title_go_to_checkout', 'Go To Checkout'); ?> <?php echo f::draw_fonticon('icon-arrow-right', 'aria-hidden="true"'); ?>
						</a>
					</div>

				</div>
			</li>
		</ul>
	</nav>

	<div class="navbar-toggle">
		<button type="button" class="btn btn-default navbar-toggler hidden-md hidden-lg hidden-xl hidden-xxl" data-toggle="offcanvas" data-target="#offcanvas" aria-label="<?php echo f::escape_attr(t('title_toggle_navigation', 'Toggle navigation')); ?>" aria-expanded="false">
			<span class="navbar-toggler-bar" aria-hidden="true"></span>
			<span class="navbar-toggler-bar" aria-hidden="true"></span>
			<span class="navbar-toggler-bar" aria-hidden="true"></span>
		</button>
	</div>
</div>

<script>
	$('.navbar .navbar-toggle').on('click', function() {
		$(this).closest('.navbar').toggleClass('expanded');
	});

	$('#site-navigation .search').on('click', function() {
		$('input[name="query"]', $(this)).trigger('focus');
	});
</script>
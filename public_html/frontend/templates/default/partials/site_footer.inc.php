<footer id="site-footer" class="hidden-print">
	<div class="container">
		<div class="row">

			<?php if (settings::get('accounts_enabled')) { ?>
			<section class="account col-6 col-sm-4 col-md-3 col-lg-2">
				<h3 class="title"><?php echo language::translate('title_account', 'Account'); ?></h3>
				<ul class="list-unstyled">
					<li><a href="<?php echo document::href_ilink('regional_settings'); ?>"><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></a></li>
					<?php if (empty(customer::$data['id'])) { ?>
					<li><a href="<?php echo document::href_ilink('account/sign_up'); ?>"><?php echo language::translate('title_sign_up', 'Sign Up'); ?></a></li>
					<li><a href="<?php echo document::href_ilink('account/sign_in'); ?>"><?php echo language::translate('title_sign_in', 'Sign In'); ?></a></li>
					<?php } else { ?>
					<li><a href="<?php echo document::href_ilink('account/order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
					<li><a href="<?php echo document::href_ilink('account/edit'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
					<li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_sign_out', 'Sign Out'); ?></a></li>
					<?php } ?>
				</ul>
			</section>
			<?php } ?>

			<section class="information col-6 col-sm-4 col-md-3 col-lg-2">
				<h3 class="title"><?php echo language::translate('title_information', 'Information'); ?></h3>
				<ul class="list-unstyled">
					<?php foreach ($pages as $page) echo '<li><a href="'. functions::escape_attr($page['link']) .'">'. $page['title'] .'</a></li>' . PHP_EOL; ?>
				</ul>
			</section>

			<section class="store-info hidden-xs col-sm-4 col-md-3 col-lg-2">
				<h3 class="title"><?php echo language::translate('title_contact', 'Contact'); ?></h3>

				<p class="address">
					<?php echo nl2br(settings::get('store_postal_address')); ?>
				</p>

				<?php if (settings::get('store_phone')) { ?>
				<p class="phone">
					<?php echo functions::draw_fonticon('icon-phone'); ?> <a href="tel:<?php echo settings::get('store_phone'); ?>"><?php echo settings::get('store_phone'); ?></a>
				<p>
				<?php } ?>

				<p class="email">
					<?php echo functions::draw_fonticon('icon-envelope'); ?> <a href="mailto:<?php echo settings::get('store_email'); ?>"><?php echo settings::get('store_email'); ?></a>
				</p>
			</section>

			<section class="col-12 col-sm-12 col-md-3 col-lg-6 text-center" style="align-self: center;">
				<div class="logotype hidden-xs hidden-sm">
					<img class="responsive" src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>">
				</div>

				<?php if ($modules) { ?>
					<div id="modules" class="buttons">
					<?php foreach ($modules as $module) { ?>
						<img class="thumbnail responsive" src="<?php echo document::href_rlink($module['icon']); ?>" alt="">
					<?php } ?>
					</div>
				<?php } ?>

				<?php if ($social_bookmarks) { ?>
				<dic id="social-bookmarks" class="buttons">
					<?php foreach ($social_bookmarks as $bookmark) { ?>
					<a href="<?php echo functions::escape_html($bookmark['link']); ?>" target="_blank">
						<?php echo functions::draw_fonticon($bookmark['icon'] .'', 'title="'. functions::escape_attr($bookmark['title']) .'"'); ?>
					</a>
					<?php } ?>
				</dic>
				<?php } ?>
			</section>

		</div>
	</div>
</footer>

<section id="copyright">
	<div class="container notice">
		<!-- LiteCart is provided free under license CC BY-ND 4.0 - https://creativecommons.org/licenses/by-nd/4.0/. Removing the link back to litecart.net without permission is a violation - https://www.litecart.net/addons/172/removal-of-attribution-link -->
		Copyright &copy; <?php echo date('Y'); ?> <?php echo settings::get('store_name'); ?>. All rights reserved &middot; Powered by <a href="https://www.litecart.net" target="_blank" title="High performing e-commerce platform">LiteCart®</a>
	</div>
</section>

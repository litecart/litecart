<footer id="site-footer" class="hidden-print" role="contentinfo">
	<div class="container my-2">

		<div class="grid">
			<div class="col-md-9">

				<div class="grid">

					<?php if (settings::get('accounts_enabled')) { ?>
					<section class="account col-6 col-sm-4" aria-label="<?= f::escape_attr(t('title_account', 'Account')) ?>">

						<h3 class="title">
							<?= t('title_account', 'Account') ?>
						</h3>

						<ul class="list-unstyled">

							<li>
								<a href="<?= document::href_ilink('regional_settings') ?>">
									<?= t('title_regional_settings', 'Regional Settings') ?>
								</a>
							</li>

							<?php if (!customer::check_login()) { ?>

							<li>
								<a href="<?= document::href_ilink('account/sign_up') ?>">
									<?= t('title_sign_up', 'Sign Up') ?>
								</a>
							</li>

							<li>
								<a href="<?= document::href_ilink('account/sign_in') ?>">
									<?= t('title_sign_in', 'Sign In') ?>
								</a>
							</li>

							<?php } else { ?>

							<li>
								<a href="<?= document::href_ilink('account/order_history') ?>">
									<?= t('title_order_history', 'Order History') ?>
								</a>
							</li>

							<li>
								<a href="<?= document::href_ilink('account/edit') ?>">
									<?= t('title_edit_account', 'Edit Account') ?>
								</a>

							</li>
							<li>
								<a href="<?= document::href_ilink('account/sign_out') ?>">
									<?= t('title_sign_out', 'Sign Out') ?>
								</a>
							</li>

							<?php } ?>

							<li>
								<a href="<?= document::href_ilink('newsletter') ?>">
									<?= t('title_newsletter', 'Newsletter') ?>
								</a>
							</li>

						</ul>
					</section>
					<?php } ?>

					<section class="information col-6 col-sm-4" aria-label="<?= f::escape_attr(t('title_information', 'Information')) ?>">

						<h3 class="title">
							<?= t('title_information', 'Information') ?>
						</h3>

						<ul class="list-unstyled">
							<?php foreach ($pages as $page) echo '<li><a href="'. f::escape_attr($page['link']) .'">'. $page['title'] .'</a></li>' . PHP_EOL; ?>
						</ul>

					</section>

					<section class="store-info hidden-xs col-sm-4" aria-label="<?= f::escape_attr(t('title_contact', 'Contact')) ?>">

						<h3 class="title">
							<?= t('title_contact', 'Contact') ?>
						</h3>

						<p class="address">
							<?= nl2br(f::escape_html(settings::get('store_postal_address'))) ?>
						</p>

						<?php if (settings::get('store_phone')) { ?>
						<p class="phone">
							<?= f::draw_fonticon('icon-phone', 'aria-hidden="true"') ?> <a href="tel:<?= f::escape_attr(settings::get('store_phone')) ?>"><?= f::escape_html(settings::get('store_phone')) ?></a>
						</p>
						<?php } ?>

						<p class="email">
							<?= f::draw_fonticon('icon-envelope', 'aria-hidden="true"') ?> <a href="mailto:<?= f::escape_attr(settings::get('store_email')) ?>"><?= f::escape_html(settings::get('store_email')) ?></a>
						</p>

					</section>
				</div>

			</div>

			<div class="col-md-3">

				<section class="text-center">

					<div class="facility hidden-xs hidden-sm">
						<a href="<?= document::href_rlink('storage://images/illustration/facility.jpg') ?>" title="<?= settings::get('store_name') ?>" data-toggle="lightbox">
							<img class="responsive" src="<?= document::href_rlink('storage://images/illustration/facility.jpg') ?>" alt="<?= settings::get('store_name') ?>" title="<?= settings::get('store_name') ?>">
						</a>
					</div>

					<?php if ($modules) { ?>
					<div id="modules" class="buttons" aria-label="<?= f::escape_attr(t('title_payment_methods', 'Payment Methods')) ?>">
						<?php foreach ($modules as $module) { ?>
						<img class="thumbnail responsive" src="<?= document::href_rlink($module['icon']) ?>" alt="<?= f::escape_attr(!empty($module['title']) ? $module['title'] : '') ?>">
						<?php } ?>
					</div>
					<?php } ?>

					<?php if ($social_bookmarks) { ?>
					<div id="social-bookmarks" class="buttons" aria-label="<?= f::escape_attr(t('title_social_media', 'Social Media')) ?>">
						<?php foreach ($social_bookmarks as $bookmark) { ?>
						<a href="<?= f::escape_html($bookmark['link']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= f::escape_attr($bookmark['title']) ?>">
							<?= f::draw_fonticon($bookmark['icon'] .'', 'aria-hidden="true"') ?>
						</a>
						<?php } ?>
					</div>
					<?php } ?>

				</section>

			</div>
		</div>
	</div>
</footer>

<section id="copyright">
	<div class="container">

		<div class="notice">
			<!-- LiteCart is provided free under license CC BY-ND 4.0 - https://creativecommons.org/licenses/by-nd/4.0/. Removing the link back to litecart.net without permission is a violation - https://www.litecart.net/addons/172/removal-of-attribution-link -->
			Copyright &copy; <?= date('Y') ?> <?= settings::get('store_name') ?>. All rights reserved &middot; Powered by <a href="https://www.litecart.net" target="_blank" title="High performing e-commerce platform">LiteCart®</a>
		</div>

	</div>
</section>
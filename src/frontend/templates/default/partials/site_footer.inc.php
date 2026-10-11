<footer id="site-footer" class="hidden-print" role="contentinfo">

	<div class="container">

		<div class="footer-layout">

			<div class="footer-brand">

				<div class="logotype">
					<img src="<?= document::href_rlink('storage://images/logotype.png') ?>" alt="<?= f::escape_attr(settings::get('store_name')) ?>" title="<?= f::escape_attr(settings::get('store_name')) ?>">
				</div>

				<?php if ($tagline = t('tagline', 'Tiny core, serious power.')) { ?>
				<p class="tagline"><?= f::escape_html($tagline) ?></p>
				<?php } ?>

				<?php if (!empty($social_bookmarks)) { ?>
				<div class="footer-social" aria-label="<?= f::escape_attr(t('title_social_media', 'Social Media')) ?>">
					<?php foreach ($social_bookmarks as $bookmark) { ?>
					<a href="<?= f::escape_attr($bookmark['link']) ?>" target="_blank" rel="noopener noreferrer" title="<?= f::escape_attr($bookmark['title']) ?>" aria-label="<?= f::escape_attr($bookmark['title']) ?>">
						<?= f::draw_fonticon($bookmark['icon'], 'aria-hidden="true"') ?>
					</a>
					<?php } ?>
				</div>
				<?php } ?>
			</div>

			<div class="footer-columns">

				<?php if (settings::get('accounts_enabled')) { ?>
				<section class="footer-column" aria-label="<?= f::escape_attr(t('title_account', 'Account')) ?>">
					<h3 class="title"><?= t('title_account', 'Account') ?></h3>
					<ul>
						<li><a href="<?= document::href_ilink('regional_settings') ?>"><?= t('title_regional_settings', 'Regional Settings') ?></a></li>

						<?php if (!customer::check_login()) { ?>
						<li><a href="<?= document::href_ilink('account/sign_up') ?>"><?= t('title_sign_up', 'Sign Up') ?></a></li>
						<li><a href="<?= document::href_ilink('account/sign_in') ?>"><?= t('title_sign_in', 'Sign In') ?></a></li>
						<?php } else { ?>
						<li><a href="<?= document::href_ilink('account/order_history') ?>"><?= t('title_order_history', 'Order History') ?></a></li>
						<li><a href="<?= document::href_ilink('account/edit') ?>"><?= t('title_edit_account', 'Edit Account') ?></a></li>
						<li><a href="<?= document::href_ilink('account/sign_out') ?>"><?= t('title_sign_out', 'Sign Out') ?></a></li>
						<?php } ?>

						<li><a href="<?= document::href_ilink('newsletter') ?>"><?= t('title_newsletter', 'Newsletter') ?></a></li>
					</ul>
				</section>
				<?php } ?>

				<?php if (!empty($pages)) { ?>
				<section class="footer-column" aria-label="<?= f::escape_attr(t('title_information', 'Information')) ?>">
					<h3 class="title"><?= t('title_information', 'Information') ?></h3>
					<ul>
						<?php foreach ($pages as $page) { ?>
						<li><a href="<?= f::escape_attr($page['link']) ?>"><?= f::escape_html($page['title']) ?></a></li>
						<?php } ?>
					</ul>
				</section>
				<?php } ?>

				<section class="footer-column" aria-label="<?= f::escape_attr(t('title_contact', 'Contact')) ?>">
					<h3 class="title"><?= t('title_contact', 'Contact') ?></h3>
					<ul>
						<?php if ($store_email = settings::get('store_email')) { ?>
						<li><?= f::draw_fonticon('icon-envelope', 'aria-hidden="true"') ?> <a href="mailto:<?= f::escape_attr($store_email) ?>"><?= f::escape_html($store_email) ?></a></li>
						<?php } ?>
						<?php if ($store_phone = settings::get('store_phone')) { ?>
						<li><?= f::draw_fonticon('icon-phone', 'aria-hidden="true"') ?> <a href="tel:<?= f::escape_attr($store_phone) ?>"><?= f::escape_html($store_phone) ?></a></li>
						<?php } ?>
					</ul>
				</section>

				<section class="footer-column" aria-label="<?= f::escape_attr(t('title_facility', 'Facility')) ?>">
					<div class="facility">
						<a href="<?= document::href_rlink('storage://images/illustration/facility.jpg') ?>" title="<?= f::escape_attr(settings::get('store_name')) ?>" data-toggle="lightbox">
							<img src="<?= document::href_rlink('storage://images/illustration/facility.jpg') ?>" alt="<?= f::escape_attr(settings::get('store_name')) ?>" title="<?= f::escape_attr(settings::get('store_name')) ?>" style="max-width: 100%;">
						</a>
					</div>
				</section>

			</div>
		</div>

		<?php if (!empty($modules)) { ?>
		<div class="footer-modules" aria-label="<?= f::escape_attr(t('title_payment_methods', 'Payment & Shipping Methods')) ?>">
			<span class="label"><?= t('title_payment_methods', 'Payment & Shipping') ?></span>
			<?php foreach ($modules as $module) { ?>
			<img src="<?= document::href_rlink($module['icon']) ?>" alt="<?= f::escape_attr($module['title']) ?>">
			<?php } ?>
		</div>
		<?php } ?>

	</div>
</footer>

<section id="copyright">
	<div class="container">
		<span>
			Copyright &copy; <?= date('Y') ?> <?= f::escape_html(settings::get('store_name')) ?>. <?= t('text_all_rights_reserved', 'All rights reserved.') ?>
		</span>
		<span class="trademark">
			<?= t('powered_by', 'Powered by') ?> <a href="https://www.litecart.net" target="_blank" rel="noopener noreferrer" title="<?= f::escape_attr(t('title_litecart_platform', 'LiteCart e-commerce platform')) ?>">LiteCart&reg;</a>
		</span>
	</div>
</section>

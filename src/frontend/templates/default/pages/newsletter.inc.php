<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div class="grid">
		<div class="col-md-6">

			<section id="box-newsletter-subscribe" class="card" aria-label="<?= f::escape_attr(t('box_newsletter_subscribe:title', 'Subscribe to our newsletter!')) ?>">
				<div class="card-body">
					<h2><?= t('box_newsletter_subscribe:title', 'Subscribe to our newsletter!') ?></h2>

					<p>
						<?= t('box_newsletter_subscribe:description', 'Get the latest news and offers straight to your inbox. Subscribe now.') ?>
					</p>

					<?= f::form_begin('newsletter_subscribe_form', 'post', document::ilink('newsletter'), false, ['aria-label' => f::escape_attr(t('box_newsletter_subscribe:title', 'Subscribe to our newsletter!'))]) ?>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_firstname', 'First Name') ?></div>
									<?= f::form_input_text('firstname', true, ['autocomplete' => 'given-name']) ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_lastname', 'Last Name') ?></div>
									<?= f::form_input_text('lastname', true, ['autocomplete' => 'family-name']) ?>
								</label>
							</div>
						</div>

						<label class="form-group">
							<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
							<?= f::form_input_email('email', true, ['required' => true, 'autocomplete' => 'email']) ?>
						</label>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_country', 'Country') ?></div>
									<?= f::form_select_country('country_code', true, ['autocomplete' => 'country']) ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_language', 'Language') ?></div>
									<?= f::form_select_language('language_code', true) ?>
								</label>
							</div>
						</div>

						<?php if (settings::get('captcha')) { ?>
						<div class="grid">
							<div class="col-xs-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_captcha', 'CAPTCHA') ?></div>
									<?= f::form_captcha('newsletter_subscribe') ?>
								</label>
							</div>
						</div>
						<?php } ?>

						<?php if ($consent) { ?>
						<div class="form-group consent">
							<?= f::form_checkbox('terms_agreed', ['1', $consent], true, ['required' => true]) .'</label>' ?>
						</div>
						<?php } ?>

						<?= f::form_button('subscribe', t('title_subscribe', 'Subscribe')) ?>

					<?= f::form_end() ?>
				</div>
			</section>
		</div>

		<div class="col-md-6">
			<section id="box-newsletter-unsubscribe" class="card" aria-label="<?= f::escape_attr(t('box_newsletter_unsubscribe:title', 'Unsubscribe from our newsletter')) ?>">
				<div class="card-body">
					<h2><?= t('box_newsletter_unsubscribe:title', 'Unsubscribe from our newsletter') ?></h2>

					<?= f::form_begin('newsletter_unsubscribe_form', 'post', document::ilink('newsletter'), false, ['aria-label' => f::escape_attr(t('box_newsletter_unsubscribe:title', 'Unsubscribe from our newsletter'))]) ?>

						<label class="form-group">
							<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
							<?= f::form_input_email('email', true, ['required' => true, 'autocomplete' => 'email']) ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?= t('title_captcha', 'CAPTCHA') ?></div>
							<?= f::form_captcha('newsletter_unsubscribe') ?>
						</label>

						<?= f::form_button('unsubscribe', t('title_unsubscribe', 'Unsubscribe')) ?>

					<?= f::form_end() ?>
				</div>
			</section>
		</div>
	</div>
</main>

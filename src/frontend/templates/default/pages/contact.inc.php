<main id="main" class="container">
	{{notices}}

	<div class="grid">

		<div class="col-md-8">
			<section id="box-contact-us" class="card" aria-label="<?= f::escape_attr(t('title_contact_us', 'Contact Us')) ?>">
				<div class="card-body">

					<h1><?= t('title_contact_us', 'Contact Us') ?></h1>

					<?= f::form_begin('contact_form', 'post', null, true, ['aria-label' => f::escape_attr(t('title_contact_us', 'Contact Us'))]) ?>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_firstname', 'First Name') ?></div>
									<?= f::form_input_text('firstname', true, ['required' => true, 'autocomplete' => 'given-name']) ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?= t('title_lastname', 'Last Name') ?></div>
									<?= f::form_input_text('lastname', true, ['required' => true, 'autocomplete' => 'family-name']) ?>
								</label>
							</div>
						</div>

						<label class="form-group">
							<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
							<?= f::form_input_email('email', true, ['required' => true, 'autocomplete' => 'email']) ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?= t('title_subject', 'Subject') ?></div>
							<?= f::form_input_text('subject', true, ['required' => true, 'autocomplete' => 'off']) ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?= t('title_message', 'Message') ?></div>
							<?= f::form_textarea('message', true, ['required' => true, 'autocomplete' => 'off', 'style' => 'height: 250px;']) ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?= t('title_attachments', 'Attachments') ?></div>
							<?= f::form_input_file('attachments[]', ['multiple' => true, 'accept' => '.jpg,.jpeg,.png,.gif,.webp,.avif,.txt,.doc,.docx,.pdf,.mp4']) ?>
						</label>

						<?php if (settings::get('captcha')) { ?>
						<label class="form-group" style="max-width: 250px;">
							<div class="form-label"><?= t('title_captcha', 'CAPTCHA') ?></div>
							<?= f::form_captcha('contact_us') ?>
						</label>
						<?php } ?>

						<div>
							<?= f::form_button('send', t('title_send', 'Send'), 'submit', ['style' => 'font-weight: bold;']) ?>
						</div>

					<?= f::form_end() ?>
				</div>
			</section>
		</div>

		<div class="col-md-4">
			<article class="card" aria-label="<?= f::escape_attr(t('title_contact_details', 'Contact Details')) ?>">

				<div class="card-header">
					<h2 class="card-title"><?= t('title_contact_details', 'Contact Details') ?></h2>
				</div>

				<div class="card-body">

					<div class="address">
						<?= nl2br(f::escape_html(settings::get('store_postal_address'))) ?>
					</div>

					<?php if (settings::get('store_phone')) { ?>
					<div class="phone">
						<?= f::draw_fonticon('icon-phone', 'aria-hidden="true"') ?> <a href="tel:<?= f::escape_attr(settings::get('store_phone')) ?>"><?= f::escape_html(settings::get('store_phone')) ?></a>
					</div>
					<?php } ?>

					<div class="email">
						<?= f::draw_fonticon('icon-envelope', 'aria-hidden="true"') ?> <a href="mailto:<?= f::escape_attr(settings::get('store_email')) ?>"><?= f::escape_html(settings::get('store_email')) ?></a>
					</div>

				</div>

			</article>
		</div>
	</div>

</main>
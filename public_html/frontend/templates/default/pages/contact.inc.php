<main id="main" class="container">

	<div class="grid">

		<div class="col-md-8">
			<section id="box-contact-us" class="card">
				<div class="card-body">
					{{notices}}

					<h1><?php echo t('title_contact_us', 'Contact Us'); ?></h1>

					<?php echo functions::form_begin('contact_form', 'post', null, true); ?>

						<div class="grid">
							<div class="col-md-6">
								<div class="form-group">
									<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
									<?php echo functions::form_input_text('firstname', true, 'required'); ?>
								 </div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
									<?php echo functions::form_input_text('lastname', true, 'required'); ?>
								 </div>
							</div>
						</div>

						<div class="form-group">
							<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
							<?php echo functions::form_input_email('email', true, 'required'); ?>
						</div>

						<div class="form-group">
							<div class="form-label"><?php echo t('title_subject', 'Subject'); ?></div>
							<?php echo functions::form_input_text('subject', true, 'required'); ?>
						 </div>

						<div class="form-group">
							<div class="form-label"><?php echo t('title_message', 'Message'); ?></div>
							<?php echo functions::form_textarea('message', true, 'required style="height: 250px;"'); ?>
						 </div>

						<div class="form-group">
							<div class="form-label"><?php echo t('title_attachments', 'Attachments'); ?></div>
							<?php echo functions::form_input_file('attachments[]', 'multiple accept=".jpg,.jpeg,.png,.gif,.webp,.avif,.txt,.doc,.docx,.pdf,.mp4"'); ?>
						</div>

						<?php if (settings::get('captcha_enabled')) { ?>
						<div class="form-group" style="max-width: 250px;">
							<div class="form-label"><?php echo t('title_captcha', 'CAPTCHA'); ?></div>
							<?php echo functions::form_captcha('contact_us'); ?>
						 </div>
						<?php } ?>

						<div>
							<?php echo functions::form_button('send', t('title_send', 'Send'), 'submit', 'style="font-weight: bold;"'); ?>
						</div>

					<?php echo functions::form_end(); ?>
				</div>
			</section>
		</div>

		<div class="col-md-4">
			<article class="card">

				<div class="card-header">
					<h2 class="card-title"><?php echo t('title_contact_details', 'Contact Details'); ?></h2>
				</div>

				<div class="card-body">

					<div class="address">
						<?php echo nl2br(settings::get('store_postal_address')); ?>
					</div>

					<?php if (settings::get('store_phone')) { ?>
					<div class="phone">
						<?php echo functions::draw_fonticon('icon-phone'); ?> <a href="tel:<?php echo settings::get('store_phone'); ?>"><?php echo settings::get('store_phone'); ?></a>
					</div>
					<?php } ?>

					<div class="email">
						<?php echo functions::draw_fonticon('icon-envelope'); ?> <a href="mailto:<?php echo settings::get('store_email'); ?>"><?php echo settings::get('store_email'); ?></a>
					</div>

				</div>

			</article>
		</div>
	</div>

</main>
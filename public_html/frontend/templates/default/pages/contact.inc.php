<main id="main" class="container">

	<div class="grid">

		<div class="col-md-8">
			<section id="box-contact-us" class="card">
				<div class="card-body">
					{{notices}}

					<h1><?php echo t('title_contact_us', 'Contact Us'); ?></h1>

					<?php echo f::form_begin(
						name      : 'contact_form',
						method    : 'post',
						action    : null,
						multipart : true
					); ?>

						<div class="grid">
							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_firstname', 'First Name'); ?></div>
									<?php echo f::form_input_text(
										name       : 'firstname',
										input      : true,
										parameters : 'required',
									); ?>
								</label>
							</div>

							<div class="col-md-6">
								<label class="form-group">
									<div class="form-label"><?php echo t('title_lastname', 'Last Name'); ?></div>
									<?php echo f::form_input_text(
										name       : 'lastname',
										input      : true,
										parameters : 'required',
									); ?>
								</label>
							</div>
						</div>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
							<?php echo f::form_input_email(
								name       : 'email',
								input      : true,
								parameters : 'required',
							); ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_subject', 'Subject'); ?></div>
							<?php echo f::form_input_text(
								name       : 'subject',
								input      : true,
								parameters : 'required',
							); ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_message', 'Message'); ?></div>
							<?php echo f::form_textarea(
								name       : 'message',
								input      : true,
								parameters : 'required style="height: 250px;"',
							); ?>
						</label>

						<label class="form-group">
							<div class="form-label"><?php echo t('title_attachments', 'Attachments'); ?></div>
							<?php echo f::form_input_file(
								name       : 'attachments[]',
								parameters : 'multiple accept=".jpg,.jpeg,.png,.gif,.webp,.avif,.txt,.doc,.docx,.pdf,.mp4"',
							); ?>
						</label>

						<?php if (settings::get('captcha_enabled')) { ?>
						<label class="form-group" style="max-width: 250px;">
							<div class="form-label"><?php echo t('title_captcha', 'CAPTCHA'); ?></div>
							<?php echo f::form_captcha('contact_us'); ?>
						</label>
						<?php } ?>

						<div>
							<?php echo f::form_button(
								name       : 'send',
								value      : t('title_send', 'Send'),
								type       : 'submit',
								parameters : 'style="font-weight: bold;"',
							); ?>
						</div>

					<?php echo f::form_end(); ?>
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
						<?php echo f::draw_fonticon('icon-phone'); ?> <a href="tel:<?php echo settings::get('store_phone'); ?>"><?php echo settings::get('store_phone'); ?></a>
					</div>
					<?php } ?>

					<div class="email">
						<?php echo f::draw_fonticon('icon-envelope'); ?> <a href="mailto:<?php echo settings::get('store_email'); ?>"><?php echo settings::get('store_email'); ?></a>
					</div>

				</div>

			</article>
		</div>
	</div>

</main>

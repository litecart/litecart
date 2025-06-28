<main id="main" class="container">
	{{notices}}

	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">

				<section id="box-reset-password" class="card">

					<div class="card-header">
						<h2 class="card-title"><?php echo t('title_reset_password', 'Reset Password'); ?></h2>
					</div>

					<div class="card-body">
						<?php echo functions::form_begin('reset_password_form', 'post', null, false, 'style="max-width: 480px;"'); ?>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
								<?php echo functions::form_input_email('email', true); ?>
							</label>


							<?php if (isset($_REQUEST['reset_token'])) { ?>
							<label class="form-group">
								<div class="form-label"><?php echo t('title_reset_token', 'Reset Token'); ?></div>
								<?php echo functions::form_input_text('reset_token', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_new_password', 'New Password'); ?></div>
								<?php echo functions::form_input_password('new_password', ''); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_confirmed_password', 'Confirmed Password'); ?></div>
								<?php echo functions::form_input_password('confirmed_password', ''); ?>
							</label>
							<?php } ?>

							<?php if (settings::get('captcha_enabled')) { ?>
							<label class="form-group">
								<div class="form-label"><?php echo t('title_captcha', 'CAPTCHA'); ?></div>
								<?php echo functions::form_captcha('reset_password'); ?>
							</label>
							<?php } ?>

							<?php echo functions::form_button('reset_password', t('title_reset_password', 'Reset Password')); ?>

						<?php echo functions::form_end(); ?>
					</div>
				</section>

			</div>
		</div>
	</div>
</main>

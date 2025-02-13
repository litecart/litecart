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
						<h2 class="card-title"><?php echo language::translate('title_reset_password', 'Reset Password'); ?></h2>
					</div>

					<div class="card-body">
						<?php echo functions::form_begin('reset_password_form', 'post', null, false, 'style="max-width: 480px;"'); ?>

							<div class="form-grid">

								<div class="col-12">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
										<?php echo functions::form_input_email('email', true); ?>
									</label>
								</div>

								<?php if (isset($_REQUEST['reset_token'])) { ?>
								<div class="col-12">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_reset_token', 'Reset Token'); ?></div>
										<?php echo functions::form_input_text('reset_token', true); ?>
									</label>
								</div>

								<div class="col-12">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_new_password', 'New Password'); ?></div>
										<?php echo functions::form_input_password('new_password', ''); ?>
									</label>
								</div>

								<div class="col-12">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_confirmed_password', 'Confirmed Password'); ?></div>
										<?php echo functions::form_input_password('confirmed_password', ''); ?>
									</label>
								</div>
								<?php } ?>

								<?php if (settings::get('captcha_enabled')) { ?>
								<div class="col-12">
									<label class="form-group">
										<div class="form-label"><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></div>
										<?php echo functions::form_captcha('reset_password'); ?>
									</label>
								</div>
								<?php } ?>

								<?php echo functions::form_button('reset_password', language::translate('title_reset_password', 'Reset Password')); ?>
							</div>

						<?php echo functions::form_end(); ?>
					</div>
				</section>

			</div>
		</div>
	</div>
</main>

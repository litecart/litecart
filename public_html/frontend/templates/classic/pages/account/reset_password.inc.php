<div class="container">
	<div class="layout row">

		<div class="col-md-3">
			<div id="sidebar">
					<?php include 'app://frontend/partials/box_customer_service_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<main id="content">
				{snippet:notices}
				{snippet:breadcrumbs}

				<section id="box-reset-password" class="card">

					<div class="card-header">
						<h2 class="card-title"><?php echo language::translate('title_reset_password', 'Reset Password'); ?></h2>
					</div>

					<div class="card-body">
						<?php echo functions::form_begin('reset_password_form', 'post', null, false, 'style="width: 480px;"'); ?>

							<div class="form-group">
								<label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
								<?php echo functions::form_input_email('email', true); ?>
							</div>

							<?php if (isset($_REQUEST['reset_token'])) { ?>
							<div class="form-group">
								<label><?php echo language::translate('title_reset_token', 'Reset Token'); ?></label>
								<?php echo functions::form_input_text('reset_token', true); ?>
							</div>

							<div class="form-group">
								<label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
								<?php echo functions::form_input_password('new_password', '', 'required autocomplete="new-password" data-toggle="password-strength"'); ?>
							</div>

							<div class="form-group">
								<label><?php echo language::translate('title_confirmed_password', 'Confirmed Password'); ?></label>
								<?php echo functions::form_input_password('confirmed_password', '', 'required autocomplete="off"'); ?>
							</div>
							<?php } ?>

							<?php if (settings::get('captcha_enabled')) { ?>
							<div class="form-group">
								<label><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></label>
								<?php echo functions::form_captcha('reset_password'); ?>
							</div>
							<?php } ?>

							<?php echo functions::form_button('reset_password', language::translate('title_reset_password', 'Reset Password')); ?>

						<?php echo functions::form_end(); ?>
					</div>
				</section>
			</main>
		</div>

	</div>
</div>

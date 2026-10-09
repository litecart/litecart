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

				<section id="box-reset-password" class="card" aria-label="<?= f::escape_attr(t('title_reset_password', 'Reset Password')) ?>">

					<div class="card-header">
						<h2 class="card-title"><?= t('title_reset_password', 'Reset Password') ?></h2>
					</div>

					<div class="card-body">
						<?= f::form_begin('reset_password_form', 'post', null, false, ['style' => 'max-width: 480px;', 'aria-label' => f::escape_attr(t('title_reset_password', 'Reset Password'))]) ?>

							<label class="form-group">
								<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
								<?= f::form_input_email('email', true, ['autocomplete' => 'email']) ?>
							</label>


							<?php if (isset($_REQUEST['verification_code'])) { ?>
							<label class="form-group">
								<div class="form-label"><?= t('title_verification_code', 'Verification Code') ?></div>
								<?= f::form_input_text('verification_code', true, ['autocomplete' => 'one-time-code']) ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?= t('title_new_password', 'New Password') ?></div>
								<?= f::form_input_password('new_password', '', ['autocomplete' => 'new-password']) ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?= t('title_confirmed_password', 'Confirmed Password') ?></div>
								<?= f::form_input_password('confirmed_password', '', ['autocomplete' => 'new-password']) ?>
							</label>
							<?php } ?>

							<?php if (settings::get('captcha')) { ?>
							<label class="form-group">
								<div class="form-label"><?= t('title_captcha', 'CAPTCHA') ?></div>
								<?= f::form_captcha('reset_password') ?>
							</label>
							<?php } ?>

							<?= f::form_button('reset_password', t('title_reset_password', 'Reset Password')) ?>

						<?= f::form_end() ?>
					</div>
				</section>

			</div>
		</div>
	</div>
</main>

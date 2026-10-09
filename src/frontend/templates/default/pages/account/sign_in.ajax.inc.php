<section id="box-sign-in" class="card" aria-label="<?= f::escape_attr(t('title_sign_in', 'Sign In')) ?>">
	<div class="card-header">
		<h2 class="card-title"><?= t('title_sign_in', 'Sign In') ?></h2>
	</div>

	<div class="card-body">
		<?= f::form_begin('sign_in_form', 'post', document::ilink('account/sign_in'), false, ['style' => 'width: 320px;', 'aria-label' => f::escape_attr(t('title_sign_in', 'Sign In'))]) ?>
			<?= f::form_input_hidden('redirect_url', true) ?>

			<label class="form-group">
				<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
				<?= f::form_input_email('email', true, ['autocomplete' => 'email', 'placeholder' => t('title_email_address', 'Email Address')]) ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?= t('title_password', 'Password') ?></div>
				<?= f::form_input_password('password', '', ['autocomplete' => 'current-password', 'placeholder' => t('title_password', 'Password')]) ?>
			</label>

			<div class="form-group">
				<?= f::form_checkbox('remember_me', ['1', t('title_remember_me', 'Remember Me')], true) ?>
			</div>

			<div>
				<?= f::form_button('sign_in', t('title_sign_in', 'Sign In'), 'submit', ['class' => 'btn btn-default btn-block']) ?>
			</div>

			<p class="text-center">
				<a href="<?= document::ilink('account/reset_password') ?>">
					<?= t('text_lost_your_password', 'Lost your password?') ?>
				</a>
			</p>

		<?= f::form_end() ?>
	</div>
</section>
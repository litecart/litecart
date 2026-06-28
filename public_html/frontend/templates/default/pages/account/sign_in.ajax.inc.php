<section id="box-sign-in" class="card" aria-label="<?php echo f::escape_attr(t('title_sign_in', 'Sign In')); ?>">
	<div class="card-header">
		<h2 class="card-title"><?php echo t('title_sign_in', 'Sign In'); ?></h2>
	</div>

	<div class="card-body">
		<?php echo f::form_begin('sign_in_form', 'post', document::ilink('account/sign_in'), false, ['style' => 'width: 320px;', 'aria-label' => f::escape_attr(t('title_sign_in', 'Sign In'))]); ?>
			<?php echo f::form_input_hidden('redirect_url', true); ?>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
				<?php echo f::form_input_email('email', true, ['autocomplete' => 'email', 'placeholder' => t('title_email_address', 'Email Address')]); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_password', 'Password'); ?></div>
				<?php echo f::form_input_password('password', '', ['autocomplete' => 'current-password', 'placeholder' => t('title_password', 'Password')]); ?>
			</label>

			<div class="form-group">
				<?php echo f::form_checkbox('remember_me', ['1', t('title_remember_me', 'Remember Me')], true); ?>
			</div>

			<div>
				<?php echo f::form_button('sign_in', t('title_sign_in', 'Sign In'), 'submit', ['class' => 'btn btn-default btn-block']); ?>
			</div>

			<p class="text-center">
				<a href="<?php echo document::ilink('account/reset_password'); ?>">
					<?php echo t('text_lost_your_password', 'Lost your password?'); ?>
				</a>
			</p>

		<?php echo f::form_end(); ?>
	</div>
</section>
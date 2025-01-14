<section id="box-sign-in" class="card">
	<div class="card-header">
		<h2 class="card-title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('sign_in_form', 'post', document::ilink('account/sign_in'), false, 'style="width: 320px;"'); ?>
			<?php echo functions::form_input_hidden('redirect_url', true); ?>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_email_address', 'Email Address'); ?></div>
				<?php echo functions::form_input_email('email', true, 'placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_password', 'Password'); ?></div>
				<?php echo functions::form_input_password('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
			</label>

			<div class="form-group">
				<?php echo functions::form_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
			</div>

			<div>
				<?php echo functions::form_button('sign_in', language::translate('title_sign_in', 'Sign In'), 'submit', 'class="btn btn-default btn-block"'); ?>
			</div>

			<p class="text-center">
				<a href="<?php echo document::ilink('account/reset_password'); ?>">
					<?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?>
				</a>
			</p>

		<?php echo functions::form_end(); ?>
	</div>
</section>
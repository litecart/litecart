<section id="box-account-sign-in" class="card">
	<div class="card-header">
		<h2 class="card-title"><?php echo t('title_sign_in', 'Sign In'); ?></h2>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('sign_in_form', 'post', document::ilink('account/sign_in')); ?>
			<?php echo functions::form_input_hidden('redirect_url', fallback($_GET['redirect_url'], document::ilink(''))); ?>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_email_address', 'Email Address'); ?></div>
				<?php echo functions::form_input_email('email', true, 'required placeholder="'. t('title_email_address', 'Email Address') .'"'); ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?php echo t('title_password', 'Password'); ?></div>
				<?php echo functions::form_input_password('password', '', 'placeholder="'. t('title_password', 'Password') .'"'); ?>
			</label>

			<div class="btn-group btn-block">
				<?php echo functions::form_button('sign_in', t('title_sign_in', 'Sign In')); ?>
			</div>

			<p class="text-center">
				<a href="<?php echo document::href_ilink('account/sign_up'); ?>"><?php echo t('text_new_customers_click_here', 'New customers click here'); ?></a>
			</p>

			<p class="text-center">
				<a href="<?php echo document::href_ilink('account/reset_password'); ?>"><?php echo t('text_lost_your_password', 'Lost your password?'); ?></a>
			</p>

		<?php echo functions::form_end(); ?>
	</div>
</section>
<section id="box-account-sign-in" class="card" aria-label="<?= f::escape_attr(t('title_sign_in', 'Sign In')) ?>">
	<div class="card-header">
		<h2 class="card-title"><?= t('title_sign_in', 'Sign In') ?></h2>
	</div>

	<div class="card-body">
		<?= f::form_begin('sign_in_form', 'post', document::ilink('account/sign_in'), false, ['aria-label' => f::escape_attr(t('title_sign_in', 'Sign In'))]) ?>
			<?= f::form_input_hidden('redirect_url', $_GET['redirect_url'] ?? document::ilink('')) ?>

			<label class="form-group">
				<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
				<?= f::form_input_email('email', true, ['required' => true, 'autocomplete' => 'email', 'placeholder' => t('title_email_address', 'Email Address')]) ?>
			</label>

			<label class="form-group">
				<div class="form-label"><?= t('title_password', 'Password') ?></div>
				<?= f::form_input_password('password', '', ['autocomplete' => 'current-password', 'placeholder' => t('title_password', 'Password')]) ?>
			</label>

			<div class="btn-group btn-block">
				<?= f::form_button('sign_in', t('title_sign_in', 'Sign In')) ?>
			</div>

			<p class="text-center">
				<a href="<?= document::href_ilink('account/sign_up') ?>"><?= t('text_new_customers_click_here', 'New customers click here') ?></a>
			</p>

			<p class="text-center">
				<a href="<?= document::href_ilink('account/reset_password') ?>"><?= t('text_lost_your_password', 'Lost your password?') ?></a>
			</p>

		<?= f::form_end() ?>
	</div>
</section>
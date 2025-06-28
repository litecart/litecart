<style>
html {
	background: #f8f8f8;
}

body {
	display: flex;
	width: 100vw;
	height: 100vh;
	background: url(<?php echo document::href_rlink('app://backend/template/images/background.svg'); ?>);
	background-size: cover;
}
html.dark-mode body {
	background: #1a2133;
}

.loader-wrapper {
	display: none;
	position: absolute !important;
	top: 50%;
	left: 50%;
	margin-top: -64px;
	margin-inline-start: -64px;
}

#box-login {
	width: 400px;
	margin: auto;
	border-radius: var(--border-radius);
}
#box-login .card-header a {
	display: block;
}
#box-login .card-header img {
	margin: 0 auto;
	max-width: 250px;
	max-height: 100px;
}
</style>

<div class="loader-wrapper">
	<div class="loader" style="width: 128px; height: 128px;"></div>
</div>

<div id="box-login">

	<?php echo functions::form_begin('login_form', 'post'); ?>
		<?php echo functions::form_input_hidden('login', 'true'); ?>
		<?php echo functions::form_input_hidden('redirect_url', true); ?>

		<div class="card" style="margin: 0;">
			<div class="card-header text-center">
				<a href="<?php echo document::href_ilink(''); ?>">
					<img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>">
				</a>
			</div>

			<div class="card-body">

				{{notices}}

				<h1><?php echo t('title_sign_in', 'Sign In'); ?></h1>

				<label class="form-group">
					<?php echo functions::form_input_username('username', true, 'placeholder="'. t('title_username_or_email_address', 'Username or Email Address') .'"'); ?>
					<div class="form-label"></div>
				</label>

				<label class="form-group">
					<?php echo functions::form_input_password('password', '', 'placeholder="'. t('title_password', 'Password') .'" autocomplete="current-password"'); ?>
					<div class="form-label"></div>
				</label>

				<div class="form-group">
					<?php echo functions::form_checkbox('remember_me', ['1', t('title_remember_me', 'Remember Me')], true); ?>
				</div>
			</div>

			<div class="card-footer">
				<div class="grid">
					<div class="col-md-6 text-start">
						<a class="btn btn-outline btn-lg" href="<?php echo document::href_ilink('f:'); ?>">
							<?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo t('title_go_to_frontend', 'Go To Frontend'); ?>
						</a>
					</div>
					<div class="col-md-6 text-end">
						<?php echo functions::form_button('login', t('title_login', 'Login'), 'submit', 'class="btn btn-default btn-lg"'); ?>
					</div>
				</div>
			</div>

		</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	if (!$('input[name="username"]').val()) {
		$('input[name="username"]').trigger('focus');
	} else {
		$('input[name="password"]').trigger('focus');
	}

	$('form[name="login_form"]').submit(function(e) {
		e.preventDefault();
		let form = this;
		$('#box-login .card-body').slideUp(100, function() {
			$('#box-login').fadeOut(250, function() {
				$('.loader-wrapper').fadeIn(100, function() {
					form.submit();
				});
			});
		});
	});
</script>
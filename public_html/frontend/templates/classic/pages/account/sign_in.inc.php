<style>
@media only screen and (min-width: 768px) {
	#box-login, #box-login-create {
		padding: 0 3em;
	}
}
</style>

<main id="main" class="container">
	<div id="content">
		{{notices}}
		{{breadcrumbs}}

		<div class="row">
			<section id="box-sign-in" class="card col-sm-6 col-md-4">

				<div class="card-header">
					<h2 class="card-title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>
				</div>

				<div class="card-body">
					<?php echo functions::form_begin('sign_in_form', 'post', document::ilink('account/sign_in'), false, 'style="max-width: 320px;"'); ?>
						<?php echo functions::form_input_hidden('redirect_url', true); ?>

						<div class="form-group">
							<?php echo functions::form_input_email('email', true, 'required autofocus placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
						</div>

						<div class="form-group">
							<?php echo functions::form_input_password('password', '', 'required placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
						</div>

						<div class="form-group">
							<?php echo functions::form_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
						</div>

						<p class="btn-group btn-block">
							<?php echo functions::form_button('sign_in', language::translate('title_sign_in', 'Sign In')); ?>
						</p>

						<p class="text-center">
							<a href="<?php echo document::ilink('account/reset_password', ['email' => fallback($_POST['email'], '')]); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
						</p>

					<?php echo functions::form_end(); ?>
				</div>
			</section>

			<section id="box-login-create" class="card col-sm-6 col-md-8">

				<div class="card-header">
					<h2 class="card-title"><?php echo language::translate('title_create_an_account', 'Create an Account'); ?></h2>
				</div>

				<div class="card-body">
					<ul>
						<li><?php echo language::translate('description_get_access_to_all_order_history', 'Get access to all your order history.'); ?></li>
						<li><?php echo language::translate('description_save_your_cart_items', 'Save your shopping cart for a later visit.'); ?></li>
						<li><?php echo language::translate('description_access_your_cart_simultaneously', 'Access your shopping cart from different computers. Even simultaneously!'); ?></li>
						<li><?php echo language::translate('description_faster_checkout_with_prefilled_details', 'Faster checkout with prefilled customer details.'); ?></li>
						<li><?php echo language::translate('description_receive_new_offers', 'Receive information about new offers and great deals.'); ?></li>
					</ul>

					<div>
						<a class="btn btn-default" href="<?php echo document::href_ilink('account/sign_up'); ?>"><?php echo language::translate('title_register_now', 'Register Now'); ?></a>
					</div>
				</section>
			</div>
		</div>
	</div>
</main>
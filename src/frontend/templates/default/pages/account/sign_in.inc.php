<main id="main" class="container">
	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}

				<div class="grid">
				<section id="box-login" class="card col-md-6" style="margin-bottom: 0;" aria-label="<?= f::escape_attr(t('title_sign_in', 'Sign In')) ?>">

					<div class="card-header">
						<h2 class="card-title"><?= t('title_sign_in', 'Sign In') ?></h2>
					</div>

					<div class="card-body">
						<?= f::form_begin('sign_in_form', 'post', document::ilink('account/sign_in'), false, ['aria-label' => f::escape_attr(t('title_sign_in', 'Sign In'))]) ?>

							<?= f::form_input_hidden('redirect_url', true) ?>

							<label class="form-group">
								<div class="form-label"><?= t('title_email_address', 'Email Address') ?></div>
								<?= f::form_input_email('email', true, ['autocomplete' => 'email', 'placeholder' => t('title_email_address', 'Email Address')]) ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?= t('title_password', 'Password') ?></div>
								<?= f::form_input_password('password', '', ['autocomplete' => 'current-password', 'placeholder' => t('title_password', 'Password')]) ?>
							</label>

							<label class="form-group">
								<?= f::form_checkbox('remember_me', ['1', t('title_remember_me', 'Remember Me')], true) ?>
							</label>

							<div class="form-group">
								<?= f::form_button('sign_in', t('title_sign_in', 'Sign In'), 'submit', ['class' => 'btn btn-default btn-block']) ?>
							</div>

							<div class="text-center">
								<a href="<?= document::ilink('account/reset_password', ['email' => $_POST['email'] ?? '']) ?>">
									<?= t('text_lost_your_password', 'Lost your password?') ?>
								</a>
							</div>

						<?= f::form_end() ?>
					</div>
				</section>

				<section id="box-login-create" class="card col-md-6" style="margin-bottom: 0;" aria-label="<?= f::escape_attr(t('title_sign_up', 'Sign Up')) ?>">
					<div class="card-header">
						<h2 class="card-title"><?= t('title_sign_up', 'Sign Up') ?></h2>
					</div>

					<div class="card-body">
						<ul>
							<li><?= t('description_get_access_to_all_order_history', 'Get access to all your order history.') ?></li>
							<li><?= t('description_save_your_cart_items', 'Save your shopping cart for a later visit.') ?></li>
							<li><?= t('description_access_your_cart_simultaneously', 'Access your shopping cart from different computers. Even simultaneously!') ?></li>
							<li><?= t('description_faster_checkout_with_prefilled_details', 'Faster checkout with prefilled customer details.') ?></li>
							<li><?= t('description_receive_new_offers', 'Receive information about new offers and great deals.') ?></li>
						</ul>

						<div>
							<a class="btn btn-default" href="<?= document::href_ilink('account/sign_up') ?>"><?= t('title_sign_up', 'Sign Up') ?></a>
						</div>
					</div>
				</section>
			</div>
		</div>
	</div>
</main>

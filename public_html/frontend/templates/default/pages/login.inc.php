<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}

        <div class="row">
        <section id="box-login" class="card col-md-6" style="margin-bottom: 0;">

          <div class="card-header">
            <h2 class="card-title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>
          </div>

          <div class="card-body">
            <?php echo functions::form_begin('login_form', 'post', document::ilink('login')); ?>
              <?php echo functions::form_hidden_field('redirect_url', true); ?>

              <div class="form-group">
                <?php echo functions::form_email_field('email', true, 'placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
              </div>

              <div class="form-group">
                <?php echo functions::form_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
              </div>

              <div class="form-group">
                <?php echo functions::form_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
              </div>

              <div>
                <?php echo functions::form_button('login', language::translate('title_sign_in', 'Sign In'), 'submit', 'class="btn btn-default btn-block"'); ?>
              </div>

              <p class="text-center">
                <a href="<?php echo document::ilink('reset_password', ['email' => fallback($_POST['email'])]); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
              </p>

            <?php echo functions::form_end(); ?>
          </div>
        </section>

        <section id="box-login-create" class="card col-md-6" style="margin-bottom: 0;">
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
              <a class="btn btn-default" href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('title_register_now', 'Register Now'); ?></a>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</main>

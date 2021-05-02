<main id="main" class="container">
  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <div class="row layout">
      <div class="col-md-6">
        <section id="box-login" class="box white" style="">

          <h2 class="title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>

          <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login'), false, 'style="max-width: 320px;"'); ?>
            <?php echo functions::form_draw_hidden_field('redirect_url', true); ?>

            <div class="form-group">
              <?php echo functions::form_draw_email_field('email', true, 'placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
            </div>

            <div class="form-group">
              <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
            </div>

            <div class="form-group">
              <?php echo functions::form_draw_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
            </div>

            <div class="btn-group btn-block">
              <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
            </div>

            <p class="text-center">
              <a href="<?php echo document::ilink('reset_password', ['email' => !empty($_POST['email']) ? $_POST['email'] : '']); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
            </p>

          <?php echo functions::form_draw_form_end(); ?>
        </section>
      </div>

      <div class="col-md-6">

        <section id="box-login-create" class="box white">

          <h2 class="title"><?php echo language::translate('title_create_an_account', 'Create an Account'); ?></h2>

          <ul>
            <li><?php echo language::translate('description_get_access_to_all_order_history', 'Get access to all your order history.'); ?></li>
            <li><?php echo language::translate('description_save_your_cart_items', 'Save your shopping cart for a later visit.'); ?></li>
            <li><?php echo language::translate('description_access_your_cart_simultaneously', 'Access your shopping cart from different computers. Even simultaneously!'); ?></li>
            <li><?php echo language::translate('description_faster_checkout_with_prefilled_details', 'Faster checkout with prefilled customer details.'); ?></li>
            <li><?php echo language::translate('description_receive_new_offers', 'Receive information about new offers and great deals.'); ?></li>
          </ul>

          <p><a class="btn btn-default" href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('title_register_now', 'Register Now'); ?></a><p>
        </section>
      </div>
    </div>
  </div>
</main>

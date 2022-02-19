<section id="box-account-login" class="card">
  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login')); ?>
      <?php echo functions::form_draw_hidden_field('redirect_url', fallback($_GET['redirect_url'], document::ilink(''))); ?>

      <div class="form-group">
        <?php echo functions::form_draw_email_field('email', true, 'required placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
      </div>

      <div class="form-group">
        <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
      </div>

      <div class="btn-group btn-block">
        <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
      </div>

      <p class="text-center">
        <a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a>
      </p>

      <p class="text-center">
        <a href="<?php echo document::href_ilink('reset_password'); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
      </p>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</section>
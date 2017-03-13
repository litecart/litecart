<div id="box-account-login" class="box">
  <h2><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>

  <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login')); ?>
    <?php echo functions::form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::ilink('')); ?>

    <div class="form-group">
      <?php echo functions::form_draw_email_field('email', true, 'required="required" placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
    </div>

    <div class="form-group">
      <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
    </div>

    <div class="btn-group btn-block">
      <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
    </div>

    <p class="text-center"><a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a></p>

  <?php echo functions::form_draw_form_end(); ?>
</div>
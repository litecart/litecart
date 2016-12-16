<main id="content">
  <!--snippet:notices-->

  <div class="box-login" class="box">

    <h1><?php echo language::translate('title_sign_in', 'Sign In'); ?></h1>

    <?php echo functions::form_draw_form_begin('login_form', 'post', null, false, 'style="max-width: 320px;"'); ?>
      <?php echo functions::form_draw_hidden_field('redirect_url', true); ?>

      <div class="form-group">
        <?php echo functions::form_draw_email_field('email', true, 'placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
      </div>

      <div class="form-group">
        <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
      </div>

      <div class="checkbox">
        <label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label>
      </div>

      <p class="btn-grup">
        <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
      </p>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</main>
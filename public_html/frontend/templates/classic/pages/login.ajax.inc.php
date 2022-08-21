<section id="box-login">

  <h2 class="title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>

  <?php echo functions::form_begin('login_form', 'post', document::ilink('login'), false, 'style="width: 320px;"'); ?>
    <?php echo functions::form_hidden_field('redirect_url', true); ?>

    <div class="form-group">
      <?php echo functions::form_email_field('email', true, 'required autofocus placeholder="'. language::translate('title_email_address', 'Email Address') .'" autocomplete="email"'); ?>
    </div>

    <div class="form-group">
      <?php echo functions::form_password_field('password', '', 'required placeholder="'. language::translate('title_password', 'Password') .'" autocomplete="current-password"'); ?>
    </div>

    <div class="form-group">
      <?php echo functions::form_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
    </div>

    <p class="btn-group btn-block">
      <?php echo functions::form_button('login', language::translate('title_sign_in', 'Sign In')); ?>
    </p>

    <p class="text-center">
      <a href="<?php echo document::ilink('reset_password'); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
    </p>

  <?php echo functions::form_end(); ?>
</section>
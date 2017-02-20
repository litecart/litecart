<div class="box-login" class="box">

  <h2 class="title"><?php echo language::translate('title_sign_in', 'Sign In'); ?></h2>

  <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login'), false, 'style="width: 320px;"'); ?>
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

    <p class="btn-group btn-block">
      <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
    </p>

    <p class="text-center">
      <a href="<?php echo document::ilink('reset_password'); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
    </p>

  <?php echo functions::form_draw_form_end(); ?>
</div>
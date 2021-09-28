<style>
html, body {
}

html {
  display: table;
  width: 100%;
  height: 100%;
  background: #f8f8f8;
}

body {
  display: table-row;
  background: url({snippet:template_path}images/background.svg);
  background-size: cover;
}

.loader-wrapper {
  display: none;
  position: absolute !important;
  top: 50%;
  left: 50%;
  margin-top: -64px;
  margin-inline-start: -64px;
}

#box-login-wrapper {
  position: relative;
  display: table-cell;
  height: 100%;
  vertical-align: middle;
  padding: 30px;
}

#box-login {
  max-width: 360px;
  margin: auto;

  padding: 0px;

  background: #fff;

  text-align: center;

  border-radius: 0px 25px 0px 25px;

  box-shadow: 0px 0px 60px rgba(0,0,0,0.25);
}

#box-login .header {
  padding: 10px;
}
#box-login .header img {
  margin: 1em;
  max-width: 250px;
  max-height: 100px;
}

#box-login .content {
  padding: 0 30px;
  margin: 0 auto;
  border-bottom: 1px solid rgba(0,0,0,0.1);
}

#box-login .footer {
  border-top: 1px solid rgba(255,255,255,0.8);
  background: #f6f6f6;
  padding: 10px;
  text-align: end;
  border-radius: 0px 0px 0px 25px;
}
</style>

<div class="loader-wrapper">
  <div class="loader" style="width: 128px; height: 128px;"></div>
</div>

<div id="box-login-wrapper">

  <div id="box-login" class="">

    <div class="header">
      <a href="<?php echo document::href_ilink(''); ?>"><img src="<?php echo document::href_link(WS_DIR_TEMPLATE . 'images/logotype.svg'); ?>" alt="<?php echo settings::get('store_name'); ?>" /></a>
    </div>

    <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>

      <div class="content">
        <?php echo functions::form_draw_hidden_field('redirect_url', true); ?>

        {snippet:notices}

        <div class="form-group">
          <?php echo functions::form_draw_username_field('username', true, 'placeholder="'. language::translate('title_username', 'Username') .'"'); ?>
        </div>

        <div class="form-group">
          <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'" autocomplete="current-password"'); ?>
        </div>

        <div class="form-group checkbox">
          <label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label>
        </div>
      </div>

      <div class="footer">
        <?php echo functions::form_draw_hidden_field('login', 'true'); ?>
        <?php echo functions::form_draw_button('login', language::translate('title_login', 'Login')); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

</div>

<script>
  if ($('input[name="username"]').val() == '') {
    $('input[name="username"]').focus();
  } else {
    $('input[name="password"]').focus();
  }

  $('form[name="login_form"]').submit(function(e) {
    e.preventDefault();
    var form = this;
    $('#box-login-wrapper .content').slideUp(250, function(){
      $('#box-login-wrapper').fadeOut(500, function(){
        $('.loader-wrapper').fadeIn(250, function(){
          form.submit();
        });
      });
    });
 });
</script>
<style>
html {
  background: #f8f8f8;
}

body {
  display: flex;
  width: 100vw;
  height: 100vh;
  background: url({{template_path}}images/background.svg);
  background-size: cover;
}
html.dark-mode body {
  background: #1a2133;
}

.loader-wrapper {
  display: none;
  position: absolute !important;
  top: 50%;
  left: 50%;
  margin-top: -64px;
  margin-inline-start: -64px;
}

#box-login {
  width: 360px;
  margin: auto;
  border-radius: 0px 25px 0px 25px;
  box-shadow: 0px 0px 60px rgba(0,0,0,0.25);
}
#box-login .card-header a {
  display: block;
}
#box-login .card-header img {
  margin: 0 auto;
  max-width: 250px;
  max-height: 100px;
}
</style>

<div class="loader-wrapper">
  <div class="loader" style="width: 128px; height: 128px;"></div>
</div>


<div id="box-login" class="card">
  <div class="card-header text-center">
    <a href="<?php echo document::href_ilink(''); ?>"><img src="<?php echo document::href_link(WS_DIR_TEMPLATE . 'images/logotype.svg'); ?>" alt="<?php echo settings::get('store_name'); ?>" /></a>
  </div>

  <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>
    <?php echo functions::form_draw_hidden_field('login', 'true'); ?>
    <?php echo functions::form_draw_hidden_field('redirect_url', true); ?>

    <div class="card-body">

      {{notices}}

      <h1><?php echo language::translate('title_sign_in', 'Sign In'); ?></h1>

      <div class="form-group">
        <?php echo functions::form_draw_username_field('username', true, 'placeholder="'. language::translate('title_username_or_email_address', 'Username or Email Address') .'"'); ?>
      </div>

      <div class="form-group">
        <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'" autocomplete="current-password"'); ?>
      </div>

      <div class="form-group">
        <?php echo functions::form_draw_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="col-md-6 text-start">
          <a class="btn btn-hover-outline btn-lg" href="<?php echo document::href_ilink('f:'); ?>">
            <?php echo functions::draw_fonticon('fa-chevron-left'); ?> <?php echo language::translate('title_go_to_frontend', 'Go To Frontend'); ?>
          </a>
        </div>
        <div class="col-md-6 text-end">
          <?php echo functions::form_draw_button('login', language::translate('title_login', 'Login'), 'submit', 'class="btn btn-default btn-lg"'); ?>
        </div>
      </div>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
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
    $('#box-login .card-body').slideUp(100, function(){
      $('#box-login').fadeOut(250, function(){
        $('.loader-wrapper').fadeIn(100, function(){
          form.submit();
        });
      });
    });
 });
</script>
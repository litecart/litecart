<style>
html {
  background: #f8f8f8;
}

body {
  display: flex;
  width: 100vw;
  height: 100vh;
  background: url({snippet:template_path}images/background.svg);
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

@keyframes bounce-in {
  from, 20%, 40%, 60%, 80%, to {
    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
  }

  0%  { transform: scale3d(0.3, 0.3, 0.3); opacity: 0; }
  20% { transform: scale3d(1.1, 1.1, 1.1); }
  40% { transform: scale3d(0.9, 0.9, 0.9); }
  60% { transform: scale3d(1.03, 1.03, 1.03); opacity: 1; }
  80% { transform: scale3d(0.97, 0.97, 0.97); }
  to  { transform: scale3d(1, 1, 1); opacity: 1; }
}


@keyframes flip-out {
  from { transform: perspective(400px); }
  30%  { transform: perspective(400px) rotate3d(1, 0, 0, -20deg); opacity: 1; }
  to   { transform: perspective(400px) rotate3d(1, 0, 0, 90deg); opacity: 0; }
}

.bounce-in {
  animation: bounce-in 250ms;
}
.flip-out {
  animation: flip-out 500ms forwards !important;
  backface-visibility: visible !important;
}
</style>

<div class="loader-wrapper">
  <div class="loader" style="width: 128px; height: 128px;"></div>
</div>


<div id="box-login" class="card bounce-in">
  <div class="card-header text-center">
    <a href="<?php echo document::href_ilink(''); ?>"><img src="<?php echo document::href_link(WS_DIR_TEMPLATE . 'images/logotype.svg'); ?>" alt="<?php echo settings::get('store_name'); ?>" /></a>
  </div>

  <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>
    <?php echo functions::form_draw_hidden_field('login', 'true'); ?>
    <?php echo functions::form_draw_hidden_field('redirect_url', true); ?>

    <div class="card-body">

      {snippet:notices}

      <h1><?php echo language::translate('title_sign_in', 'Sign In'); ?></h1>

      <div class="form-group">
        <?php echo functions::form_draw_username_field('username', true, 'placeholder="'. language::translate('title_username', 'Username') .'"'); ?>
      </div>

      <div class="form-group">
        <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'" autocomplete="current-password"'); ?>
      </div>

      <div class="form-group checkbox text-center">
        <label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="col-md-6 text-start">
          <a class="btn btn-hover-outline btn-lg" href="<?php echo document::href_ilink(''); ?>">
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

  $('form[name="login_form"]').submit(function() {
    let form = this;
    $('#box-login').removeClass('bounce-in');
    $('#box-login .card-body').slideUp(100, function(){
      $('#box-login').addClass('flip-out');
    });
  });
</script>
<style>
html, body, body > table {
  width: 100%;
  height: 100%;
  background: #f8f8f8;
}
#box-login-wrapper {
  max-width: 400px;
  margin: auto;
  text-align: center;
  z-index: 99;
  position: relative;
}
#box-login {
  margin: auto;
  text-align: center;
  padding: 0px;
  box-shadow: 0px 0px 60px rgba(0,0,0,0.25)
}
#box-login .header {
  padding: 10px;
}
#box-login .content {
  margin: auto;
  border-bottom: 1px solid rgba(0,0,0,0.1);

}
#box-login form table{
  margin: auto;
}
#box-login .footer {
  border-top: 1px solid rgba(255,255,255,0.8);
  background: #f6f6f6;
  padding: 10px;
  text-align: right;
}

#loader img {
  position: absolute;
  top: 50%;
  left: 50%;
  margin-left: -44px;
  margin-top: -44px;
  z-index: 0;

  -webkit-animation-name: spin;
  -webkit-animation-duration: 4000ms;
  -webkit-animation-iteration-count: infinite;
  -webkit-animation-timing-function: linear;
  -moz-animation-name: spin;
  -moz-animation-duration: 4000ms;
  -moz-animation-iteration-count: infinite;
  -moz-animation-timing-function: linear;
  -ms-animation-name: spin;
  -ms-animation-duration: 4000ms;
  -ms-animation-iteration-count: infinite;
  -ms-animation-timing-function: linear;

  animation-name: spin;
  animation-duration: 4000ms;
  animation-iteration-count: infinite;
  animation-timing-function: linear;
}
@-ms-keyframes spin {
  from { -ms-transform: rotate(0deg); }
  to { -ms-transform: rotate(360deg); }
}
@-moz-keyframes spin {
  from { -moz-transform: rotate(0deg); }
  to { -moz-transform: rotate(360deg); }
}
@-webkit-keyframes spin {
  from { -webkit-transform: rotate(0deg); }
  to { -webkit-transform: rotate(360deg); }
}
@keyframes spin {
  from { transform:rotate(0deg); }
  to { transform:rotate(360deg); }
}
</style>

<div id="loader">
  <img src="{snippet:template_path}images/loader.png" alt="" />
</div>

<table>
  <tr>
    <td class="content">

      <div id="box-login-wrapper">
        <!--snippet:notices-->

          <div id="box-login" class="box rounded-corners">

            <div class="header">
              <a href="<?php echo document::href_ilink(''); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" style="max-width: 250px; max-height: 100px;" alt="<?php echo settings::get('store_name'); ?>" /></a>
            </div>

            <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>
            <?php echo functions::form_draw_hidden_field('redirect_url', $action); ?>
              <div class="content">
                <table>
                  <tr>
                    <td style="text-align: right;"><?php echo language::translate('title_username', 'Username'); ?></td>
                    <td><span class="input-wrapper"><?php echo functions::draw_fonticon('fa-user'); ?> <?php echo functions::form_draw_text_field('username', true); ?></span></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><?php echo language::translate('title_password', 'Password'); ?></td>
                    <td><span class="input-wrapper"><?php echo functions::draw_fonticon('fa-key'); ?> <?php echo functions::form_draw_password_field('password', ''); ?></span></td>
                  </tr>
                  <tr>
                    <td></td>
                    <td style="text-align: left;"><label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label></td>
                  </tr>
                </table>
              </div>

              <div class="footer">
                <?php echo functions::form_draw_button('login', language::translate('title_login', 'Login')); ?>
              </div>
            <?php echo functions::form_draw_form_end(); ?>
          </div>

      </div>
    </td>
  </tr>
</table>

<script>
  if ($("input[name='username']").val() == '') {
    $("input[name='username']").focus();
  } else {
    $("input[name='password']").focus();
  }

  $("form[name='login_form']").submit(function(e) {
    $("#box-login-wrapper").fadeOut(100);
  });
</script>
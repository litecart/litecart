<?php
  require_once('../includes/app_header.inc.php');
  
  document::$template = settings::get('store_template_admin');
  document::$layout = 'login';
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (!empty(user::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));
  
  if (!empty($_POST['login'])) {
    
    if (basename(parse_url($_POST['redirect_url'], PHP_URL_PATH)) == basename(__FILE__)) $_POST['redirect_url'] = null;
    
    user::login($_POST['username'], $_POST['password'], $_POST['redirect_url'], isset($_POST['remember_me']) ? $_POST['remember_me'] : false);
  }
  
  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
  
  document::$snippets['javascript'][] = '  $(document).ready(function() {' . PHP_EOL
                                      . '    if ($("input[name=\'username\']").val() == \'\') {' . PHP_EOL
                                      . '      $("input[name=\'username\']").focus();' . PHP_EOL
                                      . '    } else {' . PHP_EOL
                                      . '      $("input[name=\'password\']").focus();' . PHP_EOL
                                      . '    }' . PHP_EOL
                                      . '  });';
?>
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
}
#box-login {
  margin: auto;
  text-align: center;
  padding: 0px;
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
</style>
<table>
  <tr>
    <td class="content">

      <div id="box-login-wrapper">
        <!--snippet:notices-->
      
          <div id="box-login" class="box shadow rounded-corners">
          
            <div class="header">
              <a href="<?php echo document::href_link(WS_DIR_HTTP_HOME . 'index.php'); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>logotype.png" height="50" alt="<?php echo settings::get('store_name'); ?>" /></a>
            </div>
            
            <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>
            <?php echo functions::form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::link(WS_DIR_ADMIN)); ?>
              <div class="content">
                <table>
                  <tr>
                    <td style="text-align: right;"><?php echo language::translate('title_username', 'Username'); ?></td>
                    <td><?php echo functions::form_draw_text_field('username', true); ?></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><?php echo language::translate('title_password', 'Password'); ?></td>
                    <td><?php echo functions::form_draw_password_field('password', ''); ?></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"></td>
                    <td><label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label></td>
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
  $("form[name='login_form']").submit(function() {
    $("form[name='login_form']").slideUp('fast');
  });
</script>



<?php
  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
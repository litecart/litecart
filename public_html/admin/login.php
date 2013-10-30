<?php
  require_once('../includes/app_header.inc.php');
  
  document::$template = settings::get('store_template_admin');
  document::$layout = 'login';
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (!empty(customer::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));
  
  if (!empty($_POST['login'])) user::login($_POST['username'], $_POST['password']);
  
  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
?>


  <div id="box-login">
    <div class="content">
      <?php echo functions::form_draw_form_begin('login_form', 'post'); ?>
      <?php echo functions::form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::link(WS_DIR_HTTP_HOME)); ?>
        <table>
          <tr>
            <td align="left"><?php echo language::translate('title_username', 'Username'); ?><br />
              <?php echo functions::form_draw_text_field('username', true); ?></td>
          </tr>
          <tr>
            <td align="left"><?php echo language::translate('title_password', 'Password'); ?><br />
            <?php echo functions::form_draw_password_field('password', ''); ?></td>
          </tr>
          <tr>
            <td align="left"><?php echo functions::form_draw_button('login', language::translate('title_login', 'Login')); ?></td>
          </tr>
        </table>
      <?php echo functions::form_draw_form_end(); ?>
    </div>
  </div>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
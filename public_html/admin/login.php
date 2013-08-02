<?php
  require_once('../includes/app_header.inc.php');
  
  $system->document->template = $system->settings->get('store_template_admin');
  $system->document->layout = 'login';
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (!empty($system->customer->data['id'])) $system->notices->add('notice', $system->language->translate('text_already_logged_in', 'You are already logged in'));
  
  if (!empty($_POST['login'])) $system->user->login($_POST['username'], $_POST['password']);
  
  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
?>

<div id="box-login" class="box shadow">
  <div><h1 class="heading"><?php echo $system->settings->get('store_name'); ?></h1></div>
  <div class="content">
    <?php echo $system->functions->form_draw_form_begin('login_form', 'post'); ?>
    <?php echo $system->functions->form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : $system->document->link(WS_DIR_HTTP_HOME)); ?>
      <table>
        <tr>
          <td align="left"><?php echo $system->language->translate('title_username', 'Username'); ?><br />
            <?php echo $system->functions->form_draw_text_field('username', true); ?></td>
        </tr>
        <tr>
          <td align="left"><?php echo $system->language->translate('title_password', 'Password'); ?><br />
          <?php echo $system->functions->form_draw_password_field('password', ''); ?></td>
        </tr>
        <tr>
          <td align="left"><?php echo $system->functions->form_draw_button('login', $system->language->translate('title_login', 'Login')); ?></td>
        </tr>
      </table>
    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
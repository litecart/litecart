<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (!empty($system->customer->data['id'])) $system->notices->add('notice', $system->language->translate('text_already_logged_in', 'You are already logged in'));
  
  $system->document->snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. htmlspecialchars($system->document->link('')) .'" />';

?>
 
<div class="box">
  <div class="heading"><h1><?php echo $system->language->translate('title_login', 'Login'); ?></h1></div>
  <div class="content">
    <?php echo $system->functions->form_draw_form_begin('login_form', 'post'); ?>
    <?php echo $system->functions->form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : $system->document->link(WS_DIR_HTTP_HOME)); ?>
      <table width="100%">
        <tr>
          <td><?php echo $system->language->translate('title_email_address', 'E-mail Address'); ?><br />
            <?php echo $system->functions->form_draw_input('email', true, 'text', 'style="width: 175px;"'); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_password', 'Password'); ?><br />
          <?php echo $system->functions->form_draw_input('password', '', 'password', 'style="width: 175px;"'); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->functions->form_draw_button('login', $system->language->translate('title_login', 'Login')); ?> <?php echo $system->functions->form_draw_button('lost_password', $system->language->translate('title_lost_password', 'Lost Password')); ?> </td>
        </tr>
        <tr>
          <td><a href="<?php echo $system->document->href_link(WS_DIR_HTTP_HOME . 'create_account.php'); ?>"><?php echo $system->language->translate('text_new_customers_click_here', 'New customers click here'); ?></td>
        </tr>
    </table>
    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>

<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>
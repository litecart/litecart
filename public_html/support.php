<?php
  require_once('includes/app_header.inc.php');
  
  $system->document->snippets['title'][] = $system->language->translate('support.php:head_title', 'Customer Service');
  $system->document->snippets['keywords'] = $system->language->translate('support.php:meta_keywords', '');
  $system->document->snippets['description'] = $system->language->translate('support.php:meta_description', '');
  
  $system->breadcrumbs->add($system->language->translate('title_customer_service', 'Customer Service'), basename(__FILE__));

  if (!empty($_POST['send'])) {
  
    if ($system->settings->get('contact_form_captcha_enabled') == 'true') {
      $captcha = $system->functions->captcha_get('contact_us');
      if (empty($captcha) || $captcha != $_POST['captcha']) $system->notices->add('errors', $system->language->translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
    }
    if (empty($_POST['name'])) $system->notices->add('errors', $system->language->translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['email'])) $system->notices->add('errors', $system->language->translate('error_must_enter_email', 'You must enter a valid e-mail address'));
    
    if (empty($system->notices->data['errors'])) {
      
      if ($system->functions->email_send($_POST['name'] .' <'. $_POST['email'] .'>', $system->settings->get('store_email'), $_POST['subject'], $_POST['message'])) {
        $system->notices->add('success', $system->language->translate('success_your_email_was_sent', 'Your e-mail has successfully been sent'));
      } else {
        $system->notices->add('errors', $system->language->translate('error_sending_email_for_unknown_reason', 'The e-mail could not be sent for an unknown reason'));
      }
      
      header('Location: '. $system->document->link());
      exit;
    }
  }
  
?>

<div style="float: left; display: inline-block; width: 200px;">
  <div class="box">
    <div class="heading"><h1><?php echo $system->language->translate('title_information', 'Information'); ?></h1></div>
    <div class="content">
      <div id="support-links-wrapper">
        <ul>
        <?php
          $pages_query = $system->database->query(
            "select p.id, pi.title from ". DB_TABLE_PAGES ." p
            left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". $system->language->selected['code'] ."')
            where dock_support
            order by p.priority, pi.title;"
          );
          while ($page = $system->database->fetch($pages_query)) {
            echo '    <li><a href="'. $system->document->href_link(WS_DIR_HTTP_HOME . 'page.php', array('page_id' => $page['id'])) .'">'. $page['title'] .'</a></li>' . PHP_EOL;
          }
        ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<div style="float: left; display: inline-block; margin-left: 20px;">
  <h1><?php echo $system->language->translate('title_contact_us', 'Contact Us'); ?></h1>
  <?php echo $system->functions->form_draw_form_begin('contact_form', 'post'); ?>
  <table border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td><?php echo $system->language->translate('title_name', 'Name'); ?><br />
        <?php echo $system->functions->form_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'text', 'style="width: 175px;"'); ?></td>
      <td style="padding-left: 0px;"><?php echo $system->language->translate('title_email_address', 'E-mail Address'); ?><br />
        <?php echo $system->functions->form_draw_input_field('email', isset($_POST['email']) ? $_POST['email'] : '', 'text', 'style="width: 175px;"'); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo $system->language->translate('title_subject', 'Subject'); ?><br />
        <?php echo $system->functions->form_draw_input_field('subject', isset($_POST['subject']) ? $_POST['subject'] : '', 'text', 'style="width: 360px;"'); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo $system->language->translate('title_message', 'Message'); ?><br />
        <?php echo $system->functions->form_draw_textarea('message', isset($_POST['message']) ? $_POST['message'] : '', 'style="width: 360px; height: 250px;"'); ?></td>
    </tr>
    <?php if ($system->settings->get('contact_form_captcha_enabled') == 'true') { ?>
    <tr>
      <td colspan="2"><?php echo $system->language->translate('title_captcha', 'CAPTCHA'); ?><br />
        <?php echo $system->functions->captcha_generate(100, 40, 4, 'contact_us', 'numbers', 'align="absbottom"') .' '. $system->functions->form_draw_input_field('captcha', '', 'input', 'style="width: 90px; height: 30px; font-size: 24px; text-align: center;"'); ?>
      </td>
    </tr>
    <?php } ?>
    <tr>
      <td><?php echo $system->functions->form_draw_button('send', $system->language->translate('title_send', 'Send'), 'submit', 'style="font-weight: bold;"'); ?></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <?php echo $system->functions->form_draw_form_end(); ?>
</div>

<div style="clear: both;"></div>

<?php
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
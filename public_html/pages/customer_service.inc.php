<?php
  
  document::$snippets['title'][] = language::translate('support.php:head_title', 'Customer Service');
  document::$snippets['keywords'] = language::translate('support.php:meta_keywords', '');
  document::$snippets['description'] = language::translate('support.php:meta_description', '');
  
  breadcrumbs::add(language::translate('title_customer_service', 'Customer Service'), basename(__FILE__));
  
  if (!empty($_POST['send'])) {
  
    if (settings::get('contact_form_captcha_enabled')) {
      $captcha = functions::captcha_get('contact_us');
      if (empty($captcha) || $captcha != $_POST['captcha']) notices::add('errors', language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
    }
    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['email'])) notices::add('errors', language::translate('error_must_enter_email', 'You must enter a valid e-mail address'));
    
    if (empty(notices::$data['errors'])) {
      
      if (functions::email_send($_POST['name'] .' <'. $_POST['email'] .'>', settings::get('store_email'), $_POST['subject'], $_POST['message'])) {
        notices::add('success', language::translate('success_your_email_was_sent', 'Your e-mail has successfully been sent'));
      } else {
        notices::add('errors', language::translate('error_sending_email_for_unknown_reason', 'The e-mail could not be sent for an unknown reason'));
      }
      
      header('Location: '. document::link());
      exit;
    }
  }
  
  ob_start();
?>
<aside class="shadow rounded-corners">
  <div class="box" id="box-information">
    <div class="heading"><h3><?php echo language::translate('title_customer_service', 'Customer Service'); ?></h3></div>
    <div class="content">
      <nav>
        <ul class="list-vertical">
          <li<?php echo (empty($_GET['page_id'])) ? ' class="active"' : ''; ?>><a href="<?php echo document::href_link('', array()); ?>"><?php echo language::translate('title_contact_us', 'Contact Us'); ?></a></li>
          <?php
            $pages_query = database::query(
              "select p.id, pi.title from ". DB_TABLE_PAGES ." p
              left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
              where status
              and find_in_set('customer_service', dock)
              order by p.priority, pi.title;"
            );
            while ($page = database::fetch($pages_query)) {
              echo '<li'. ((isset($_GET['page_id']) && $_GET['page_id'] == $page['id']) ? ' class="active"' : '') .'><a href="'. document::href_link('', array('page_id' => $page['id'])) .'">'. $page['title'] .'</a></li>' . PHP_EOL;
            }
          ?>
        </ul>
      </nav>
    </div>
  </div>
</aside>
<?php
  document::$snippets['column_left'] = ob_get_clean();

  if (empty($_GET['page_id'])) {
?>

  <?php if (settings::get('store_visiting_address')) { ?>
  <div class="box" id="box-map">
    <div class="content">
      <div id="map" style="height: 400px;" class="shadow">
        <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo document::href_link('https://www.google.com/maps', array('q' => settings::get('store_postal_address'), 'output' => 'svembed')); ?>"></iframe>
      </div>
    </div>
  </div>
  <?php } ?>
  
  <div class="box" id="box-customer-service">
    <div class="heading"><h1><?php echo language::translate('title_contact_us', 'Contact Us'); ?></h1></div>
    <div class="content">
      <?php echo functions::form_draw_form_begin('contact_form', 'post'); ?>
      
      <table>
        <tr>
          <td><?php echo language::translate('title_name', 'Name'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('name', true); ?></td>
          <td><?php echo language::translate('title_email_address', 'E-mail Address'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_email_field('email', true, ''); ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo language::translate('title_subject', 'Subject'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_text_field('subject', true, 'data-size="large"'); ?></td>
        </tr>
        <tr>
          <td colspan="2"><?php echo language::translate('title_message', 'Message'); ?> <span class="required">*</span><br />
            <?php echo functions::form_draw_textarea('message', true, 'data-size="large" style="height: 250px;"'); ?></td>
        </tr>
        <?php if (settings::get('contact_form_captcha_enabled')) { ?>
        <tr>
          <td colspan="2"><?php echo language::translate('title_captcha', 'CAPTCHA'); ?> <span class="required">*</span><br />
            <?php echo functions::captcha_generate(100, 40, 4, 'contact_us', 'numbers', 'align="absbottom"') .' '. functions::form_draw_text_field('captcha', '', 'style="width: 90px; height: 30px; font-size: 24px; text-align: center;"'); ?>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <td><?php echo functions::form_draw_button('send', language::translate('title_send', 'Send'), 'submit', 'style="font-weight: bold;"'); ?></td>
          <td>&nbsp;</td>
        </tr>
      </table>
      <?php echo functions::form_draw_form_end(); ?>
    </div>
  </div>
  
<?php
  } else {
    $pages_query = database::query(
      "select p.id, p.status, pi.title, pi.content, pi.head_title, pi.meta_keywords, pi.meta_description from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where p.id = '". (int)$_GET['page_id'] ."'
      limit 1;"
    );
    $page = database::fetch($pages_query);
    
    if (empty($page['status'])) {
      notices::add('errors', language::translate('error_page_not_found', 'The requested page could not be found'));
      header('HTTP/1.1 404 Not Found');
      header('Location: '. document::link(WS_DIR_HTTP_HOME));
      exit;
    }
    
    document::$snippets['title'][] = !empty($page['head_title']) ? $page['head_title'] : $page['title'];
    document::$snippets['keywords'] = !empty($page['meta_keywords']) ? $page['meta_keywords'] : '';
    document::$snippets['description'] = !empty($page['meta_description']) ? $page['meta_description'] : '';
    
    breadcrumbs::add($page['title'], document::link('', array(), true));
?>
  
  <div class="box" id="box-customer-service">
    <div class="content">
      <?php echo $page['content']; ?>
    </div>
  </div>
  
<?php
  }
?>
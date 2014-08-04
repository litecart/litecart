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
  
// Information box
  ob_start();
  include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_customer_service_links.inc.php');
  document::$snippets['column_left'] = ob_get_clean();

  if (empty($_GET['page_id'])) {
  
  // Store map
    $box_store_map = new view();
    echo $box_store_map->stitch('file', 'box_store_map');
  
  // Contact us
    $box_contact_us = new view();
    echo $box_contact_us->stitch('file', 'box_contact_us');
    
  } else {
  
  // Box information
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
    
    $snippets = array(
      'title' => $page['title'],
      'content' => $page['content'],
    );
    
    $box_information = new view();
    echo $box_information->stitch('file', 'box_information');
  }
  
?>
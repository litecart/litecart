<?php
  
  document::$snippets['title'][] = language::translate('support.php:head_title', 'Customer Service');
  document::$snippets['description'] = language::translate('support.php:meta_description', '');
  
  breadcrumbs::add(language::translate('title_customer_service', 'Customer Service'), document::ilink('customer_service'));
  
  if (!empty($_POST['send'])) {
  
    if (settings::get('captcha_enabled')) {
      $captcha = functions::captcha_get('contact_us');
      if (empty($captcha) || $captcha != $_POST['captcha']) notices::add('errors', language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
    }
    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (empty($_POST['email'])) notices::add('errors', language::translate('error_must_enter_email', 'You must enter a valid email address'));
    
    if (empty(notices::$data['errors'])) {
      
      $result = functions::email_send(
        '"'. $_POST['name'] .'" <'. $_POST['email'] .'>',
        settings::get('store_email'),
        $_POST['subject'],
        $_POST['message']
      );
      
      if ($result) {
        notices::add('success', language::translate('success_your_email_was_sent', 'Your email has successfully been sent'));
        header('Location: '. document::ilink());
        exit;
      } else {
        notices::add('errors', language::translate('error_sending_email_for_unknown_reason', 'The email could not be sent for an unknown reason'));
      }
    }
  }
  
// Information box
  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_customer_service_links.inc.php');
  document::$snippets['column_left'] = ob_get_clean();

  if (empty($_GET['page_id'])) {
  
  // Store map
    $box_store_map = new view();
    echo $box_store_map->stitch('views/box_store_map');
  
  // Contact us
    $box_contact_us = new view();
    echo $box_contact_us->stitch('views/box_contact_us');
    
  } else {
  
  // Box information
    $pages_query = database::query(
      "select p.id, p.status, pi.title, pi.content, pi.head_title, pi.meta_description from ". DB_TABLE_PAGES ." p
      left join ". DB_TABLE_PAGES_INFO ." pi on (p.id = pi.page_id and pi.language_code = '". language::$selected['code'] ."')
      where p.id = '". (int)$_GET['page_id'] ."'
      limit 1;"
    );
    $page = database::fetch($pages_query);
    
    if (empty($page['id'])) {
      notices::add('errors', language::translate('error_410_gone', 'The requested file is no longer available'));
      http_response_code(410);
      header('Refresh: 0; url='. document::ilink(''));
      exit;
    }
    
    if (empty($page['status'])) {
      notices::add('errors', language::translate('error_404_not_found', 'The requested file could not be found'));
      http_response_code(404);
      header('Refresh: 0; url='. document::ilink(''));
      exit;
    }
    
    document::$snippets['title'][] = !empty($page['head_title']) ? $page['head_title'] : $page['title'];
    document::$snippets['description'] = !empty($page['meta_description']) ? $page['meta_description'] : '';
    
    breadcrumbs::add($page['title'], document::ilink('customer_service', array(), array('page_id')));
    
    $box_information = new view();
    
    $box_information->snippets = array(
      'title' => $page['title'],
      'content' => $page['content'],
    );
    
    echo $box_information->stitch('views/box_information');
  }
  
?>
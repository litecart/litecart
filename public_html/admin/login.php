<?php
  require_once('../includes/app_header.inc.php');
  
  document::$template = settings::get('store_template_admin');
  document::$layout = 'login';
  
  if (!isset($_POST['redirect_url'])) $_POST['redirect_url'] = null;
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  if (!empty(user::$data['id'])) notices::add('notice', language::translate('text_already_logged_in', 'You are already logged in'));
  
  if (!empty($_POST['login'])) {
    
    if (basename(parse_url($_POST['redirect_url'], PHP_URL_PATH)) == basename(__FILE__)) $_POST['redirect_url'] = null;
    
    user::login($_POST['username'], $_POST['password'], $_POST['redirect_url'], isset($_POST['remember_me']) ? $_POST['remember_me'] : false);
  }
  
  if (empty($_POST['username']) && !empty($_SERVER['PHP_AUTH_USER'])) $_POST['username'] = !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
  
  $page_login = new view();
  $page_login->snippets = array(
    'action' => !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::link(WS_DIR_ADMIN),
  );
  
  echo $page_login->stitch('views/login');
  
  require_once vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
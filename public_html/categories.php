<?php
  require_once('includes/app_header.inc.php');
  
  breadcrumbs::add(language::translate('title_categories', 'Categories'), document::link(basename(__FILE__)));
  
  document::$snippets['title'][] = language::translate('title_categories', 'Categories');
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';
  
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'categories.inc.php');
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
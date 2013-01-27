<?php
  require_once('includes/app_header.inc.php');
  
  $system->breadcrumbs->add($system->language->translate('title_categories', 'Categories'), $system->document->link(basename(__FILE__)));
  
  $system->document->snippets['title'][] = $system->language->translate('title_categories', 'Categories');
  //$system->document->snippets['keywords'] = '';
  //$system->document->snippets['description'] = '';
  
  include(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'categories.inc.php');
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>
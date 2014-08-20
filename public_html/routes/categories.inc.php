<?php
  breadcrumbs::add(language::translate('title_categories', 'Categories'));
  
  document::$snippets['title'][] = language::translate('title_categories', 'Categories');
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';
  
  include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
  
  include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_categories.inc.php');
?>
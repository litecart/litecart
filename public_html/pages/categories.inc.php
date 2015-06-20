<?php
  breadcrumbs::add(language::translate('title_categories', 'Categories'));
  
  document::$snippets['title'][] = language::translate('title_categories', 'Categories');
  
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');
  
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_categories.inc.php');
?>
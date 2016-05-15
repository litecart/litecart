<?php
  document::$snippets['title'][] = language::translate('categories:head_title', 'Categories');
  document::$snippets['description'] = language::translate('categories:meta_description', '');

  breadcrumbs::add(language::translate('title_categories', 'Categories'));

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'column_left.inc.php');

  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_categories.inc.php');
?>
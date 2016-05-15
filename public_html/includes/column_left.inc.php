<?php
  $column_left = new view();
  document::$snippets['column_left'] = $column_left->stitch('views/column_left');

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_search.inc.php');
  document::$snippets['box_search'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_category_tree.inc.php');
  document::$snippets['box_category_tree'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_filter.inc.php');
  document::$snippets['box_filter'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_recently_viewed_products.inc.php');
  document::$snippets['box_recently_viewed_products'] = ob_get_clean();

  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_account.inc.php');
  document::$snippets['box_account'] = ob_get_clean();
?>
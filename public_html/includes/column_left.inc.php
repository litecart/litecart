<?php ob_start(); // Begin capture column left ?>
<aside class="shadow rounded-corners">
  <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_search.inc.php'); ?>
  <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_category_tree.inc.php'); ?>
  <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_manufacturers_list.inc.php'); ?>
  <?php include vqmod::modcheck(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_filter.inc.php'); ?>
</aside>
<?php document::$snippets['column_left'] = ob_get_clean(); // End capture ?>
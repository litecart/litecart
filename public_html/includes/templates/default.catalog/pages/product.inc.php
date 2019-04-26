<div id="sidebar">
  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_category_tree.inc.php'); ?>

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_recently_viewed_products.inc.php'); ?>
</div>

<div id="content">
  {snippet:notices}
  {snippet:breadcrumbs}

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_TEMPLATE . 'views/box_product.inc.php'); ?>

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_similar_products.inc.php'); ?>

  <?php include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_also_purchased_products.inc.php'); ?>
</div>
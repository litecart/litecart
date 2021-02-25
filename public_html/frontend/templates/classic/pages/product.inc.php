<main id="main">
  <div id="sidebar">
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_category_tree.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_recently_viewed_products.inc.php'); ?>
  </div>

  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <?php include vmod::check(FS_DIR_TEMPLATE . 'views/box_product.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_similar_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_also_purchased_products.inc.php'); ?>
  </div>
</main>
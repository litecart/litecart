<main id="main">
  <div id="sidebar">
    <?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>

    <?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
  </div>

  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <?php include FS_DIR_TEMPLATE . 'partials/box_product.inc.php'; ?>

    <?php include 'app://frontend/partials/box_similar_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_also_purchased_products.inc.php'; ?>
  </div>
</main>
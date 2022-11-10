<div class="fourteen-forty container">
  <div class="layout row">
    <div class="hidden-xs hidden-sm col-md-3">
      <div id="sidebar">
      <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_category_tree.inc.php'); ?>

      <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_recently_viewed_products.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <main id="content">
        {snippet:notices}
        {snippet:breadcrumbs}

        <?php include vmod::check(FS_DIR_TEMPLATE . 'views/box_product.inc.php'); ?>

        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_similar_products.inc.php'); ?>

        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_also_purchased_products.inc.php'); ?>
      </main>
    </div>
  </div>
</div>
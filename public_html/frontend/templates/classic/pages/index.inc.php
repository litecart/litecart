<main id="main">
  <div id="sidebar">
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_category_tree.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_recently_viewed_products.inc.php'); ?>
  </div>

  <div id="content">
    {{notices}}

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_slides.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_campaign_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_popular_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_latest_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_brand_logotypes.inc.php'); ?>
  </div>
</main>

<?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_newsletter_subscribe.inc.php'); ?>

<main id="main" class="container">
  <div id="content">
    {snippet:notices}

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_slides.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_manufacturer_logotypes.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_campaign_products.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_popular_products.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_latest_products.inc.php'); ?>
    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_categories.inc.php'); ?>
  </div>
</main>
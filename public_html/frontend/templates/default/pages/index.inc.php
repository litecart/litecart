<main id="main" class="container">
  <div id="content">
    {{notices}}

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_slides.inc.php'); ?>

    <div class="row">
      <div class="col-md-4">
        <div class="box white" style="background: cornsilk;"></div>
      </div>

      <div class="col-md-4">
        <div class="box white" style="background: ivory;"></div>
      </div>

      <div class="col-md-4">
        <div class="box white" style="background: seashell;"></div>
      </div>
    </div>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_campaign_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_popular_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_latest_products.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_categories.inc.php'); ?>

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_brand_logotypes.inc.php'); ?>
  </div>
</main>

<?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_newsletter_subscribe.inc.php'); ?>

<main id="main" class="container">
  <div id="content">
    {{notices}}

    <?php include 'app://frontend/partials/box_categories.inc.php'; ?>

    <?php include 'app://frontend/partials/box_slides.inc.php'; ?>

    <div class="row layout">
      <div class="col-md-4">
        <?php echo functions::draw_banner('left'); ?>
      </div>

      <div class="col-md-4">
        <?php echo functions::draw_banner('middle'); ?>
      </div>

      <div class="col-md-4">
        <?php echo functions::draw_banner('right'); ?>
      </div>
    </div>


    <?php include 'app://frontend/partials/box_campaign_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_popular_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_latest_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_brand_logotypes.inc.php'; ?>
  </div>
</main>

<?php include 'app://frontend/partials/box_newsletter_subscribe.inc.php'; ?>

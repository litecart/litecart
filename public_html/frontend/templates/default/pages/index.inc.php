<main id="main" class="container">
  <div id="content">
    {{notices}}

    <?php if ($leaderboard = functions::draw_banner('leaderboard')) { ?>
    <div id="box-leaderboard" style="margin-bottom: 2em;">
      <?php echo $leaderboard; ?>
    </div>
    <?php } ?>

    <div class="row layout">
      <div class="col-xs-6 col-md-4">
        <?php echo functions::draw_banner('left'); ?>
      </div>

      <div class="col-xs-6 col-md-4">
        <?php echo functions::draw_banner('middle'); ?>
      </div>

      <div class="hidden-xs hidden-sm col-md-4">
        <?php echo functions::draw_banner('right'); ?>
      </div>
    </div>

    <?php include 'app://frontend/partials/box_categories.inc.php'; ?>

    <?php include 'app://frontend/partials/box_campaign_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_popular_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_latest_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_brand_logotypes.inc.php'; ?>
  </div>
</main>

<?php include 'app://frontend/partials/box_newsletter_subscribe.inc.php'; ?>

<main id="main">
  <div id="sidebar">
    <?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>

    <?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
  </div>

  <div id="content">
    {{notices}}

    <?php include 'app://frontend/partials/box_slides.inc.php'; ?>

    <?php include 'app://frontend/partials/box_campaign_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_popular_products.inc.php'; ?>

    <?php include 'app://frontend/partials/box_latest_products.inc.php'; ?>
  </div>
</main>

<?php include 'app://frontend/partials/box_brand_logotypes.inc.php'; ?>

<?php include 'app://frontend/partials/box_newsletter_subscribe.inc.php'; ?>

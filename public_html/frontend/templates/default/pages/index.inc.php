<main id="main" class="container">
  <div id="content">
    {{notices}}

    <?php include vmod::check(FS_DIR_APP . 'frontend/boxes/box_slides.inc.php'); ?>

    <div class="row layout">
      <div class="col-md-4">
        <div class="box white" style="padding: 0; line-height: 0; background: cornsilk;">
          <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 460 200">
              <rect fill="#ddd" width="100%" height="100%"/>
              <text fill="rgba(0,0,0,0.5)" font-family="sans-serif" font-size="30" dy="10.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">460×200</text>
            </svg>
          </a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="box white" style="padding: 0; line-height: 0; background: ivory;">
          <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 460 200">
              <rect fill="#eee" width="100%" height="100%"/>
              <text fill="rgba(0,0,0,0.5)" font-family="sans-serif" font-size="30" dy="10.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">460×200</text>
            </svg>
          </a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="box white" style="padding: 0; line-height: 0; background: seashell;">
          <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 460 200">
              <rect fill="#ccc" width="100%" height="100%"/>
              <text fill="rgba(0,0,0,0.5)" font-family="sans-serif" font-size="30" dy="10.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">460×200</text>
            </svg>
          </a>
        </div>
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

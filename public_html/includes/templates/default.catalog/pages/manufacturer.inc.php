<style>
.btn-group.float-end {
  margin-inline-end: 1.5em;
  margin-top: 1.5em;
}
</style>

<div class="fourteen-forty">
  <div class="layout row">
    <div class="col-md-3">

      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_manufacturer_links.inc.php'); ?>
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_recently_viewed_products.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <main id="content">
        {snippet:notices}
        {snippet:breadcrumbs}

        <article id="box-manufacturer" class="card">
          <?php if ($products) { ?>
          <div class="btn-group float-end hidden-xs">
<?php
  foreach ($sort_alternatives as $key => $value) {
    if ($_GET['sort'] == $key) {
      echo '<span class="btn btn-default active">'. $value .'</span>';
    } else {
      echo '<a class="btn btn-default" href="'. document::href_ilink(null, ['sort' => $key], true) .'">'. $value .'</a>';
    }
  }
?>
          </div>
          <?php } ?>

          <div class="card-header">
            <h1 class="card-title"><?php echo $title; ?></h1>
          </div>

          <div class="card-body">
            <?php if ($_GET['page'] == 1 && $description) { ?>
            <p class="description"><?php echo $description; ?></p>
            <?php } ?>

            <?php if ($products) { ?>
            <section class="listing products columns">
              <?php foreach ($products as $product) echo functions::draw_listing_product($product, ['manufacturer_id']); ?>
            </section>
            <?php } ?>
          </div>

          <?php if ($pagination) { ?>
          <div class="card-footer">
            <?php echo $pagination; ?>
          </div>
          <?php } ?>
        </article>
      </main>

    </div>
  </div>
</div>

<div class="fourteen-forty">
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

        <article id="box-category">

          <div class="row" style="margin-bottom: 0;">
            <?php if ($_GET['page'] == 1 && $image) { ?>
            <div class="hidden-xs hidden-sm col-md-4">
              <div class="thumbnail" style="margin-bottom: var(--gutter-y);">
                <img src="<?php echo document::href_rlink(FS_DIR_APP . $image['thumbnail_1x']); ?>" style="aspect-ratio: <?php echo $image['ratio']; ?>;">
              </div>
            </div>
            <?php } ?>

            <div class="<?php echo $image ? 'col-md-8' : 'col-md-12'; ?>">
              <h1 class="title"><?php echo $h1_title; ?></h1>

              <?php if ($_GET['page'] == 1 && $description) { ?>
              <div class="description" style="margin-bottom: var(--gutter-y);">
                <?php echo $description; ?>
              </div>
              <?php } ?>
            </div>
          </div>

          <?php if ($_GET['page'] == 1 && $subcategories) { ?>
          <section class="listing categories" style="margin-bottom: var(--gutter-y);">
            <?php foreach ($subcategories as $subcategory) echo functions::draw_listing_category($subcategory); ?>
          </section>
          <?php } ?>

          <?php if (isset($_GET['product_name']) || isset($_GET['attributes']) || isset($_GET['manufacturers']) || $products) { ?>
          <div class="card">
            <div class="card-body">
              <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_filter.inc.php'); ?>

              <section class="listing products <?php echo (isset($_GET['list_style']) && $_GET['list_style'] == 'rows') ? 'rows' : 'columns'; ?>">
                <?php foreach ($products as $product) echo functions::draw_listing_product($product, ['category_id']); ?>
              </section>
            </div>

            <?php if ($pagination) { ?>
            <div class="card-footer">
              <?php echo $pagination; ?>
            </div>
            <?php } ?>
          </div>
          <?php } ?>

        </article>
      </main>
    </div>

  </div>
</div>

<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">

        <?php if (!empty($main_category)) { ?>
        <h1 style="margin-top: 0;"><?php echo $main_category['name']; ?></h1>

        <?php if (!empty($main_category['image'])) { ?>
        <div style="margin-bottom: 2em;">
          <a href="<?php echo document::href_ilink('category', ['category_id' => $main_category['id']]); ?>">
            <img class="thumbnail fit" src="<?php echo document::href_rlink($main_category['image']['thumbnail']); ?>" style="aspect-ratio: <?php echo $main_category['image']['viewport']['ratio']; ?>;" />
          </a>
        </div>
        <?php } ?>
        <?php } ?>

        <?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>
        <?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}
        {{breadcrumbs}}

        <?php include 'app://frontend/templates/'.settings::get('template').'/partials/box_product.inc.php'; ?>
        <?php include 'app://frontend/partials/box_similar_products.inc.php'; ?>
        <?php include 'app://frontend/partials/box_also_purchased_products.inc.php'; ?>
    </div>
  </div>
</main>
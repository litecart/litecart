<main id="main" class="container">
  <div class="layout row">

    <div class="hidden-xs hidden-sm col-md-3">
      <div id="sidebar">
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
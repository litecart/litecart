<main id="main" class="container">
  <div class="row layout">
    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_category_tree.inc.php'); ?>
        <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_recently_viewed_products.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <div id="content">
        {{notices}}
        {{breadcrumbs}}

        <section id="box-search-results" class="card">
          <div class="card-header">
            <h1 class="card-title">{{title}}</h1>
          </div>

          <div class="card-body">
            <?php if ($products) { ?>
            <div class="btn-group float-end hidden-xs">
<?php
  $separator = false;
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


            <?php if ($products) { ?>
            <section class="listing products">
              <?php foreach ($products as $product) echo functions::draw_listing_product($product, 'column'); ?>
            </section>
            <?php } else { ?>
            <div><em><?php echo language::translate('text_no_matching_results', 'No matching results'); ?></em></div>
            <?php } ?>

          </div>

          <?php if ($pagination) { ?>
          <div class="card-footer">
            {{pagination}}
          </div>
          <?php } ?>
        </section>

      </div>
    </div>
  </div>
</main>

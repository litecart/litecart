<div id="sidebar">
  <?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>
  <?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
</div>

<main id="main" class="container">
  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <section id="box-search-results" class="box">

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

      <h1 class="title"><?php echo $title; ?></h1>

      <?php if ($products) { ?>
      <section class="listing products columns">
        <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
      </section>
      <?php } else { ?>
      <div><em><?php echo language::translate('text_no_matching_results', 'No matching results'); ?></em></div>
      <?php } ?>

      <?php echo $pagination; ?>
    </section>
  </div>
</main>
<main id="main" class="container">
  <div id="content">
    {{notices}}
    {{breadcrumbs}}

    <section id="box-search-results" class="card">

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

      <div class="card-header">
        <h1 class="card-title"><?php echo $title; ?></h1>
      </div>

      <div class="card-body">

        <?php if ($categories) { ?>
        <nav class="nav nav-pills" style="margin-bottom: 1em;">
          <?php foreach ($categories as $category) { ?><a class="nav-item" href="<?php echo document::href_ilink('category', ['category_id' => $category['id']]); ?>"><?php echo $category['name']; ?></a><?php } ?>
        </nav>
        <?php } ?>

        <?php if ($products) { ?>
        <section class="listing products columns">
          <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
        </section>
        <?php } ?>

        <?php if (!$categories && !$products) { ?>
        <div><em><?php echo language::translate('text_no_matching_results', 'No matching results'); ?></em></div>
        <?php } ?>
      </div>

      <?php if ($pagination) { ?>
      <div class="card-footer">
        <?php echo $pagination; ?>
      </div>
      <?php } ?>

    </section>
  </div>
</main>
<style>
.btn-group.float-end {
  margin-inline-end: 1.5em;
  margin-top: 1.5em;
}
</style>

<div class="fourteen-forty container">
  <main id="content">
    {snippet:notices}
    {snippet:breadcrumbs}

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
        <?php if ($products) { ?>
        <section class="listing products columns">
          <?php foreach ($products as $product) echo functions::draw_listing_product($product); ?>
        </section>
        <?php } else { ?>
        <div><em><?php echo language::translate('text_no_matching_results', 'No matching results'); ?></em></div>
        <?php } ?>
      </div>

      <?php if ($pagination) { ?>
      <div class="card-footer">
        <?php echo $pagination; ?>
      </div>
      <?php } ?>
    </section>
  </main>
</div>

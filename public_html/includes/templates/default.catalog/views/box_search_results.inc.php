<div id="box-search-results" class="box">
  <nav class="filter" style="float: right;">
<?php
    $separator = false;
    foreach ($sort_alternatives as $key => $value) {
      if ($separator) echo ' ';
      if ($_GET['sort'] == $key) {
        echo '<span class="button active">'. $value .'</span>';
      } else {
        echo '<a class="button" href="'. document::href_ilink(null, array('sort' => $key), true) .'">'. $value .'</a>';
      }
      $separator = true;
    }
?>
  </nav>

  <h1 class="title"><?php echo $title; ?></h1>

  <div class="content">

    <?php if ($products) { ?>
    <ul class="listing-wrapper products">
      <?php foreach ($products as $product) echo functions::draw_listing_product($product, 'column'); ?>
    </ul>
    <?php } ?>

    <?php echo $pagination; ?>
  </div>
</div>
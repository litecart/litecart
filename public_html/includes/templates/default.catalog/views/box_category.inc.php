<div style="margin: 0 -20px 2em -20px;">
  <!--snippet:breadcrumbs-->
</div>

<div id="box-category" class="box">
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

  <h1 class="title"><?php echo $h1_title; ?></h1>

  <div class="content">

    <?php if ($_GET['page'] == 1) { ?>
    <?php if ($description) { ?>
    <div class="description-wrapper">
      <p class="category-description"><?php echo $description; ?></p>
    </div>
    <?php } ?>

    <?php if ($subcategories) { ?>
    <ul class="listing-wrapper categories">
      <?php foreach ($subcategories as $subcategory) echo functions::draw_listing_category($subcategory); ?>
    </ul>
    <?php } ?>
    <?php } ?>

    <?php if ($products) { ?>
    <ul class="listing-wrapper products">
      <?php foreach ($products as $product) echo functions::draw_listing_product($product, $product['listing_type']); ?>
    </ul>
    <?php } ?>

    <?php echo $pagination; ?>
  </div>
</div>
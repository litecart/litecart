<div id="box-manufacturer" class="box">
  <div class="heading">
    <span class="filter" style="float: right;">
<?php
    $sort_alternatives = array(
      'popularity' => language::translate('title_popularity', 'Popularity'),
      'name' => language::translate('title_name', 'Name'),
      'price' => language::translate('title_price', 'Price') ,
      'date' => language::translate('title_date', 'Date'),
    );
    
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
    </span>
    <h1><?php echo $title; ?></h1>
  </div>
  
  <div class="content">
  
    <?php if ($_GET['page'] == 1 && $description) { ?>
    <div class="description-wrapper">
      <p class="manufacturer-description"><?php echo $description; ?></p>
    </div>
    <?php } ?>
    
    <?php if ($products) { ?>
    <ul class="listing-wrapper products">
      <?php foreach ($products as $product) echo functions::draw_listing_product($product, 'column'); ?>
    </ul>
    <?php } ?>
    
    <?php echo $pagination; ?>
  </div>
</div>

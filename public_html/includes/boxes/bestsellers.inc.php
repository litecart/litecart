<?php
  $system->functions->draw_fancybox('a.fancybox');
  
  $products_query = $system->functions->catalog_products_query(array('purchased' => true, 'sort' => 'popularity', 'limit' => 8));
  
  if ($system->database->num_rows($products_query) == 0) {
    $products_query = $system->functions->catalog_products_query(array('sort' => 'popularity', 'limit' => 8));
  }
  
  if ($system->database->num_rows($products_query) == 0) return;
?>
<div class="box" id="box-bestsellers">
  <div class="heading"><h3><?php echo $system->language->translate('title_bestsellers', 'Bestsellers'); ?></h3></div>
  <div class="content">
    <div class="listing-wrapper">
<?php
  while ($listing_product = $system->database->fetch($products_query)) {
    echo $system->functions->draw_listing_product($listing_product);
  }
?>
    </div>
  </div>
</div>
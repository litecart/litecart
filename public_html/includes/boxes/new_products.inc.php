<?php
  $system->functions->draw_fancybox('a.fancybox');
?>
<div class="box" id="box-latest-products">
  <div class="heading"><h3><?php echo $system->language->translate('title_new_products', 'New Products'); ?></h3></div>
  <div class="content">
    <div class="listing-wrapper">
<?php
  $products_query = $system->functions->catalog_products_query(array('sort' => 'date', 'limit' => 8));
  while ($listing_product = $system->database->fetch($products_query)) {
    echo $system->functions->draw_listing_product($listing_product);
  }
?>
    </div>
  </div>
</div>
<?php
  $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => 10));
  
  if (database::num_rows($products_query) == 0) {
    $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => 10));
  }
  
  if (database::num_rows($products_query) == 0) return;
  
  functions::draw_fancybox('a.fancybox');
?>
<div class="box" id="box-most-popular">
  <div class="heading"><h3><?php echo language::translate('title_most_popular', 'Most Popular'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper products">
<?php
  while ($listing_product = database::fetch($products_query)) {
    echo functions::draw_listing_product($listing_product);
  }
?>
    </ul>
  </div>
</div>
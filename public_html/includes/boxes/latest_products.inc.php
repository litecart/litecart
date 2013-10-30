<?php
  $products_query = functions::catalog_products_query(array('sort' => 'date', 'limit' => 10));
  if (database::num_rows($products_query) == 0) return;

  functions::draw_fancybox('a.fancybox');
?>
<div class="box" id="box-latest-products">
  <div class="heading"><h3><?php echo language::translate('title_latest_products', 'Latest Products'); ?></h3></div>
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
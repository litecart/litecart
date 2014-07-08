<?php
  functions::draw_fancybox('a.fancybox');
  
  $box_campaigns_cache_id = cache::cache_id('box_campaigns', array('language', 'currency', 'prices'));
  if (cache::capture($box_campaigns_cache_id, 'file')) {
  
    $products_query = functions::catalog_products_query(array('campaign' => true, 'sort' => 'rand', 'limit' => 5));
    
    if (database::num_rows($products_query)) {
?>

<div class="box" id="box-campaigns">
  <div class="heading"><h3><?php echo language::translate('title_campaigns', 'Campaigns'); ?></h3></div>
  <div class="content">
    <ul class="listing-wrapper products">
<?php
      while ($listing_product = database::fetch($products_query)) {
        echo functions::draw_listing_product_column($listing_product);
      }
?>
    </ul>
  </div>
</div>
<?php
    }
    
    cache::end_capture($box_campaigns_cache_id);
  }
?>
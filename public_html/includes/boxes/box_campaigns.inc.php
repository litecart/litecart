<?php
  functions::draw_fancybox('a.fancybox');
  
  $cache_id = cache::cache_id('box_campaigns', array('language', 'currency', 'prices'));
  if (cache::capture($cache_id, 'file')) {
    
    $box_campaigns = new view();
    
    $products_query = functions::catalog_products_query(array('campaign' => true, 'sort' => 'rand', 'limit' => 4));
    
    if (database::num_rows($products_query) == 0) return;
    
    $box_campaigns->snippets['products'] = '';
    while ($listing_product = database::fetch($products_query)) {
      $box_campaigns->snippets['products'] .= functions::draw_listing_product($listing_product, 'column');
    }
    
    echo $box_campaigns->stitch('file', 'box_campaigns');
    
    cache::end_capture($cache_id);
  }
?>
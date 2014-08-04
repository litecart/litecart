<?php
  functions::draw_fancybox('a.fancybox');
  
  $cache_id = cache::cache_id('box_campaigns', array('language', 'currency', 'prices'));
  if (cache::capture($cache_id, 'file')) {
    
    $products_query = functions::catalog_products_query(array('campaign' => true, 'sort' => 'rand', 'limit' => 4));
    
    if (database::num_rows($products_query) == 0) return;
    
    $campaigns_snippets = array(
      'products' => '',
    );
    
    while ($listing_product = database::fetch($products_query)) {
      $campaigns_snippets['products'] .= functions::draw_listing_product($listing_product, 'column');
    }
    
    echo document::stitch('file', 'box_campaigns', $campaigns_snippets);
    
    cache::end_capture($cache_id);
  }
?>
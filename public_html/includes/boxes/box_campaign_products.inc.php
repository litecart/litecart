<?php
  functions::draw_fancybox('a.fancybox');
  
  $box_campaign_products_cache_id = cache::cache_id('box_campaign_products', array('language', 'currency', 'prices'));
  if (cache::capture($box_campaign_products_cache_id, 'file')) {
    
    $box_campaign_products = new view();
    
    $products_query = functions::catalog_products_query(array('campaign' => true, 'sort' => 'rand', 'limit' => 4));
    
    if (database::num_rows($products_query)) {
    
      $box_campaign_products->snippets['products'] = '';
      while ($listing_product = database::fetch($products_query)) {
        $box_campaign_products->snippets['products'] .= functions::draw_listing_product($listing_product, 'column');
      }
      
      echo $box_campaign_products->stitch('box_campaign_products');
    }
    
    cache::end_capture($box_campaign_products_cache_id);
  }
?>
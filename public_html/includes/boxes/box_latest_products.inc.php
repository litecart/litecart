<?php
  functions::draw_fancybox('a.fancybox');

  $box_latest_products_cache_id = cache::cache_id('box_latest_products', array('language', 'currency', 'prices'));
  if (cache::capture($box_latest_products_cache_id, 'file')) {
  
    $products_query = functions::catalog_products_query(array('sort' => 'date', 'limit' => 10));
    if (database::num_rows($products_query)) {
    
      $box_latest_products = new view();
      
      $box_latest_products->snippets['products'] = '';
      
      while ($listing_product = database::fetch($products_query)) {
        $box_latest_products->snippets['products'] .= functions::draw_listing_product($listing_product, 'column');
      }
      
      echo $box_latest_products->stitch('box_latest_products');
    }
    
    cache::end_capture($box_latest_products_cache_id);
  }
?>
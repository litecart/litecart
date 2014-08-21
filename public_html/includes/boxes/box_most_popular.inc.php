<?php
  functions::draw_fancybox('a.fancybox');
  
  $box_most_popular_cache_id = cache::cache_id('box_most_popular_products', array('language', 'currency', 'prices'));
  if (cache::capture($box_most_popular_cache_id, 'file')) {
  
    $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => 10));
    
    if (database::num_rows($products_query) == 0) {
      $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => 10));
    }
    
    if (database::num_rows($products_query)) {
    
      $box_most_popular = new view();
      
      $box_most_popular->snippets['products'] = '';
      
      while ($listing_product = database::fetch($products_query)) {
        $box_most_popular->snippets['products'] .= functions::draw_listing_product($listing_product, 'column');
      }
      
      echo $box_most_popular->stitch();
    }
    
    cache::end_capture($box_most_popular_cache_id);
  }
?>
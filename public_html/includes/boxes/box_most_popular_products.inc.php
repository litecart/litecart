<?php
  functions::draw_fancybox('a.fancybox');
  
  $box_most_popular_products_cache_id = cache::cache_id('box_most_popular_products', array('language', 'currency', 'prices'));
  if (cache::capture($box_most_popular_products_cache_id, 'file')) {
  
    $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => 10));
    
    if (database::num_rows($products_query) == 0) {
      $products_query = functions::catalog_products_query(array('sort' => 'popularity', 'limit' => 10));
    }
    
    if (database::num_rows($products_query)) {
    
      $box_most_popular_products = new view();
      
      $box_most_popular_products->snippets['products'] = array();
      while ($listing_product = database::fetch($products_query)) {
        $box_most_popular_products->snippets['products'][] = $listing_product;
      }
      
      echo $box_most_popular_products->stitch('views/box_most_popular_products');
    }
    
    cache::end_capture($box_most_popular_products_cache_id);
  }
?>
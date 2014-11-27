<?php
  if (!is_object($product)) return;
  
  if (settings::get('box_similar_products_num_items') == 0) return;
  
  functions::draw_fancybox('a.fancybox');
  
  $box_similar_products_cache_id = cache::cache_id('box_similar_products', array('get', 'language', 'currency', 'prices'));
  if (cache::capture($box_similar_products_cache_id, 'file')) {
    
    $product_groups = array();
    if ($product->product_group_ids) {
      foreach ($product->product_group_ids as $product_group) {
        $product_groups[] = "find_in_set('". database::input($product_group) ."', p.product_groups)";
      }
    }
    
    $keywords = array();
    if ($product->keywords != '') {
      foreach (explode(',', $product->keywords) as $keyword) {
        $keyword = trim($keyword);
        if (empty($keyword)) continue;
        $keywords[] = $keyword;
      }
    }
    
    $products_query = functions::catalog_products_query(array(
      'product_name' => $product->name[language::$selected['code']],
      'categories' => isset($_GET['category_id']) ? array($_GET['category_id']) : array_keys($product->categories),
      'manufacturers' => array($product->manufacturer_id),
      'product_groups' => $product_groups,
      'exclude_products' => $product->id,
      'keywords' => $keywords,
      'sort' => 'occurrences',
      'limit' => settings::get('box_similar_products_num_items'),
    ));
    
    if (database::num_rows($products_query) > 0) {
    
      $box_similar_products = new view();
      
      $box_similar_products->snippets['products'] = array();
      while ($listing_product = database::fetch($products_query)) {
        if (empty($listing_product['occurrences'])) break;
        $box_similar_products->snippets['products'][] = $listing_product;
      }
      
      echo $box_similar_products->stitch('views/box_similar_products');
    }
    
    cache::end_capture($box_similar_products_cache_id);
  }
?>
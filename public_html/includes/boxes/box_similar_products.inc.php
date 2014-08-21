<?php
  if (!is_object($product)) return;
  
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
      'limit' => 10,
    ));
    
    if (database::num_rows($products_query) == 0) return;
    
    $box_similar_products = new view();
    
    $box_similar_products->snippets['products'] = '';
    while ($listing_product = database::fetch($products_query)) {
      if (empty($listing_product['occurrences'])) break;
      $box_similar_products->snippets['products'] .= functions::draw_listing_product($listing_product, 'column');
    }
    
    echo $box_similar_products->stitch('box_similar_products');
    
    cache::end_capture($box_similar_products_cache_id);
  }
?>
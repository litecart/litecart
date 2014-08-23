<?php
  
  class url_product {
    
    function routes() {
      return array(
        array(
          'pattern' => '#^(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$#',
          'page' => 'product',
          'params' => 'category_id=$1&product_id=$2',
        ),
      );
    }
  
  	function rewrite($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['product_id'])) return false;
      
      $product = new ref_product($parsed_link['query']['product_id'], $language_code);
      
      if (!$product->id) return $parsed_link;
      
      
      if (!empty($parsed_link['query']['category_id']) && !empty($product->categories)) {
        $parsed_link['path'] = '';
        foreach ($product->categories as $category_id => $category_name) {
          $parsed_link['path'] .= functions::general_path_friendly($category_name[$language_code]) .'-c-'. $category_id .'/';
        }
        
      } else if (!empty($product->manufacturer)) {
        $parsed_link['path'] = functions::general_path_friendly($product->manufacturer['name']) .'-m-'. $product->manufacturer['id'] .'/';
      }
      
      $parsed_link['path'] .= functions::general_path_friendly($product->name[$language_code]) .'-p-'. $product->id;
      
      unset($parsed_link['query']['category_id']);
      unset($parsed_link['query']['product_id']);
      
      return $parsed_link;
    }
  }
  
?>
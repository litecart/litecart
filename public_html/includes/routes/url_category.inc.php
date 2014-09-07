<?php
  class url_category {
    
    function routes() {
      return array(
        array(
          'pattern' => '#^.*-c-([0-9]+)/?$#',
          'page' => 'category',
          'params' => 'category_id=$1',
        ),
      );
    }
    
    function rewrite($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['category_id'])) return;
      
      $category_trail = functions::catalog_category_trail($parsed_link['query']['category_id']);
      
      if (empty($category_trail)) return;
      
      $parsed_link['path'] = '';
      foreach ($category_trail as $category_id => $category_name) $parsed_link['path'] .= functions::general_path_friendly($category_name) .'-c-'. $category_id .'/';
      
      unset($parsed_link['query']['category_id']);
      
      return $parsed_link;
    }
  }
?>
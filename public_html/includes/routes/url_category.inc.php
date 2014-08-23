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
      
      $category_query = database::query(
        "select category_id, name from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$parsed_link['query']['category_id'] ."'
        and language_code = '". database::input($language_code) ."'
        limit 1;"
      );
      $category = database::fetch($category_query);
      if (empty($category)) return;
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME . functions::general_path_friendly($category['name']) .'-c-'. $category['category_id'] .'/';
      
      unset($parsed_link['query']['category_id']);
      
      return $parsed_link;
    }
  }
?>
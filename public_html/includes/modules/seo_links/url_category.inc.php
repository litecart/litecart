<?php
  
  class url_category {
    
  	function __construct() {
    }
    
    function process($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['category_id'])) return;
      
      $category_query = $GLOBALS['system']->database->query(
        "select category_id, name from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$parsed_link['query']['category_id'] ."'
        and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
        limit 1;"
      );
      $category = $GLOBALS['system']->database->fetch($category_query);
      if (empty($category)) return;
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME . $GLOBALS['system']->functions->general_path_friendly($category['name']) .'-c-'. $category['category_id'];
      
      unset($parsed_link['query']['category_id']);
      
      return $parsed_link;
    }
  }
  
?>
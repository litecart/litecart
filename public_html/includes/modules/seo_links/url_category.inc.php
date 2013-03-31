<?php
  
  class url_category {
    
  	function __construct($system) {
      $this->system = &$system;
    }
    
    function process($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['category_id'])) return;
      
      $category_query = $this->system->database->query(
        "select category_id, name from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$parsed_link['query']['category_id'] ."'
        and language_code = '". $this->system->database->input($language_code) ."'
        limit 1;"
      );
      $category = $this->system->database->fetch($category_query);
      if (empty($category)) return;
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME . $this->system->seo_links->url_friendly_string($category['name']) .'-c-'. $category['category_id'];
      
      unset($parsed_link['query']['category_id']);
      
      return $parsed_link;
    }
  }
  
?>
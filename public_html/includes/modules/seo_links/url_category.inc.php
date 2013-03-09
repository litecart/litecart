<?php
  
  class url_category {
    
    public $config = array(
      'doc' => 'category.php',
      'params' => array('category_id'),
      'seo_path' => '%title-c-%category_id',
    );
    
  	function __construct($system) {
      $this->system = &$system;
    }
    
    function title($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['category_id'])) return '';
      
      $category_query = $this->system->database->query(
        "select name from ". DB_TABLE_CATEGORIES_INFO ."
        where category_id = '". (int)$parsed_link['query']['category_id'] ."'
        and language_code = '". $this->system->database->input($language_code) ."'
        limit 1;"
      );
      $category = $this->system->database->fetch($category_query);
      if (empty($category)) return '';
      
      $title = $category['name'];
      
      return $this->system->seo_links->url_friendly_string($title);
    }
  }
  
?>
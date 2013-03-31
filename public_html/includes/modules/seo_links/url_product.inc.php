<?php
  
  class url_product {

  	function __construct($system) {
      $this->system = &$system;
    }
    
    function process($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['product_id'])) return false;
      
      $product_query = $this->system->database->query(
        "select product_id, name from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id = '". (int)$parsed_link['query']['product_id'] ."'
        and language_code = '". $this->system->database->input($language_code) ."'
        limit 1;"
      );
      $product = $this->system->database->fetch($product_query);
      
      if (empty($product)) die('ee');
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME . $this->system->seo_links->url_friendly_string($product['name']) .'-p-'. $product['product_id'];
      
      unset($parsed_link['query']['product_id']);
      
      return $parsed_link;
    }
  }
  
?>
<?php
  
  class seo_link_product {
    
    public $config = array(
      'doc' => 'product.php',
      'params' => array('product_id'),
      'seo_path' => '%title-p-%product_id',
    );
    
  	function __construct($system) {
      $this->system = $system;
    }
    
    function title($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['product_id'])) return '';
      
      $product_query = $this->system->database->query(
        "select name, short_description from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id = '". (int)$parsed_link['query']['product_id'] ."'
        and language_code = '". $this->system->database->input($language_code) ."'
        limit 1;"
      );
      $product = $this->system->database->fetch($product_query);
      if (empty($product)) return '';
      
      $title = !empty($product['short_description']) ? $product['short_description'] : $product['name'];
      
      return $this->system->seo_links->url_friendly_string($title);
    }
  }
  
?>
<?php
  
  class url_manufacturer {
    
    public $config = array(
      'doc' => 'manufacturer.php',
      'params' => array('manufacturer_id'),
      'seo_path' => '%title-m-%manufacturer_id',
    );
    
  	function __construct($system) {
      $this->system = &$system;
    }
    
    function title($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['manufacturer_id'])) return '';
      
      $manufacturer_query = $this->system->database->query(
        "select m.name, mi.short_description from ". DB_TABLE_MANUFACTURERS ." m
        left join ". DB_TABLE_MANUFACTURERS_INFO ." mi on (m.id = m.id and mi.language_code = '". $this->system->database->input($language_code) ."')
        where m.id = '". (int)$parsed_link['query']['manufacturer_id'] ."'
        limit 1;"
      );
      $manufacturer = $this->system->database->fetch($manufacturer_query);
      if (empty($manufacturer)) return '';
      
      $title = !empty($manufacturer['short_description']) ? $manufacturer['short_description'] : $manufacturer['name'];
      
      return $this->system->seo_links->url_friendly_string($title);
    }
  }
  
?>
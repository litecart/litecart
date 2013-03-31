<?php
  
  class url_designer {
    
  	function __construct($system) {
      $this->system = &$system;
    }
    
    function process($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['designer_id'])) return;
      
      $designer_query = $this->system->database->query(
        "select id, name from ". DB_TABLE_DESIGNERS ."
        where id = '". (int)$parsed_link['query']['designer_id'] ."'
        limit 1;"
      );
      $designer = $this->system->database->fetch($designer_query);
      if (empty($designer)) return;
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME . $this->system->seo_links->url_friendly_string($designer['name']) .'-d-'. $designer['id'];
      
      unset($parsed_link['query']['designer_id']);
      
      return $parsed_link;
    }
  }
  
?>
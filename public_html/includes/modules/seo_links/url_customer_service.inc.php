<?php
  
  class url_customer_service {
    
  	function __construct($system) {
      $this->system = &$system;
    }
    
    function process($parsed_link, $language_code) {
      
      if (!empty($parsed_link['query']['page_id'])) {
        $page_query = $this->system->database->query(
          "select page_id, title from ". DB_TABLE_PAGES_INFO ."
          where page_id = '". (int)$parsed_link['query']['page_id'] ."'
          and language_code = '". $this->system->database->input($language_code) ."'
          limit 1;"
        );
        $page = $this->system->database->fetch($page_query);
        
        if (!empty($page)) {
          $parsed_link['path'] = WS_DIR_HTTP_HOME . $this->system->seo_links->url_friendly_string($page['title']) .'-cs-'. $page['page_id'];
        }
        
      } else {
      
        $title = $this->system->language->translate('title_customer_service', 'Customer Service', $language_code);
        $parsed_link['path'] = WS_DIR_HTTP_HOME . $this->system->seo_links->url_friendly_string($title) .'-cs-0';
      }
      
      if (isset($parsed_link['query']['page_id'])) unset($parsed_link['query']['page_id']);
      
      return $parsed_link;
    }
  }
  
?>
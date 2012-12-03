<?php
  
  class seo_link_page {
    
    public $config = array(
      'doc' => 'page.php',
      'params' => array('page_id'),
      'seo_path' => '%title-i-%page_id',
    );
    
  	function __construct($system) {
      $this->system = $system;
    }
    
    function title($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['page_id'])) return '';
      
      $page_query = $this->system->database->query(
        "select title from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$parsed_link['query']['page_id'] ."'
        and language_code = '". $this->system->database->input($language_code) ."'
        limit 1;"
      );
      $page = $this->system->database->fetch($page_query);
      if (empty($page)) return '';
      
      return $this->system->seo_links->url_friendly_string($page['title']);
    }
  }
  
?>
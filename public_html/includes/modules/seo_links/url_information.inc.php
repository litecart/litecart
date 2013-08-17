<?php
  
  class url_information {
    
  	function __construct() {
    }
    
    function process($parsed_link, $language_code) {
      
      if (empty($parsed_link['query']['page_id'])) return false;
      
      $page_query = $GLOBALS['system']->database->query(
        "select page_id, title from ". DB_TABLE_PAGES_INFO ."
        where page_id = '". (int)$parsed_link['query']['page_id'] ."'
        and language_code = '". $GLOBALS['system']->database->input($language_code) ."'
        limit 1;"
      );
      $page = $GLOBALS['system']->database->fetch($page_query);
      
      if (empty($page)) return false;
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME . $GLOBALS['system']->functions->general_path_friendly($page['title']) .'-i-'. $page['page_id'];
      
      unset($parsed_link['query']['page_id']);
      
      return $parsed_link;
    }
  }
  
?>
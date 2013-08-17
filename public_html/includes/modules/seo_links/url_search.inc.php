<?php
  
  class url_search {

  	function __construct() {
    }
    
    function process($parsed_link, $language_code) {
      
      $parsed_link['path'] = WS_DIR_HTTP_HOME .'search/'. (isset($parsed_link['query']['query']) ? urlencode($parsed_link['query']['query']) : '');
      
      unset($parsed_link['query']['search']);
      unset($parsed_link['query']['query']);
      
      return $parsed_link;
    }
  }
  
?>
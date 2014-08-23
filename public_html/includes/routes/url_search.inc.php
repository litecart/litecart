<?php
  
  class url_search {

    function routes() {
      return array(
        array(
          'pattern' => '#^search/(.*)?$#',
          'page' => 'search',
          'params' => 'query=$1',
        ),
      );
    }
    
  	function rewrite($parsed_link, $language_code) {
      
      $parsed_link['path'] = 'search/'. (isset($parsed_link['query']['query']) ? urlencode($parsed_link['query']['query']) : '');
      
      unset($parsed_link['query']['search']);
      unset($parsed_link['query']['query']);
      
      return $parsed_link;
    }
  }
  
?>
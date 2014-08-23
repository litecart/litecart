<?php
  
  class url_search {

    function routes() {
      return array(
        array(
          'pattern' => '#^search/(.*)?$#',
          'script' => FS_DIR_HTTP_ROOT . WS_DIR_PAGES .'search.inc.php',
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
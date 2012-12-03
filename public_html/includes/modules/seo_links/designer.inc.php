<?php
  
  class seo_link_designer {
    
    public $config = array(
      'doc' => 'designer.php',
      'params' => array('designer_id'),
      'seo_path' => '%title-d-%designer_id',
    );
    
  	function __construct($system) {
      $this->system = $system;
    }
    
    function title($parsed_link, $language_code) {
      
      if (!isset($parsed_link['query']['designer_id'])) return '';
      
      $designer_query = $this->system->database->query(
        "select d.name, di.short_description from ". DB_TABLE_DESIGNERS ." d
        left join ". DB_TABLE_DESIGNERS_INFO ." di on (d.id = d.id and di.language_code = '". $this->system->database->input($language_code) ."')
        where d.id = '". (int)$parsed_link['query']['designer_id'] ."'
        limit 1;"
      );
      $designer = $this->system->database->fetch($designer_query);
      if (empty($designer)) return '';
      
      $title = !empty($designer['short_description']) ? $designer['short_description'] : $designer['name'];
      
      return $this->system->seo_links->url_friendly_string($title);
    }
  }
  
?>
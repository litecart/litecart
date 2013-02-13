<?php
  
  class seo_link_manufacturers {
    
    public $config = array(
      'doc' => 'manufacturers.php',
      'params' => array(),
      'seo_path' => '',
    );
    
  	function __construct($system) {
      $this->system = $system;
      $this->config['seo_path'] = $this->system->seo_links->url_friendly_string($this->system->language->translate('manufacturers.php:url_manufacturers', 'Manufacturers'));
    }
    
    function title($parsed_link, $language_code) {
      $title = $this->system->language->translate('manufacturers.php:url_manufacturers', 'Manufacturers');
      return $this->system->seo_links->url_friendly_string($title);
    }
  }
  
?>
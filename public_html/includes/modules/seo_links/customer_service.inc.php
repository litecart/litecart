<?php
  
  class seo_link_customer_service {
    
    public $config = array(
      'doc' => 'customer_service.php',
      'params' => array(),
      'seo_path' => '',
    );
    
  	function __construct($system) {
      $this->system = $system;
      $this->config['seo_path'] = $this->system->seo_links->url_friendly_string($this->system->language->translate('title_customer_service', 'Customer Service'));
    }
    
    function title($parsed_link, $language_code) {
      $title = $this->system->language->translate('title_customer_service', 'Customer Service');
      return $this->system->seo_links->url_friendly_string($title);
    }
  }
  
?>
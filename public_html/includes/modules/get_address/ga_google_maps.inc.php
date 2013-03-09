<?php

  class ga_google_maps {
    private $system;
    public $id = __CLASS__;
    public $name = 'Google Maps';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    
    public function __construct() {
      global $system;
      $this->system = &$system;
    }
    
    public function query($data) {
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (!in_array($data['trigger'], array('company', 'address1', 'postcode', 'city'))) return;
      
      $address = array(
        !empty($data['company']) ? $data['company'] : false,
        !empty($data['address1']) ? $data['address1'] : false,
        !empty($data['postcode']) ? $data['postcode'] : false,
        !empty($data['city']) ? $data['city'] : false,
        !empty($data['country_code']) ? $data['country_code'] : false,
      );
      
      $params = array(
        'address' => implode(', ', $address),
        'sensor' => 'false',
      );
      
      $response = $this->system->functions->http_request('http://maps.googleapis.com/maps/api/geocode/xml?'. http_build_query($params));
      
      
      if (empty($response)) return;
      $response = simplexml_load_string($response);
      
      if (empty($response->status) || (string)$response->status != 'OK') return;
      
      if (count($response->result) > 1) return;
      
      $output = array();
      foreach ($response->result->address_component as $row) {
        switch($row->type) {
          case 'route':
            $output['address1'] = (string)$row->long_name .' '. (isset($output['address1']) ? $output['address1'] : false);
            break;
          case 'street_number':
            $output['address1'] = (isset($output['address1']) ? $output['address1'] : false) .' '. (string)$row->long_name;
            break;
          case 'postal_code':
            $output['postcode'] = (string)$row->long_name;
            break;
          case 'locality':
          case 'postal_town':
            $output['city'] = (string)$row->long_name;
            break;
          case 'country':
            $output['country_code'] = (string)$row->short_name;
            break;
        }
      }
      
      if (!empty($output['address1'])) $output['address1'] = str_replace('  ', ' ', $output['address1']);
      
      if (strtolower($this->system->language->selected['charset']) != 'utf-8') {
        $output = array_walk($output, 'utf8_decode');
      }
      
      return $output;
    }
    
    public function before_process() {}
    
    public function after_process() {}
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>
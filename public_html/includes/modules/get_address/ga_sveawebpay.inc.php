<?php

  class ga_sveawebpay {
    private $system;
    public $id = __CLASS__;
    public $name = 'SveaWebPay Get Address';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
    public function query($data) {
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (empty($data['trigger']) || $data['trigger'] != 'tax_id') return;
      
      if (empty($data['tax_id'])) return;
      
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'sveawebpay/Includes.php');
      
      $swp = WebPay::getAddresses();
      
      if (empty($data['company'])) {
        $swp->setIndividual($data['tax_id']);
      } else {
        $swp->setCompany($data['tax_id']);
      }
      
      if ($this->settings['gateway'] != 'Live') {
        $swp->setTestmode()
          ->setIndividual(194605092222);
      }
      
      $response = $swp->setOrderTypeInvoice()
                    ->setCountryCode("SE")
                    ->doRequest();
                    
      
      if (empty($response->customerIdentity)) {
        return array('error' => $this->system->language->translate(__CLASS__.':error_no_response_from_server', 'No response from server'));
      }
      
      $response = array_shift(array_values($response->customerIdentity));
      
      list($lastname, $firstname) = explode(', ', $response->fullName);
      
      $info = array(
        'company' => '',
        //'firstname' => $info->LegalName,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'address1' => $response->street,
        'address2' => $response->coAddress,
        'postcode' => $response->zipCode,
        'city' => $response->locality,
        'country_code' => '',
        'zone_code' => '',
      );
      
      if (strtolower($this->system->language->selected['charset']) != 'utf-8') {
        $info = array_walk($info, 'utf8_encode');
      }
      
      return $info;
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
          'key' => 'gateway',
          'default_value' => 'Test',
          'title' => $this->system->language->translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => $this->system->language->translate(__CLASS__.':description_gateway', 'Select your gateway.'),
          'function' => 'radio(\'Test\',\'Live\')',
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
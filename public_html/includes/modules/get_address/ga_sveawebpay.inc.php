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
      
    // Call Soap and set up data
      $client = new SoapClient($this->settings['gateway'] ? 'https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL' : 'https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL');
      
    // Handle response
      $response = $client->GetAddresses(array(
        'request' => array(
          'Auth' => array(
            'ClientNumber' => $this->settings['client_no'],
            'Username' => $this->settings['username'],
            'Password' => $this->settings['password'],
           ),
          'IsCompany' => empty($data['company']) ? 0 : 1,
          'CountryCode' => isset($data['country_code']) ? $data['country_code'] : '',
          'SecurityNumber' => isset($data['tax_id']) ? $data['tax_id'] : '',
        )
      ));
      
      if (empty($response->GetAddressesResult)) {
        return array('error' => $this->system->language->translate(__CLASS__.':error_no_response_from_server', 'No response from server'));
      }
      
      if (!empty($response->GetAddressesResult->ErrorMessage)) {
        return array('error' => $response->GetAddressesResult->ErrorMessage);
      }
      
      $info = array_shift(array_values($response->GetAddressesResult->Addresses->CustomerAddress));
      
      $info = array(
        'company' => '',
        //'firstname' => $info->LegalName,
        'firstname' => $info->FirstName,
        'lastname' => $info->LastName,
        'address1' => $info->AddressLine1,
        'address2' => $info->AddressLine2,
        'postcode' => $info->Postcode,
        'city' => $info->Postarea,
        'country_code' => 'SE',
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
          'key' => 'client_no',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_client_no', 'Client No'),
          'description' => $this->system->language->translate(__CLASS__.':description_client_no', 'Your client no provided by SveaWebPay.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'username',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_username', 'Username'),
          'description' => $this->system->language->translate(__CLASS__.':description_username', 'Your API username provided by SveaWebPay.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'password',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_password', 'Password'),
          'description' => $this->system->language->translate(__CLASS__.':description_username', 'Your API password provided by SveaWebPay.'),
          'function' => 'password()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Test',
          'title' => $this->system->language->translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => $this->system->language->translate(__CLASS__.':description_gateway', 'Select your gateway.'),
          'function' => 'radio(\'Production\',\'Test\')',
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
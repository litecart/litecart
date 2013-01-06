<?php

  class pm_payone {
    private $system;
    public $id = __CLASS__;
    public $name = 'Payone';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'http://www.payone.de';
    public $website = 'http://www.payone.de';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
  /*
   * Return selectable payment options for checkout
   */
    public function options() {
      
    // If not enabled
      if ($this->settings['status'] != 'Enabled') return;
      
    // If not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $destination['country_code'], $destination['zone_code'])) return;
      }
      
      if (empty($this->settings['merchant_id'])) return;
      if (empty($this->settings['merchant_key'])) return;
      
      $method = array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'card',
            'icon' => $this->settings['icon'],
            'name' => $this->system->language->translate(__CLASS__.':title_card_payment', 'Card Payment'),
            'description' => $this->system->language->translate(__CLASS__.':description', ''),
            'fields' => '',
            'cost' => 0,
            'tax_class_id' => 0,
            'confirm' => $this->system->language->translate(__CLASS__.':title_pay_now', 'Pay Now'),
          ),
        )
      );
      return $method;
    }
    
    public function pre_check() {
    }
    
    public function transfer() {
      global $order;
      
      list($module_id, $option_id) = explode(':', $order->data['payment_option']['id']);
      
      switch($option_id) {
        case 'card':
          $clearingtype = 'cc';
          $request = 'creditcardcheck';
          break;
        case 'invoice':
          $clearingtype = 'rec';
          $request = 'bankaccountcheck';
          break;
        default:
          die('Unknown payment option '. $option_id);
      }
      
      $fields = array(
      // Front-end specific data
        'mid' => $this->settings['merchant_id'],
        'aid' => $this->settings['aid'],
        'portalid' => $this->settings['portal_id'],
        'mode' => ($this->settings['gateway'] == 'Live') ? 'live' : 'test',
        'response_type' => 'REDIRECT',
        'reference' => $order->data['uid'],
        'successurl' => $this->system->document->link(WS_DIR_HTTP_HOME . 'order_process.php'),
        'errorurl' => $this->system->document->link(WS_DIR_HTTP_HOME . 'order_process.php'),
        'encoding' => $this->system->language->selected['charset'],
        //'settleaccount' => '',
        
      // Order data
        'request' => 'creditcardcheck',
        'amount' => $order->data['payment_due'],
        'currency' => $order->data['currency_code'],
        'clearingtype' => $clearingtype,
        'request' => $request,

      // Personal data
        'customerid' => $order->data['customer']['id'],
        'firstname' => $order->data['customer']['firstname'],
        'lastname' => $order->data['customer']['lastname'],
        'company' => $order->data['customer']['company'],
        'street' => $order->data['customer']['address1'],
        'addressaddition' => $order->data['customer']['address2'],
        'zip' => $order->data['customer']['postcode'],
        'city' => $order->data['customer']['city'],
        'country' => $order->data['customer']['country_code'],
        'email' => $order->data['customer']['email'],
        'telephonenumber' => $order->data['customer']['phone'],
        'language' => $order->data['language_code'],
        'vatid' => $order->data['customer']['tax_id'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        
      // Shipping data
        'firstname' => $order->data['customer']['shipping_address']['firstname'],
        'lastname' => $order->data['customer']['shipping_address']['lastname'],
        'company' => $order->data['customer']['shipping_address']['company'],
        'street' => $order->data['customer']['shipping_address']['address1'],
        'zip' => $order->data['customer']['shipping_address']['postcode'],
        'city' => $order->data['customer']['shipping_address']['city'],
        'country' => $order->data['customer']['shipping_address']['country_code'],
      );
      
      $item_no = 1;
      
      foreach ($order->data['items'] as $item) {
        $fields['id['.$item_no.']'] = $item['product_id'] . (!empty($item['option_id']) ? ':'.$item['product_id'] : ''); // item no
        $fields['no['.$item_no.']'] = $item['quantity']; // quantity
        $fields['pr['.$item_no.']'] = number_format($this->system->currency->calculate($item['price'], $order->data['currency_code']), 2, '', ''); // price in cents
        $fields['de['.$item_no.']'] = $item['name']; // item description
        $fields['va['.$item_no.']'] = round($item['tax'] / $item['price']); // vat percentage
        $item_no++;
      }
      
      foreach ($order->data['order_total'] as $row) {
        if (!empty($row['calculate'])) {
          $fields['id['.$item_no.']'] = $row['code']; // item no
          $fields['no['.$item_no.']'] = 1; // quantity
          $fields['pr['.$item_no.']'] = number_format($this->system->currency->calculate($row['value'], $order->data['currency_code']), 2, '', ''); // price in cents
          $fields['de['.$item_no.']'] = $row['title']; // item description
          $fields['va['.$item_no.']'] = round($row['tax'] / $row['value']); // vat percentage
          $item_no++;
        }
      }
      
      
      $hash_params = array(
        'mid', 'amount', 'productid', 'aid', 'currency', 'accessname',
        'portalid', 'due_time', 'accesscode', 'mode', 'storecarddata',
        'access_expiretime', 'request', 'checktype', 'access_canceltime',
        'responsetype', 'addresschecktype', 'access_starttime', 'reference',
        'consumerscoretype', 'access_period', 'userid', 'invoiceid',
        'access_aboperiod', 'customerid', 'invoiceappendix', 'access_price',
        'param', 'invoice_deliverymode', 'access_aboprice', 'narrative_text',
        'eci', 'access_vat', 'successurl', 'id', 'settleperiod', 'errorurl',
        'pr', 'settletime', 'backurl', 'no', 'vaccountname', 'exiturl',
        'de', 'vreference', 'clearingtype', 'ti', 'document_date', 'encoding',
        'va', 'booking_date', 'settleaccount'
      );
      
      $hash_string = '';
      foreach ($hash_params as $key) {
        if (!isset($fields['key'])) continue;
        $hash_string .= is_array($fields[$key]) ? implode('', $fields[$key]) : $fields[$key];
      }
      
      $fields['hash'] = hash('md5', $hash_string . $this->settings['merchant_key']);
      
      return array(
        'action' => 'https://secure.pay1.de/frontend/',
        'method' => 'post',
        'fields' => $fields,
      );
    }
    
    public function verify() {
      global $order;
      
      
      return array(
        'order_status_id' => '',
        'transaction_id' => '',
      );
    }
    
    public function after_process() {
    }
    
    public function callback() {
    }
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_status', 'Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_status', ''),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'icon',
          'default_value' => 'images/payment/payone.png',
          'title' => $this->system->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $this->system->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_id', 'Merchant ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_email', 'Your merchant id provided by Payone.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by Payone.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'portal_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_portal_id', 'Portal ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_portal_id', 'Your portal ID provided by Payone.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'aid',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_aid', 'AID'),
          'description' => $this->system->language->translate(__CLASS__.':description_aid', 'Your AID provided by Payone.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Test',
          'title' => $this->system->language->translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => $this->system->language->translate(__CLASS__.':description_gateway', 'Select your Paypal payment gateway.'),
          'function' => 'radio(\'Live\',\'Test\')',
        ),
        array(
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_order_status', 'Order Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_order_status', 'Give orders made with this payment module the following order status.'),
          'function' => 'order_status()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => $this->system->language->translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_priority', 'Priority'),
          'description' => $this->system->language->translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
    
?>
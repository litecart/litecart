<?php

  class pm_payone {
    private $system;
    public $id = __CLASS__;
    public $name = 'Payone';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'https://pmi.pay1.de/';
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
          $request = 'authorization';
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
        'aid' => $this->settings['aid'],
        'portalid' => $this->settings['portal_id'],
        'mode' => ($this->settings['gateway'] == 'Live') ? 'live' : 'test',
        'request' => $request,
        'encoding' => $this->system->language->selected['charset'],
        'clearingtype' => $clearingtype,
        'reference' => $order->data['uid'],
        'display_name' => 'no',
        'targetwindow' => 'top',
        'successurl' => $this->system->document->link(WS_DIR_HTTP_HOME . 'order_process.php'),
        'backurl' => $this->system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'),
        
      // Order data
        'amount' => number_format($this->system->currency->calculate($order->data['payment_due'], 'EUR'), 2, '', ''),
        'currency' => 'EUR',
        
      // Personal data
        'customerid' => $order->data['customer']['id'],
        'firstname' => $order->data['customer']['firstname'],
        'lastname' => $order->data['customer']['lastname'],
        'company' => $order->data['customer']['company'],
        'street' => $order->data['customer']['address1'],
        //'addressaddition' => $order->data['customer']['address2'],
        'zip' => $order->data['customer']['postcode'],
        'city' => $order->data['customer']['city'],
        'country' => $order->data['customer']['country_code'],
        'email' => $order->data['customer']['email'],
        'telephonenumber' => $order->data['customer']['phone'],
        'language' => $order->data['language_code'],
        //'vatid' => $order->data['customer']['tax_id'],
        //'ip' => $_SERVER['REMOTE_ADDR'],
        
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
        $fields['pr['.$item_no.']'] = number_format($this->system->currency->calculate($item['price'] + $item['tax'], 'EUR'), 2, '', ''); // price in cents
        $fields['de['.$item_no.']'] = $item['name']; // item description
        $fields['va['.$item_no.']'] = round($item['tax'] / $item['price'] * 100); // vat percentage
        $item_no++;
      }
      
      foreach ($order->data['order_total'] as $row) {
        if (!empty($row['calculate'])) {
          $fields['id['.$item_no.']'] = $row['id']; // item no
          $fields['no['.$item_no.']'] = 1; // quantity
          $fields['pr['.$item_no.']'] = number_format($this->system->currency->calculate($row['value'] + $row['tax'], 'EUR'), 2, '', ''); // price in cents
          $fields['de['.$item_no.']'] = $row['title']; // item description
          $fields['va['.$item_no.']'] = round($row['tax'] / $row['value'] * 100); // vat percentage
          $item_no++;
        }
      }
      
      $hash_keys = array(
        'aid', 'portalid', 'mode', 'request', 'encoding', 'clearingtype', 'reference',
        'customerid', 'invoiceid', 'param', 'narrative_text', 'display_name', 'display_address',
        'autosubmit', 'successurl', 'backurl', 'targetwindow', 'amount', 'currency',
      );
      
      $i = 1;
      while ($i < $item_no) {
        $hash_keys = array_merge($hash_keys, array('id['.$i.']', 'pr['.$i.']', 'no['.$i.']', 'de['.$i.']', 'va['.$i.']'));
        $i++;
      }
      
      sort($hash_keys);
      
      $hash_string = '';
      foreach ($hash_keys as $key) {
        if (!isset($fields[$key])) continue;
        $hash_string .= $fields[$key];
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
      
      $attempts = 0;
      while(empty($payone) && $attempts < 20) {
        if (!empty($attempts)) sleep(1);
        $payone_query = $this->system->database->query(
          "select * from ". DB_TABLE_PREFIX ."payone
          where order_uid = '". $this->system->database->input($order->data['uid']) ."'
          order by date_created
          limit 1;"
        );
        $payone = $this->system->database->fetch($payone_query);
        $attempts++;
      }
      
      if (empty($payone)) return array('error' => 'Missing transaction status');
      
      $result = unserialize($payone['parameters']);
      
      if (!empty($result['failedcause'])) return array('error' => $result['failedcause']);
      if (!in_array($result['txaction'], array('appointed'))) return array('error' => 'Checksum error');
      if ($result['key'] != md5($this->settings['merchant_key'])) return array('error' => 'Checksum error');
      if ($result['price'] != number_format($this->system->currency->calculate($order->data['payment_due'], 'EUR'), 2, '.', '')) return array('error' => 'The paid amount did not match the order amount.');
      if ($result['currency'] != 'EUR') return array('error' => 'Invalid currency');
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'transaction_id' => $result['txid'],
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
          'description' => $this->system->language->translate(__CLASS__.':description_status', 'Enables or disables the module.'),
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
          'key' => 'merchant_key',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by Payone.'),
          'function' => 'password()',
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
    
    public function install() {
      $this->system->database->query(
        "CREATE TABLE IF NOT EXISTS `". DB_TABLE_PREFIX ."_payone` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_uid` varchar(13) NOT NULL,
          `txid` varchar(32) NOT NULL,
          `parameters` varchar(4096) NOT NULL,
          `ip` varchar(15) NOT NULL,
          `date_created` datetime NOT NULL,
          PRIMARY KEY (`id`)
        );"
      );
    }
    
    public function uninstall() {}
  }
    
?>
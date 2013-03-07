<?php

  class pm_dibs_pw {
    private $system;
    public $id = __CLASS__;
    public $name = 'DIBS';
    public $description = 'Payment Window';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'https://www.dibs.se';
    public $website = 'https://www.dibs.se';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
      
      if (empty($this->system->session->data['dibs'])) $this->system->session->data['dibs'] = array();
      $this->cache = &$this->system->session->data['dibs'];
    }
    
    public function enabled() {
      
    }
    
    public function options() {
      
      $options = array();
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $this->system->customer->data['country_code'], $this->system->customer->data['zone_code'])) return;
      }
      
      if (empty($this->settings['merchant_id'])) return;
      
      return array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'payment_window',
            'icon' => 'images/payment/dibs.png',
            'name' => $this->system->language->translate(__CLASS__.':title_option_payment_window', 'Payment Window'),
            'description' => $this->system->language->translate(__CLASS__.':description_option_payment_window', 'Safe and secure card money transactions by DIBS.'),
            'fields' => '',
            'cost' => 0,
            'tax_class_id' => 0,
            'confirm' => $this->system->language->translate(__CLASS__.':title_confirm_order', 'Next'),
          ),
        )
      );
    }
    
    public function pre_check() {
    }
    
    public function transfer() {
      global $order;

    // Set post fiels
      $fields = array(
        'merchant' => $this->settings['merchant_id'],
        'amount' => number_format($order->data['payment_due'] * $order->data['currency_value'], 2, '', ''),
        'orderId' => $order->data['uid'],
        'currency' => $order->data['currency_code'],
        'acceptReturnUrl' => $this->system->document->link('order_process.php'),
        'cancelReturnUrl' => $this->system->document->link('checkout.php'),
        'language' => $order->data['language_code'],
        //'addFee' => '1',
        'payType' => $this->settings['payment_types'],
        'captureNow' => '0',

      );
      
      if ($this->settings['gateway'] != 'Live') {
        $fields = array_merge($fields, array(
          'test' => '1',
        ));
      }
      
      $fields = array_merge($fields, array(
        'billingFirstName' => $order->data['customer']['firstname'],
        'billingLastName' => $order->data['customer']['lastname'],
        'billingAddress' => $order->data['customer']['address1'],
        'billingAddress2' => $order->data['customer']['address2'],
        'billingPostalCode' => $order->data['customer']['postcode'],
        'billingPostalPlace' => $order->data['customer']['city'],
        'billingMobile' => $order->data['customer']['mobile'],
        'billingEmail' => $order->data['customer']['email'],
        'shippingFirstName' => $order->data['customer']['shipping_address']['firstname'],
        'shippingLastName' => $order->data['customer']['shipping_address']['lastname'],
        'shippingAddress' => $order->data['customer']['shipping_address']['address1'],
        'shippingAddress2' => $order->data['customer']['shipping_address']['address2'],
        'shippingPostalCode' => $order->data['customer']['shipping_address']['postcode'],
        'shippingPostalPlace' => $order->data['customer']['shipping_address']['city'],
      ));
      
      $fields = array_merge($fields, array(
        'oiTypes' => 'QUANTITY;UNITCODE;DESCRIPTION;AMOUNT;ITEMID;VATAMOUNT',
        'oiNames' => 'Items;UnitCode;Description;Amount;ItemId;VatAmount',
      ));
      $i = 1;
      
      foreach (array_keys($order->data['items']) as $key) {
        $fields = array_merge($fields, array(
          'oiRow'.($i) => implode(';', array(
            $order->data['items'][$key]['quantity'],
            '',
            $order->data['items'][$key]['name'],
            number_format($order->data['items'][$key]['price'] * $order->data['currency_value'], 2, '', ''),
            $order->data['items'][$key]['sku'] ? $order->data['items'][$key]['sku'] : $order->data['items'][$key]['code'],
            number_format($order->data['items'][$key]['tax'] * $order->data['currency_value'], 2, '', ''),
          )),
        ));
        $i++;
      }
      
      foreach (array_keys($order->data['order_total']) as $key) {
        if (empty($order->data['order_total'][$key]['calculate'])) continue;
        $fields = array_merge($fields, array(
          'oiRow'.($i) => implode(';', array(
            '1',
            '',
            $order->data['order_total'][$key]['title'],
            number_format($order->data['order_total'][$key]['value'] * $order->data['currency_value'], 2, '', ''),
            '-',
            number_format($order->data['order_total'][$key]['tax'] * $order->data['currency_value'], 2, '', ''),
          )),
        ));
        $i++;
      }
      
      if (strtolower($this->system->language->selected['charset']) != 'utf-8') {
        foreach (array_keys($fields) as $key) {
          $fields[$key] = utf8_encode($fields[$key]);
        }
      }
      
      $fields = array_merge($fields, array(
        'MAC' => $this->dibs_hash($fields, $this->settings['merchant_key']),
      ));
      
      return array(
        'action' => 'https://sat1.dibspayment.com/dibspaymentwindow/entrypoint',
        'method' => 'post',
        'fields' => $fields,
      );
    }
    
    public function verify() {
      global $order;
      
      if (empty($_POST)) {
        return array('error' => 'No payment verification data');
      }
      
      $checksum = $this->dibs_hash($_POST, $this->settings['merchant_key']);
      
      if ($_POST['MAC'] != $checksum) {
        return array('error' => 'Could not verify payment');
      }
      
      if ($_POST['orderId'] != $order->data['uid']) {
        return array('error' => 'Order ID missmatch in payment verification');
      }
      
      if ($_POST['amount'] != number_format($order->data['payment_due'] * $order->data['currency_value'], 2, '', '')) {
        return array('error' => 'Payment amount does not match order amount');
      }
      
      if (!empty($_POST['fee'])) $_POST['amount'] += $_POST['fee'];
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => $_POST['transaction'],
        'comments' => 'DIBS Authorized ' . date("Y-m-d H:i:s") . PHP_EOL,
      );
    }
    
    public function after_process() {
    }
    
    public function callback() {
    }
    
    function dibs_hash($fields, $hex) {
      
      $mac = '';
      
      $hex = str_replace(array("\r", "\n"), array('', ''), $hex);
      
      for ($i=0; $i < strlen($hex)-1; $i+=2) {
        $mac .= chr(hexdec($hex[$i].$hex[$i+1]));
      }
      
      if (strtolower($this->system->language->selected['charset']) != 'utf-8') {
        $mac = utf8_encode($mac);
      }
      
      ksort($fields);
      
      $hash_fields = array();
      foreach ($fields as $key => $value) {
        if ($key == 'MAC') continue;
        $hash_fields[] = $key.'='.$value;
      }
      
      return hash_hmac('sha256', implode('&', $hash_fields), $mac);
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
          'key' => 'merchant_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_id', 'Merchant ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_id', 'Your merchant ID provided by DIBS.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by DIBS.'),
          'function' => 'smalltext()',
        ),
        array(
          'key' => 'payment_types',
          'default_value' => 'ALL_CARDS,ALL_NETBANKS,ALL_INVOICES',
          'title' => $this->system->language->translate(__CLASS__.':title_payment_types', 'Payment Types'),
          'description' => $this->system->language->translate(__CLASS__.':description_payment_types', 'A coma separated list of payment types to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Live',
          'title' => $this->system->language->translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => $this->system->language->translate(__CLASS__.':description_gateway', 'Select your payment gateway.'),
          'function' => 'radio("Test","Live")',
        ),
        array(
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_order_status', 'Order Status') .': '. $this->system->language->translate(__CLASS__.':title_complete', 'Complete'),
          'description' => $this->system->language->translate(__CLASS__.':description_order_status', 'Give successful orders made with this payment module the following order status.'),
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
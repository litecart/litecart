<?php

  class pm_certitrade {
    private $system;
    public $id = __CLASS__;
    public $name = 'Certitrade';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'https://www.certitrade.se';
    public $website = 'https://www.certitrade.se';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
    public function options() {
      
    // If not enabled
      if ($this->settings['status'] != 'Enabled') return;
      
    // If not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $destination['country_code'], $destination['zone_code'])) return;
      }
      
    // If not configured
      if (empty($this->settings['merchant_id'])) return;
      if (empty($this->settings['merchant_key'])) return;
      
    // If currency is not supported
      if ($this->get_currency_code($this->system->currency->selected['code']) == '') return;
      
    // If language is not supported
      $supported_languages = array(
        'da', 'de', 'en', 'fi', 'fr',
        'no', 'it', 'es', 'sv',
      );
      if (!in_array($this->system->language->selected['code'], $supported_languages)) return;
      
      $method = array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'card',
            'icon' => $this->settings['icon'],
            'name' => $this->system->language->translate(__CLASS__.':title_card_payment', 'Card Payment'),
            'description' => $this->system->language->translate(__CLASS__.':description', 'Secure and simple money transactions made by Certitrade.'),
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
      
    // Create order (required for the early callback)
      $order->save();
        
    // Set post fields
      $fields = array(
        'merchantid' => $this->settings['merchant_id'],
        'rev' => 'E',
        'orderid' => $order->data['uid'],
        'payment_method' => 'card',
        'amount' => $order->data['payment_due'] * $order->data['currency_value'],
        'currency' => $this->get_currency_code($order->data['currency_code']),
        'retururl' => $this->system->document->link('order_process.php'),
        'approveurl' => $this->system->document->link('callback.php', array('order_uid' => $order->data['uid'])), // We're not using callbacks
        'declineurl' => $this->system->document->link('checkout.php'),
        'cancelurl' => $this->system->document->link('checkout.php'),
        'returwindow' => '',
        'lang' => $order->data['language_code'],
        'cust_id' => $order->data['customer']['id'],
        'cust_name' => $order->data['customer']['company'] ? $order->data['customer']['company'] : $order->data['customer']['firstname'] .' '. $order->data['customer']['lastname'],
        'cust_address1' => $order->data['customer']['address1'],
        'cust_address2' => $order->data['customer']['address2'],
        'cust_address3' => '',
        'cust_zip' => $order->data['customer']['postcode'],
        'cust_city' => $order->data['customer']['city'],
        'cust_phone' => $order->data['customer']['phone'],
        'cust_email' => $order->data['customer']['email'],
        'cust_country' => $order->data['customer']['country_code'],
        'connection' => '',
        'acquirer' => '',
        'debug' => ($this->settings['gateway'] == 'Live') ? '0' : '1',
        'httpdebug' => ($this->settings['gateway'] == 'Live') ? '0' : '1',
        'timeout' => (ini_get('session.gc_maxlifetime')/60)-1,
        'delayed_capture' => '0',
        'ctcharset' => strtoupper($this->system->language->selected['charset']),
        'returmetod' => 'combined_redirect',
      );
      
    // Calculate checksum
      $fields['md5code'] = hash('md5',
        $this->settings['merchant_key'] .
        $fields['merchantid'] .
        $fields['rev'] .
        $fields['orderid'] .
        $fields['amount'] .
        $fields['currency'] .
        $fields['retururl'] .
        $fields['approveurl'] .
        $fields['declineurl'] .
        $fields['cancelurl'] .
        $fields['returwindow'] .
        $fields['lang'] .
        $fields['cust_id'] .
        $fields['cust_name'] .
        $fields['cust_address1'] .
        $fields['cust_address2'] .
        $fields['cust_address3'] .
        $fields['cust_zip'] .
        $fields['cust_city'] .
        $fields['cust_phone'] .
        $fields['cust_email'] .
        $fields['connection'] .
        $fields['acquirer']
        //$fields['debug'] .
        //$fields['httpdebug']
      );
      
      if ($this->settings['gateway'] == 'Live') {
        $gateway_url = 'https://payment.certitrade.net/webshophtml/e/auth.php';
      } else {
        $gateway_url = 'https://www.certitrade.net/webshophtml/e/auth.php';
      }
      
      return array(
        'action' => $gateway_url,
        'method' => 'post',
        'fields' => $fields,
      );
    }
    
    public function callback() {
      global $order;
      
    // Verify currency
      if (empty($_POST['currency']) || $_POST['currency'] != $this->get_currency_code($order->data['currency_code'])) {
        return array('error' => 'Callback failure: Missing HTTP POST data');
      }
      
      if (isset($_POST['result']) && $_POST['result'] != 'OK') {
        switch ($_POST['result_code']) {
          case '00':
            break;
          case '01':
            return array('error' => 'Payment failure: Denied by bank');
          case '02':
            return array('error' => 'Payment failure: Bank connection failed');
          case '03':
            return array('error' => 'Payment failure: Technical error');
          case '04':
            header('Location: '. $this->system->document->link('checkout.php'));
            exit;
          default:
            return array('error' => 'Payment failure: Unknown error');
        }
      }
      
    // Verify currency
      if (empty($_POST['currency']) || $_POST['currency'] != $this->get_currency_code($order->data['currency_code'])) {
        return array('error' => 'Payment failure: Payment currency did not match order currency');
      }
      
    // Verify amount
      if (empty($_POST['amount']) || round($_POST['amount']) != round($order->data['payment_due'] * $order->data['currency_value'])) {
        return array('error' => 'Payment failure: Payment amount did not match order amount' . round($_POST['amount']) .' '. round($order->data['payment_due'] * $order->data['currency_value']));
      }
      
    // Verify transaction number
      if (empty($_POST['trnumber'])) {
        return array('error' => 'Payment failure: Missing transaction number');
      }
      
    // Verify authcode
      if (empty($_POST['authcode']) || $_POST['authcode'] == '') {
        return array('error' => 'Payment failure: Missing authorization code');
      }
      
    // Verify checksum number
      if (empty($_POST['md5code'])) {
        return array('error' => 'Payment failure: Missing checksum');
      }
      
    // Verify checksum
      $checksum = hash('md5',
        $this->settings['merchant_key'] . 
        $_POST['merchantid'] . 
        $_POST['orderid'] .
        $_POST['amount'] .
        $_POST['currency'] .
        $_POST['result'] . 
        $_POST['result_code'] .
        $_POST['bank_code'] . 
        $_POST['trnumber'] . 
        $_POST['authcode'] .
        $_POST['lang'] .
        $_POST['ch_name'] .
        $_POST['ch_address1'] .
        $_POST['ch_address2'] .
        $_POST['ch_address3'] .
        $_POST['ch_zip'] .
        $_POST['ch_city'] .
        $_POST['ch_phone'] .
        $_POST['ch_email']
      );
      if ($_POST['md5code'] != $checksum) {
        return array('error' => 'Payment failure: Invalid checksum');
      }
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => $_POST['authcode'] .'-'. $_POST['trnumber'],
        'comments' => ''
      );
    }
    
    public function verify() {
      global $order;
      
      if (empty($_GET)) {
        header('Location: '. $this->system->document->link('checkout.php'));
        exit;
      }
      
      //if ($order->data['order_status_id'] != $this->settings['order_status_id']) {
      //  return array('error' => 'Payment verification failure: Unknown error, please contact customer service');
      //}
      
      if (empty($_GET['md5code'])) {
        return array('error' => 'Payment verification failure: Missing transaction checksum');
      }
      
      if (empty($_GET['trnumber'])) {
        return array('error' => 'Payment verification failure: Missing transaction number');
      }
    }
    
    public function after_process() {
    }
    
  // Get ISO-4217 code for currency
    function get_currency_code($currency) {
      switch($currency) {
        case 'AUD':
          return '036';
        case 'CAD':
          return '124';
        case 'CHF':
          return '756';
        case 'DKK':
          return '208';
        case 'GBP':
          return '826';
        case 'ISK':
          return '352';
        case 'JPY':
          return '392';
        case 'NOK':
          return '578';
        case 'NZD':
          return '554';
        case 'SEK':
          return '752';
        case 'TRY':
          return '949';
        case 'USD':
          return '840';
        default:
          return false;
      }
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
          'default_value' => 'images/payment/certitrade.png',
          'title' => $this->system->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $this->system->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_id',
          'default_value' => '12345',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_id', 'Merchant ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_id', 'Your merchant id provided by Certitrade.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key',
          'default_value' => 'AAAABBBBCCCCDDDDEEEEFFFFGGGGHHHH',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by Certitrade.'),
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
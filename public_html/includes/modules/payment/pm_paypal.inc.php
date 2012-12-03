<?php

  class pm_paypal {
    private $system;
    public $id = __CLASS__;
    public $name = 'Paypal';
    public $description = '';
    public $author = 'ACME Corporation';
    public $version = '1.0';
    public $support_link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_help';
    public $website = 'https://www.paypal.com/cgi-bin/webscr?cmd=_help';
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
      
      if (empty($this->settings['merchant_email'])) return;
      
      $method = array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'card',
            'icon' => $this->settings['icon'],
            'name' => $this->system->language->translate(__CLASS__.':title_card_payment', 'Card Payment'),
            'description' => $this->system->language->translate(__CLASS__.':description', 'Secure and simple money transactions made by Paypal.'),
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
      
      if (empty($this->settings['merchant_email'])) return;
      
      $fields = array(
        'cmd'           => '_cart',
        'upload'        => '1',
        'rm'            => '0',
        'business'      => $this->settings['merchant_email'],
        'currency_code' => $order->data['currency_code'],
        'cbt'           => $this->system->language->translate('paypal:title_finalize_order', 'Finalize Order'),
        'return'        => $this->system->document->link('order_process.php'),
        'cancel_return' => $this->system->document->link('checkout.php'),
        //'notify_url'    => $this->system->document->link('callback.php', array('order_uid' => $order->data['uid'])), // We're not using IPN callbacks
        'charset'       => $this->system->language->selected['charset'],
        'custom'        => $order->data['uid'],
      );
      
      $item_no = 1;
      
      foreach ($order->data['items'] as $item) {
        $fields['item_name_'.$item_no] = $item['name'];
        $fields['item_number_'.$item_no] = $item['product_id'] . (!empty($item['option_id']) ? ':'.$item['product_id'] : '');
        $fields['quantity_'.$item_no] = $item['quantity'];
        $fields['amount_'.$item_no] = $this->system->currency->format($item['price'], true, true);
        $fields['tax_'.$item_no] = $this->system->currency->format($item['tax'], true, true);
        $item_no++;
      }
      
      foreach ($order->data['order_total'] as $row) {
        if ($row['calculate']) {
          $fields['item_name_'.$item_no] = $row['title'];
          $fields['amount_'.$item_no] = $row['value'];
          $fields['tax_'.$item_no] = $row['tax'];
          $item_no++;
        }
      }
      
      $fields_string = '';
      foreach ($fields as $key => $value) {
        $fields_string .= $this->system->functions->form_draw_hidden_field($key, $value) . PHP_EOL;
      }
      
      if ($this->settings['gateway'] == 'Production') {
        $gateway_url = 'https://www.paypal.com/cgi-bin/webscr';
      } else {
        $gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }
      
      return array(
        'action' => $gateway_url,
        'method' => 'post',
        'fields' => $fields_string,
      );
    }
    
    public function verify() {
      global $order;
      
      $errors = array();
      
      $post_fields = array(
        'cmd' => '_notify-synch',
        'tx'  => $_GET['tx'],
        'at'  => $this->settings['pdt_auth_token'],
      );
      
      if ($this->settings['gateway'] == 'Production') {
        $gateway_url = 'https://www.paypal.com/cgi-bin/webscr';
      } else {
        $gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }
      
      $result = $this->system->functions->http_request($gateway_url, $post_fields);
      $result = explode("\n", $result);
      
      $txdata = array();
      if (strcmp ($result[0], 'SUCCESS') == 0) {
          for ($i=1; $i<count($result);$i++){
          list($key, $val) = explode('=', $result[$i]);
          $txdata[trim(urldecode($key))] = trim(urldecode($val));
        }
      }
      
      if (empty($txdata)) $errors[] = $this->system->language->translate(__CLASS__.':error_transaction_not_verified', 'Error: Payment transaction could not be verified by Paypal.');
      if ($txdata['payment_status'] != 'Completed') $errors[] = 'Payment status indicates not completed.';
      if ($txdata['mc_gross'] != $order->data['payment_due'] * $order->data['currency_value']) $errors[] = 'Payment amount '. $txdata['mc_gross'] .' is not equal to order amount ('. ($order->data['payment_due'] * $order->data['currency_value']) .').';
      if ($txdata['mc_currency'] != $order->data['currency_code']) $errors[] = 'Payment currency ('. $txdata['mc_currency'] .') should have been '. $order->data['currency_code'];
      if ($txdata['receiver_email'] != $this->settings['merchant_email']) $errors[] = 'Receipient ('. $txdata['receiver_email'] .') should be '. $this->settings['merchant_email'] .'.';
      
      if (!empty($errors)) {
        return array('error' => $errors[0]);
      }
      
      return array(
        'order_status_id' => $this->settings['order_status_id_complete'],
        'transaction_id' => $txdata['txn_id'],
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
          'default_value' => 'images/payment/paypal.png',
          'title' => $this->system->language->translate(__CLASS__.':title_icon', 'Icon'),
          'description' => $this->system->language->translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_email',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_email', 'Merchant E-mail'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_email', 'Your Paypal registered merchant e-mail address.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'pdt_auth_token',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_pdt_auth_token', 'PDT Auth Token'),
          'description' => $this->system->language->translate(__CLASS__.':description_pdt_auth_token', 'Your Paypal PDT authorization token (see your Paypal account).'),
          'function' => 'input()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Sandbox',
          'title' => $this->system->language->translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => $this->system->language->translate(__CLASS__.':description_gateway', 'Select your Paypal payment gateway.'),
          'function' => 'radio(\'Production\',\'Sandbox\')',
        ),
        array(
          'key' => 'order_status_id_complete',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_order_status', 'Order Status') .': '. $this->system->language->translate(__CLASS__.':title_complete', 'Complete'),
          'description' => $this->system->language->translate(__CLASS__.':description_order_status', 'Give successful orders made with this payment module the following order status.'),
          'function' => 'order_status()',
        ),
        array(
          'key' => 'order_status_id_error',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_order_status', 'Order Status') .': '. $this->system->language->translate(__CLASS__.':title_error', 'Error'),
          'description' => $this->system->language->translate(__CLASS__.':description_order_status', 'Give failed orders made with this payment module the following order status.'),
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
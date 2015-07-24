<?php

  class pm_paypal {
    public $id = __CLASS__;
    public $name = 'Paypal Standard';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.2';
    public $website = 'https://www.paypal.com/cgi-bin/webscr?cmd=_help';
    public $priority = 0;
    
    public function options($items, $subtotal, $tax, $currency_code, $customer) {
      
    // If not enabled
      if (empty($this->settings['status'])) return;
      
    // If not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!functions::reference_in_geo_zone($this->settings['geo_zone_id'], $customer['country_code'], $customer['zone_code'])) return;
      }
      
      if (empty($this->settings['merchant_email'])) return;
      
      $method = array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'card',
            'icon' => $this->settings['icon'],
            'name' => language::translate(__CLASS__.':title_card_payment', 'Card Payment'),
            'description' => language::translate(__CLASS__.':description', 'Secure and simple money transactions made by Paypal.'),
            'fields' => '',
            'cost' => 0,
            'tax_class_id' => 0,
            'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
          ),
        )
      );
      return $method;
    }
    
    public function pre_check($order) {
    }
    
    public function transfer($order) {
      
      if (empty($this->settings['merchant_email'])) return;
      
      if (!empty($this->settings['save_order_first'])) {
        $order->save(); // Save order to database
      }
      
      $fields = array(
        'cmd'           => '_cart',
        'bn'            => 'Lite_Cart',
        'upload'        => '1',
        'rm'            => '0',
        'business'      => $this->settings['merchant_email'],
        'currency_code' => !empty($this->settings['use_store_currency']) ? settings::get('store_currency_code') : $order->data['currency_code'],
        'cbt'           => language::translate(__CLASS__.':title_finalize_order', 'Finalize Order'),
        'return'        => document::ilink('order_process'),
        'cancel_return' => document::ilink('checkout'),
        //'notify_url'    => document::ilink('callback', array('order_uid' => $order->data['uid'])), // We're not using IPN callbacks
        'charset'       => language::$selected['charset'],
        'custom'        => $order->data['uid'],
      );
      
      $item_no = 1;
      
    // Detect negative amounts
    // Reason: Paypal don't do discounts if orders specify tax
      $order_contains_discount = false;
      foreach ($order->data['items'] as $item) {
        if ($item['price'] < 0) {
          $order_contains_discount = true;
          break;
        }
      }
      foreach ($order->data['order_total'] as $row) {
        if ($row['calculate'] && $row['value'] < 0) {
          $order_contains_discount = true;
          break;
        }
      }
      
    // Build cart containing discount - no tax specification supported
      if ($order_contains_discount) {
        
        foreach ($order->data['items'] as $item) {
          if ($item['price'] < 0) {
            if (!isset($fields['discount_amount_cart'])) $fields['discount_amount_cart'] = 0;
            $fields['discount_amount_cart'] += $item['quantity'] * $this->_format_raw(-$item['price'], $order->data['currency_code'], $order->data['currency_value']);
            $fields['discount_amount_cart'] += $item['quantity'] * $this->_format_raw(-$item['tax'], $order->data['currency_code'], $order->data['currency_value']);
          } else {
            $fields['item_name_'.$item_no] = $item['name'];
            $fields['item_number_'.$item_no] = $item['product_id'] . (!empty($item['option_id']) ? ':'.$item['product_id'] : '');
            $fields['quantity_'.$item_no] = $item['quantity'];
            $fields['amount_'.$item_no] = $this->_format_raw($item['price'], $order->data['currency_code'], $order->data['currency_value']) + $this->_format_raw($item['tax'], $order->data['currency_code'], $order->data['currency_value']);
            $item_no++;
          }
        }
        
        foreach ($order->data['order_total'] as $row) {
          if ($row['calculate']) {
            if ($row['value'] < 0) {
              if (!isset($fields['discount_amount_cart'])) $fields['discount_amount_cart'] = 0;
              $fields['discount_amount_cart'] += $this->_format_raw(-$row['value'], $order->data['currency_code'], $order->data['currency_value']);
              $fields['discount_amount_cart'] += $this->_format_raw(-$row['tax'], $order->data['currency_code'], $order->data['currency_value']);
            } else {
              $fields['item_name_'.$item_no] = $row['title'];
              $fields['item_number_'.$item_no] = $row['module_id'];
              $fields['quantity_'.$item_no] = '1';
              $fields['amount_'.$item_no] = $this->_format_raw($row['value'], $order->data['currency_code'], $order->data['currency_value']) + $this->_format_raw($row['tax'], $order->data['currency_code'], $order->data['currency_value']);
              $item_no++;
            }
          }
        }
        
    // Build cart not containing discount - with tax specification
      } else {
        
        foreach ($order->data['items'] as $item) {
          $fields['item_name_'.$item_no] = $item['name'];
          $fields['item_number_'.$item_no] = $item['product_id'] . (!empty($item['option_id']) ? ':'.$item['product_id'] : '');
          $fields['quantity_'.$item_no] = $item['quantity'];
          $fields['amount_'.$item_no] = $this->_format_raw($item['price'], $order->data['currency_code'], $order->data['currency_value']);
          $fields['tax_'.$item_no] = $this->_format_raw($item['tax'], $order->data['currency_code'], $order->data['currency_value']);
          $item_no++;
        }
        
        foreach ($order->data['order_total'] as $row) {
          if ($row['calculate']) {
            $fields['item_name_'.$item_no] = $row['title'];
            $fields['item_number_'.$item_no] = $row['module_id'];
            $fields['quantity_'.$item_no] = '1';
            $fields['amount_'.$item_no] = $this->_format_raw($row['value'], $order->data['currency_code'], $order->data['currency_value']);
            $fields['tax_'.$item_no] = $this->_format_raw($row['tax'], $order->data['currency_code'], $order->data['currency_value']);
            $item_no++;
          }
        }
      }
      
      if ($this->settings['gateway'] == 'Production') {
        $gateway_url = 'https://www.paypal.com/cgi-bin/webscr';
      } else {
        $gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }
      
      return array(
        'action' => $gateway_url,
        'method' => 'post',
        'fields' => $fields,
      );
    }
    
    public function verify($order) {
      
      $errors = array();
      
      $order->save(); // Save order to database
      
      if (empty($this->settings['pdt_auth_token'])) {
        trigger_error('Could not verify Paypal payment as no PDT token configured', E_USER_WARNING);
        return;
      }
      
      if (empty($_REQUEST['tx'])) {
        error_log('An invalid attempt to verify a Paypal transaction logged for IP '. $_SERVER['REMOTE_ADDR']);
        return array('error' => 'Could not verify the Paypal transaction as no payment data was returned');
      }
      
      $post_fields = array(
        'cmd' => '_notify-synch',
        'tx'  => $_REQUEST['tx'],
        'at'  => $this->settings['pdt_auth_token'],
      );
      
      if ($this->settings['gateway'] == 'Production') {
        $gateway_url = 'https://www.paypal.com/cgi-bin/webscr';
      } else {
        $gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }
      
      $result = functions::http_fetch($gateway_url, $post_fields);
      $result = explode("\n", $result);
      
      $txdata = array();
      if (strcmp ($result[0], 'SUCCESS') == 0) {
          for ($i=1; $i<count($result);$i++){
          list($key, $val) = explode('=', $result[$i]);
          $txdata[trim(urldecode($key))] = trim(urldecode($val));
        }
      }
      
    // Calculate order total in order currency
      $order_total = 0;
      foreach ($order->data['items'] as $item) {
        $order_total += $this->_format_raw($item['quantity'] * $item['price'], $order->data['currency_code'], $order->data['currency_value']);
        $order_total += $this->_format_raw($item['quantity'] * $item['tax'], $order->data['currency_code'], $order->data['currency_value']);
      }
      foreach ($order->data['order_total'] as $row) {
        if ($row['calculate']) {
          $order_total += $this->_format_raw($row['value'], $order->data['currency_code'], $order->data['currency_value']);
          $order_total += $this->_format_raw($row['tax'], $order->data['currency_code'], $order->data['currency_value']);
        }
      }
      
      if (!empty($this->settings['use_store_currency'])) {
        if ($txdata['mc_currency'] != settings::get('store_currency_code')) $errors[] = 'Payment currency ('. $txdata['mc_currency'] .') should have been '. settings::get('store_currency_code');
      } else {
        if ($txdata['mc_currency'] != $order->data['currency_code']) $errors[] = 'Payment currency ('. $txdata['mc_currency'] .') should have been '. $order->data['currency_code'];
      }
      
      if (empty($txdata)) $errors[] = language::translate(__CLASS__.':error_transaction_not_verified', 'Error: Payment transaction could not be verified by Paypal.');
      if ($txdata['payment_status'] != 'Completed') $errors[] = 'Payment status indicates not completed.';
      if (round($txdata['mc_gross']) != round($order_total)) $errors[] = 'Payment amount '. $txdata['mc_gross'] .' is not equal to order amount ('. $order_total .').';
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
    
    private function _format_raw($value, $currency_code, $currency_value) {
      
      if (!empty($this->settings['use_store_currency'])) {
        $currency_code = settings::get('store_currency_code');
        $currency_value = 1;        
      }
      
      return number_format($value * $currency_value, currency::$currencies[$currency_code]['decimals'], '.', '');
    }
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'icon',
          'default_value' => 'images/payment/paypal.png',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_email',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_merchant_email', 'Merchant Email'),
          'description' => language::translate(__CLASS__.':description_merchant_email', 'Your Paypal registered merchant email address.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'pdt_auth_token',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_pdt_auth_token', 'PDT Auth Token'),
          'description' => language::translate(__CLASS__.':description_pdt_auth_token', 'Your Paypal PDT authorization token (see your Paypal account).'),
          'function' => 'input()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Sandbox',
          'title' => language::translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => language::translate(__CLASS__.':description_gateway', 'Select your Paypal payment gateway.'),
          'function' => 'radio(\'Production\',\'Sandbox\')',
        ),
        array(
          'key' => 'use_store_currency',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_use_store_currency', 'Use Store Currency'),
          'description' => language::translate(__CLASS__.':description_force_store_currency', 'Use the store currency for all transactions.'),
          'function' => 'toggle("y/n")',
        ),
        array(
          'key' => 'save_order',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_save_order_first', 'Save Order First'),
          'description' => language::translate(__CLASS__.':description_save_order_first', 'Save the order to the database before sending the customer to the Paypal payment window.'),
          'function' => 'toggle("y/n")',
        ),
        array(
          'key' => 'order_status_id_complete',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_complete', 'Complete'),
          'description' => language::translate(__CLASS__.':description_order_status_success', 'Give successful orders made with this payment module the following order status.'),
          'function' => 'order_status()',
        ),
        array(
          'key' => 'order_status_id_error',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_error', 'Error'),
          'description' => language::translate(__CLASS__.':description_order_status_error', 'Give failed orders made with this payment module the following order status.'),
          'function' => 'order_status()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {}
    
    public function uninstall() {}
  }
  
?>
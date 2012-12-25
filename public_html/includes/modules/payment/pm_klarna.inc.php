<?php

  class pm_klarna {
    private $system;
    public $id = __CLASS__;
    public $name = 'Klarna';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'https://www.klarna.se';
    public $website = 'https://www.klarna.se';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
      
      if (empty($this->system->session->data['klarna'])) $this->system->session->data['klarna'] = array();
      $this->cache = &$this->system->session->data['klarna'];
    }
    
    public function enabled() {
      
    }
    
    public function options() {
      
      $options = array();
      
      if ($this->settings['status'] != 'Enabled') return;
      
      if (!empty($this->settings['geo_zone_id'])) {
        if (!$this->system->functions->reference_in_geo_zone($this->settings['geo_zone_id'], $this->system->customer->data['country_code'], $this->system->customer->data['zone_code'])) return;
      }
      
      if ($this->klarna_get_currency_id($this->system->currency->selected['code']) === false) return;
      
      if ($this->klarna_get_language_id($this->system->language->selected['code']) === false) return;
      
      if ($this->klarna_get_country_id($this->system->customer->data['country_code']) === false) return;
      
      if (empty($this->settings['merchant_id'])) return;
      
      switch ($this->system->language->selected['code']) {
        case 'da':
          $conditions_lang = 'dk';
          break;
        case 'nl':
          $conditions_lang = 'nl';
          break;
        case 'fi':
          $conditions_lang = 'fi';
          break;
        case 'de':
          $conditions_lang = 'de';
          break;
        case 'nb':
          $conditions_lang = 'no';
          break;
        case 'sv':
          $conditions_lang = 'se';
          break;
        default:
          return;
      }
      
      if ($this->settings['invoice_status'] == 'Enabled') {
        
        $options[] = array(
          'id' => 'invoice',
          'icon' => 'images/payment/klarna-invoice-'. $this->system->language->selected['code'] .'.png',
          'name' => $this->system->language->translate(__CLASS__.':title_option_invoice', 'Invoice'),
          'description' => $this->system->language->translate(__CLASS__.':description_option_invoice', '')
                         . '<a href="javascript:ShowKlarnaInvoicePopup();" id="klarna_invoice"></a> ' . PHP_EOL
                         . '<script type="text/javascript" src="https://integration.klarna.com/js/klarnainvoice.js"></script>' . PHP_EOL
                         . '<script type="text/javascript">' . PHP_EOL
                         . '  if (addKlarnaInvoiceEvent) {' . PHP_EOL
                         . '      InitKlarnaInvoiceElements("klarna_invoice", '. $this->settings['merchant_id'] .', "'. $conditions_lang .'", "'. $this->system->tax->calculate($this->settings['invoice_fee'], $this->settings['tax_class_id']) .'");' . PHP_EOL
                         . '  } else {' . PHP_EOL
                         . '    addKlarnaInvoiceEvent(function() {' . PHP_EOL
                         . '      InitKlarnaInvoiceElements("klarna_invoice", '. $this->settings['merchant_id'] .', "'. $conditions_lang .'", "'. $this->system->tax->calculate($this->settings['invoice_fee'], $this->settings['tax_class_id']) .'");' . PHP_EOL
                         . '    });' . PHP_EOL
                         . '  }' . PHP_EOL
                         . '</script>' . PHP_EOL,
          'fields' => '',
          'cost' => $this->settings['invoice_fee'],
          'tax_class_id' => $this->settings['tax_class_id'],
          'confirm' => $this->system->language->translate(__CLASS__.':title_confirm_order', 'Confirm Order'),
        );
      }
      
      if ($this->settings['account_status'] == 'Enabled') {
        
        $pclasses = $this->klarna_get_pclasses();
        
        if (!empty($pclasses)) {
        
        // Build pclass options
          $pclass_options = array(
            array('-- '. $this->system->language->translate(__CLASS__.':title_installment_plan', 'Installment Plan') .' --', ''),
          );
          foreach ($pclasses as $pclass) {
            if ($this->system->cart->data['total']['value'] + $this->system->cart->data['total']['tax'] > $pclass['min_purchase_amount']) {
              if (empty($this->userdata['pclass'])) $this->userdata['pclass'] = $pclass['pclass'];
              $pclass_options[] = array($pclass['description'], $pclass['pclass']);
            }
          }
          
          $pclass_script = '<script type="text/javascript">' . PHP_EOL
                         . '  function show_pclass_info() {' . PHP_EOL
                         . '    var pclass = $("select[name=\'pclass\']").val();' . PHP_EOL
                         . '    switch (pclass) {' . PHP_EOL;
          foreach ($pclasses as $pclass) {
            $pclass_script .= '      case "'. $pclass['pclass'] .'":' . PHP_EOL
                            . '        alert("'. str_replace('"', '\"', $pclass['description'] .':\r\n'. $this->system->language->translate(__CLASS__.':title_start_fee', 'Start Fee') .': '. $this->system->currency->format($pclass['start_fee'], true, false, $this->system->currency->selected['code'], 1) .'\r\n'. $this->system->language->translate(__CLASS__.':title_invoice_fee', 'Invoice Fee') .': '. $this->system->currency->format($pclass['invoice_fee'],  true, false, $this->system->currency->selected['code'], 1) .'\r\n'. $this->system->language->translate(__CLASS__.':title_interest_rate', 'Interest Rate') .': '. number_format($pclass['interest_rate'], 2, $this->system->language->selected['decimal_point'], $this->system->language->selected['thousands_sep'])) .'%");' . PHP_EOL
                            . '        break;' . PHP_EOL;
          }
          $pclass_script .= '      default:' . PHP_EOL
                          . '        break;' . PHP_EOL
                          . '    }'. PHP_EOL
                          . '  }' . PHP_EOL
                          . '</script>' . PHP_EOL;
          
          $fields = $this->system->functions->form_draw_select_field('pclass', $pclass_options, !empty($this->userdata['pclass']) ? $this->userdata['pclass'] : '', 1, false, 'style="width: 100%;" onchange="$(this).closest(\'.option-wrapper\').find(\'button[type=submit]\').trigger(\'click\');"') . PHP_EOL
                  . '<div id="pclass_info"><a href="javascript:show_pclass_info();">'. $this->system->language->translate(__CLASS__.':title_details', 'Details') .'</a></div>' . PHP_EOL
                  . $pclass_script;
          
          $options[] = array(
              'id' => 'account',
              'icon' => 'images/payment/klarna-account-'. $this->system->language->selected['code'] .'.png',
              'name' => $this->system->language->translate(__CLASS__.':title_option_installment', 'Installment'),
              'description' => '<a href="javascript:ShowKlarnaPartPaymentPopup();" id="klarna_partpayment"></a>' . PHP_EOL
                             . '<script type="text/javascript" src="https://integration.klarna.com/js/klarnapart.js"></script>' . PHP_EOL
                             . '<script type="text/javascript">' . PHP_EOL
                             . '  if (addKlarnaPartPaymentEvent) {' . PHP_EOL
                             . '      InitKlarnaPartPaymentElements("klarna_partpayment", '. $this->settings['merchant_id'] .', "'. $conditions_lang .'", 0);' . PHP_EOL
                             . '  } else {' . PHP_EOL
                             . '    addKlarnaPartPaymentEvent(function() {' . PHP_EOL
                             . '      InitKlarnaPartPaymentElements("klarna_partpayment", '. $this->settings['merchant_id'] .', "'. $conditions_lang .'", 0);' . PHP_EOL
                             . '    });' . PHP_EOL
                             . '  }' . PHP_EOL
                             . '</script>' . PHP_EOL,
              'fields' => $fields,
              'cost' => 0,
              'tax' => 0,
              'tax_class_id' => 0,
              'confirm' => $this->system->language->translate(__CLASS__.':title_confirm_order', 'Confirm Order'),
          );
        }
      }
      
      return array(
        'title' => $this->name,
        'options' => $options
      );
    }
    
    public function pre_check() {
    }
    
    public function transfer() {
    }
    
    public function verify() {
      global $order;
      
    // Get census record
      $address = $this->klarna_get_address($order->data['customer']['tax_id']);
      
    // Set address
      if (!empty($address)) {
        
        if (isset($address['error'])) {
          return array('error' => $address['error']);
        }
        
      // Set billing address to census
        $order->data['customer']['lastname'] = $address['lastname'];
        $order->data['customer']['address1'] = $address['address1'];
        $order->data['customer']['postcode'] = $address['postcode'];
        $order->data['customer']['city'] = $address['city'];
        
      // Set delivery address to census
        $order->data['customer']['shipping_lastname'] = $address['lastname'];
        $order->data['customer']['shipping_address1'] = $address['address1'];
        $order->data['customer']['shipping_postcode'] = $address['postcode'];
        $order->data['customer']['shipping_city'] = $address['city'];
      }
      
      $result = $this->klarna_add_transaction();
      
      if (!empty($result['error'])) {
        return array('error' => $result['error']);
      }
      
      if (empty($result['transaction_id'])) {
        return array('error' => 'Payment verification failed: Missing transaction id');
      }
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => $result['transaction_id'],
      );
    }
    
    public function klarna_get_country_id($country_code) {
      switch (strtoupper($country_code)) {
        case 'FI':
          return 73;
        case 'DK':
          return 59;
        case 'NO':
          return 164;
        case 'DE':
          return 81;
        case 'SE':
          return 209;
        case 'NL':
          return 154;
        default:
          return false;
      }
    }
    
    private function klarna_get_currency_id($currency_code) {
      switch (strtoupper($currency_code)) {
        case 'EUR':
          return 2;
        case 'DKK':
          return 3;
        case 'NOK':
          return 1;
        case 'SEK':
          return 0;
        default:
          return false;
      }
    }
    
    private function klarna_get_pno_encoding($country_code) {
      switch (strtoupper($country_code)) {
        case 'FI':
          return 4;
        case 'DK':
          return 5;
        case 'NO':
          return 3;
        case 'DE':
          return 6;
        case 'SE':
          return 2;
        case 'NL':
          return 7;
        default:
          return false;
      }
    }
    
    private function klarna_get_language_id($language_code) {
      switch (strtolower($language_code)) {
        case 'fi':
          return 37;
        case 'da':
          return 27;
        case 'nb':
          return 97;
        case 'de':
          return 28;
        case 'sv':
          return 138;
        case 'nl':
          return 101;
        default:
          return false;
      }
    }
    
    private function klarna_get_pclasses() {
    
      if (!empty($this->cache['pclasses'])) {
        //return $this->cache['pclasses'];
      }
      
    // Set params
      $params = array(
        array('string', '4.1'),
        array('string', 'php:xmlrpc:1.0:'. $this->system->settings->get('store_name')),
        array('int', $this->settings['merchant_id']),
        array('int', $this->klarna_get_currency_id($this->system->currency->selected['code'])),
        array('string', base64_encode(pack("H*", hash('md5', $this->settings['merchant_id'] .':'. $this->klarna_get_currency_id($this->system->currency->selected['code']) .':'. $this->settings['merchant_key'])))),
        array('int', $this->klarna_get_country_id($this->system->customer->data['country_code'])),
        array('int', $this->klarna_get_language_id($this->system->language->selected['code']))
      );
      
    // Set request
      $request = '<?xml version="1.0" encoding="ISO-8859-1"?>' . PHP_EOL
               . '<methodCall>' . PHP_EOL
               . '  <methodName>get_pclasses</methodName>' . PHP_EOL
               . '  <params>' . PHP_EOL;
      foreach ($params as $param) $request .= $this->xmlrpc_encode($param);
      $request .= '  </params>' . PHP_EOL
                . '</methodCall>';
      
    // Send query
      $response = $this->http_post_xml($this->settings['gateway'] == 'Live' ? 'https://payment.klarna.com/' : 'http://beta-test.klarna.com/', $request);

    // Encode response if necessary
      if (strtoupper($this->system->language->selected['charset']) == 'UTF-8') $response = utf8_encode($response);
      
    // Halt on no data
      if ($response == '') {
        error_log('No response from Klarna.');
        return array();
      }
      
    // Halt on error
      if (strpos($response, '<fault>')) {
        preg_match('/<string>(.*)<\/string>/i', $response, $matches);
        return array();
      }
      
    // Parse result
      $data = $this->xmlrpc_parse_response($response);
      
    // Halt on no data
      if (empty($data[0])) return array();
      
    // Refine result
      for ($i=0; $i<count($data); $i++) {
        $data[$i] = array(
           'pclass' => $data[$i][0],
           'description' => $data[$i][1],
           'months' => $data[$i][2],
           'start_fee' => $data[$i][3] / 100,
           'invoice_fee' => $data[$i][4] / 100,
           'interest_rate' => $data[$i][5] / 100,
           'min_purchase_amount' => $data[$i][6] / 100,
           'country' => $data[$i][7],
           'type' => $data[$i][8],
           'expiry_date' => $data[$i][9],
        );
      }
      
      $this->cache['pclasses'] = $data;
      
      return $data;
    }
    
    private function klarna_get_address($crn) {
      global $order;
      
      if (isset($this->cache[$order->data['customer']['country_code'].$crn])) {
        return $this->cache[$order->data['customer']['country_code'].$crn];
      }
      
      $address = array();
      
      switch ($order->data['customer']['country_code']) {
        case 'SE':
          
          if (!$crn = $this->TiM_crn_info($crn)) {
            $address = array('error' => 'Invalid civic registration number');
            break;
          }
          $crn = substr($crn, 2, 6) .'-'. substr($crn, 8);
          
        // Set params
          $params = array(
            array('string', '4.1'),
            array('string', 'php:xmlrpc:1.0:'. $this->system->settings->get('store_name')),
            array('string', $crn),
            array('int', $this->settings['merchant_id']),
            array('string', base64_encode(pack("H*", hash('md5', $this->settings['merchant_id'] .':'. $crn .':'. $this->settings['merchant_key'])))), //mid
            array('int', $this->klarna_get_pno_encoding($order->data['customer']['country_code'])),
            array('int', '2'),
            array('string', $_SERVER['REMOTE_ADDR']),
          );
          
        // Set request
          $request = '<?xml version="1.0" encoding="ISO-8859-1"?>' . PHP_EOL
                   . '<methodCall>' . PHP_EOL
                   . '  <methodName>get_addresses</methodName>' . PHP_EOL
                   . '  <params>' . PHP_EOL;
          foreach ($params as $param) $request .= $this->xmlrpc_encode($param);
          $request .= '  </params>' . PHP_EOL
                    . '</methodCall>';
          
        // Send query
          $response = $this->http_post_xml($this->settings['gateway'] == 'Live' ? 'https://payment.klarna.com/' : 'http://beta-test.klarna.com/', $request);
          
        // Encode response if necessary
          if (strtoupper($this->system->language->selected['charset']) == 'UTF-8') $response = utf8_encode($response);
          
        // Halt on no data
          if ($response == '') {
            return array('error' => 'No response from Klarna');
          }
          
        // Halt on error
          if (strpos($response, '<fault>')) {
            preg_match('/<string>(.*)<\/string>/i', $response, $matches);
            $crn_error = $matches[1];
          }
          
        // Parse result
          $data = $this->xmlrpc_parse_response($response);
          $data = $data[0];
          
        // Refine result
          $address = array(
            'lastname' => $data[0],
            'address1' => $data[1],
            'postcode' => $data[2],
            'city' => $data[3],
            'country' => strtoupper($data[4]),
          );
          
          break;
      }
      
      $this->cache[$order->data['customer']['country_code'].$crn] = $address;
      
      return $address;
    }
    
    private function klarna_add_transaction() {
      global $order;

    // Prepare the transaction key
      $transaction_keys = array();
      
    // Set params
      $params = array(
        array('string', '4.1'),
        array('string', 'php:xmlrpc:1.0:'. $this->system->settings->get('store_name')),
        array('string', $this->TiM_crn_info($order->data['customer']['tax_id'])),
        array('int', 0), // gender (0=female, 1=male)
        array('string', 'cID '. $order->data['customer']['id']),
        array('string', ''),
        array('string', $order->data['uid']), // orderid1
        array('string', ''), // orderid2
        array('struct', array(
          'email' => array('string', $order->data['customer']['email']),
          'phone' => array('string', $order->data['customer']['phone']),
          'cellno' => array('string', $order->data['customer']['phone']),
          'fname' => array('string', $order->data['customer']['shipping_address']['firstname']),
          'lname' => array('string', $order->data['customer']['shipping_address']['lastname']),
          'street' => array('string', $order->data['customer']['shipping_address']['address1']),
          'careof' => array('string', $order->data['customer']['shipping_address']['address2']),
          'zip' => array('string', $order->data['customer']['shipping_address']['postcode']),
          'city' => array('string', $order->data['customer']['shipping_address']['city']),
          'country' => array('int', $this->klarna_get_country_id($order->data['customer']['shipping_address']['country_code'])),
          'house_number' => array('string', ''),
          'house_extension' => array('string', ''),
          'company' => array('string', $order->data['customer']['shipping_address']['company'])
        )),
        array('struct', array(
          'email' => array('string', $order->data['customer']['email']),
          'phone' => array('string', $order->data['customer']['phone']),
          'cellno' => array('string', $order->data['customer']['phone']),
          'fname' => array('string', $order->data['customer']['firstname']),
          'lname' => array('string', $order->data['customer']['lastname']),
          'street' => array('string', $order->data['customer']['address1']),
          'careof' => array('string', $order->data['customer']['address2']),
          'zip' => array('string', $order->data['customer']['postcode']),
          'city' => array('string', $order->data['customer']['city']),
          'country' => array('int', $this->klarna_get_country_id($order->data['customer']['country_code'])),
          'house_number' => array('string', ''),
          'house_extension' => array('string', ''),
          'company' => array('string', $order->data['customer']['company'])
        )),
        array('string', $_SERVER['REMOTE_ADDR']),
        array('int', $this->settings['gateway'] == 'Live' ? 1 : 2),
        array('int', $this->klarna_get_currency_id($order->data['currency_code'])),
        array('int', $this->klarna_get_country_id($this->system->settings->get('store_country_code'))),
        array('int', $this->klarna_get_language_id($order->data['language_code'])),
        array('int', $this->settings['merchant_id']),
        array('string', '%md5_checksum'),
        array('int', $this->klarna_get_pno_encoding($order->data['customer']['country_code'])),
        array('int', (!empty($this->userdata['pclass'])) ? $this->userdata['pclass'] : '-1') // invoice = -1
      );
      
      $params_cart = array();
    
    // Include cart in xml and collect sub total sums
      foreach (array_keys($order->data['items']) as $key) {
        $transaction_keys[] = strip_tags((strtoupper($this->system->language->selected['charset']) == 'UTF-8') ? utf8_decode($order->data['items'][$key]['name']) : $order->data['items'][$key]['name']);
        $params_cart[] = array('struct', array(
          'goods' => array('struct', array(
              'artno' => array('string', $order->data['items'][$key]['code']),
              'title' => array('string', $order->data['items'][$key]['name']),
              'price' => array('int', round($this->system->currency->convert($order->data['items'][$key]['price'] * 100, $this->system->settings->get('store_currency_code'), $order->data['currency_code']))), // incl. tax
              'vat' => array('double', round($order->data['items'][$key]['tax'] / $order->data['items'][$key]['price'] * 100, 2)), // tax rate
              'discount' => array('double', 0),
              'flags' => array('int', 0), // 0 = incl. tax
            )),
          'qty' => array('int', $order->data['items'][$key]['quantity'])
        ));
      }
      
    // Include order total rows
      foreach (array_keys($order->data['order_total']) as $key) {
        if (empty($order->data['order_total'][$key]['calculate'])) continue;
        $transaction_keys[] = strip_tags((strtoupper($this->system->language->selected['charset']) == 'UTF-8') ? utf8_decode($order->data['order_total'][$key]['title']) : $GLOBALS[$class]->output[$i]['title']);
        $params_cart[] = array('struct', array(
          'goods' => array('struct', array(
              'artno' => array('string', '-'),
              'title' => array('string', strip_tags($order->data['order_total'][$key]['title'])),
              'price' => array('int', round($this->system->currency->convert($order->data['order_total'][$key]['value'] * 100, $this->system->settings->get('store_currency_code'), $order->data['currency_code']))), // incl. tax
              'vat' => array('double', round($order->data['order_total'][$key]['tax'] / $order->data['order_total'][$key]['value'] * 100, 2)), // tax rate
              'discount' => array('double', 0),
              'flags' => array('int', 0), // 0 = incl. tax
            )),
          'qty' => array('int', 1)
        ));
      }
      
    // Append to transaction_key
      $transaction_keys[] = $this->settings['merchant_key'];
      
    // Append cart to params
      $params = array_merge($params, array(
        array('array', $params_cart),
        array('string', ''), // comment
        array('struct', array(
          'delay_adjust' => array('int', 1)
        )),
        array('struct', array(
          null
        )),
        array('struct', array(
          //'yearly_salary' => array('int', 0)
        )),
        array('struct', array(
          null
        )),
        array('struct', array(
          //'dev_id_1' => array('string', ''),
          //'dev_id_2' => array('string', ''),
          //'dev_id_3' => array('string', ''),
          //'beh_id_1' => array('string', ''),
          //'beh_id_2' => array('string', ''),
          //'beh_id_3' => array('string', '')
        )),
        array('struct', array(
          'cust_no' => array('string', $order->data['customer']['id']),
          'estore_user' => array('string', $order->data['customer']['firstname'] .' '. $order->data['customer']['lastname']),
          //'ready_date' => array('string', date('Y-m-d')),
          //'rand_string' => array('string', rand(11111, 99999)),
          //'bclass' => array('string', ''),
          //'pin' => array('string', ''),
        )),
      ));
      
    // Encode request
      $request = '<?xml version="1.0" encoding="ISO-8859-1"?>' . PHP_EOL
               . '<methodCall>' . PHP_EOL
               . '  <methodName>add_invoice</methodName>' . PHP_EOL
               . '  <params>' . PHP_EOL;
      foreach ($params as $param) $request .= $this->xmlrpc_encode($param);
      $request .= '  </params>' . PHP_EOL
                . '</methodCall>';
      
    // Insert transaction key into XML
      $request = str_replace('%md5_checksum', base64_encode(pack("H*", hash('md5', implode(':', $transaction_keys)))), $request);
      
    // Charset compatibility
      if (strtoupper($this->system->language->selected['charset']) == 'UTF-8') $request = utf8_decode($request);
      
      $response = $this->http_post_xml($this->settings['gateway'] == 'Live' ? 'https://payment.klarna.com/' : 'http://beta-test.klarna.com/', $request);
      
    // Halt on no data
      if ($response == '') {
        return array('error' => 'No response from Klarna.');
      }
      
    // Encode response if necessary
      if (strtoupper($this->system->language->selected['charset']) == 'UTF-8') $response = utf8_encode($response);
      
    // Halt on error
      if (strpos($response, '<fault>')) {
        preg_match('/<string>(.*)<\/string>/i', $response, $matches);
        return array('error' => $matches[1]);
      }
      
    // Extract response
      list($klarna_order_id, $klarna_order_status) = $this->xmlrpc_parse_response($response);
      
      if (empty($klarna_order_id)) return array('error' => 'Failure in Klarna communication. Failed creating order id.');
      
      if (empty($klarna_order_status)) return array('error' => 'Failure in Klarna communication. Order status failure.');
      
      return array(
        'transaction_id' => $klarna_order_id,
        'order_status' => $klarna_order_status,
      );
    }
    
    private function TiM_crn_info($pno) {
      
      $pno = str_replace(array('-',' '), '', $pno);
      
      if(!preg_match("/^[0-9]{10}$/", $pno) && !preg_match("/^[0-9]{12}$/", $pno)) return false;
      
      if (strlen($pno) == 10) {
        if (substr($pno, 2, 2) > 19) $pno = '16' . $pno;
        else if (date('y') < substr($pno, 0, 2)) $pno = '19' . $pno;
        else $pno = '20' . $pno;
      }
      
      if (!in_array(substr($pno, 0, 2), array('16', '19', '20'))) return false;
      
      $pno10 = substr($pno, 2, 10);
      $n = 2;
      $sum = 0;
      for ($i=0; $i<9; $i++) {
        $tmp = $pno10[$i] * $n;
        ($tmp > 9) ? $sum += 1 + ($tmp % 10) : $sum += $tmp;
        ($n == 2) ? $n = 1 : $n = 2;
      }
      if (($sum + $pno10[9]) % 10) return false;
      
      return $pno;
    }
    
  // By TiM
    private function xmlrpc_encode($var, $indent='') {
      
      $output = '';
      
      if (empty($var[0])) return false;
      
      $is_root = $indent ? false : true;
      
      if ($is_root) {
        $indent = '    ';
        $output = $indent.'<param>' . PHP_EOL;
      }
      
      switch($var[0]) {
        
        case 'array':
          $output .= $indent.'  <value>' . PHP_EOL
                   . $indent.'    <array>' . PHP_EOL
                   . $indent.'      <data>' . PHP_EOL;
          foreach ($var[1] as $subvar) {
            if (!empty($subvar)) {
              $output .= $this->xmlrpc_encode($subvar, $indent.'      ');
            }
          }
          $output .= $indent.'      </data>' . PHP_EOL
                   . $indent.'    </array>' . PHP_EOL
                   . $indent.'  </value>' . PHP_EOL;
          break;
          
        case 'struct':
          $output .= $indent.'  <value>' . PHP_EOL
                   . $indent.'    <struct>' . PHP_EOL;
          foreach ($var[1] as $name => $subvar) {
            if (!empty($subvar)) {
              $output .= $indent.'      <member>' . PHP_EOL
                       . $indent.'        <name>'. $name .'</name>' . PHP_EOL
                       . $this->xmlrpc_encode($subvar, $indent.'      ')
                       . $indent.'      </member>' . PHP_EOL;
            }
          }
          $output .= $indent.'    </struct>' . PHP_EOL
                   . $indent.'  </value>' . PHP_EOL;
          break;
          
        default:
          $output .= $indent.'  <value>' . PHP_EOL
                   . $indent.'    <'. $var[0] .'>'. $var[1] .'</'. $var[0] .'>' . PHP_EOL
                   . $indent.'  </value>' . PHP_EOL;
          break;
      }
      
      if ($is_root) {
        $output .= $indent.'</param>' . PHP_EOL;
      }
      
      return $output;
    }
    
  // By hofmeister
    private function xmlrpc_parse_response($xml) {
      
      $xml = simplexml_load_string(trim($xml));
      $response = array();
      
      if (count($xml->fault) > 0) {
        return false;
        // An error was returned
        //$fault = $this->xmlrpc_parse_value($xml->fault->value);
        //throw new Exception($fault->faultString, $fault->faultCode);
      }

      if (count($xml->params->param) == 1) $scalar = true;

      foreach ($xml->params->param as $param) {
        $value_struct = $param->value;
        
        $value = $this->xmlrpc_parse_value($value_struct);
        if ($scalar) {
          return $value;
        } else {
          $response[] = $value;
        }
      }
      
      return $response;
    }
    
  // By hofmeister
    private function xmlrpc_parse_value($value_struct) {
      switch(true) {
        case count($value_struct->struct) > 0:
          $value = new stdClass();
          foreach($value_struct->struct->member as $member) {
            $name = (string)$member->name;
            $member_value = $this->xmlrpc_parse_value($member->value);
            $value->$name = $member_value;
          }
          return $value;
        case count($value_struct->array) > 0:
          $value = array();
          foreach($value_struct->array->data->value as $array_value) {
            $value[] = $this->xmlrpc_parse_value($array_value);
          }
          return $value;
        case count($value_struct->i4) > 0:
          return (int)$value_struct->i4;
        case count($value_struct->int) > 0:
          return (int)$value_struct->int;
        case count($value_struct->boolean) > 0:
          return (boolean) $value_struct->boolean;
        case count($value_struct->string) > 0:
          return (strtoupper($this->system->language->selected['charset']) == 'UTF-8') ? utf8_encode((string)$value_struct->string) : (string)$value_struct->string;
        case count($value_struct->double) > 0:
          return (double)$value_struct->double;
        case count($value_struct->dateTime) > 0:
          return (string)$value_struct->dateTime;
        case count($value_struct->base64) > 0:
          return (string)$value_struct->base64;
      }
    }
    
    function http_post_xml($url, $xml=false) {
    
      $headers = array(
        "Content-Type: text/xml",
        "User-Agent: PHPRPC/1.0",
        "Content-length: ". strlen($xml),
        "Connection: Close"
      );
      
      if (function_exists('curl_init')) {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        
      } else {
        
        $parts = parse_url($url);
        if (substr($url, 0, 8) == 'https://') {
          $parts['port'] = 443;
          $parts['ssl'] = true;
          $parts['host'] = $parts['host'];
        }
        
        $fp = fsockopen(((isset($parts['ssl']) && $parts['ssl'] == true) ? 'ssl://' : false) . $parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        
        if (!$fp) throw new Exception("Problem with $url, $errstr");
        
        $out = "POST " . $parts['path'] . ((isset($parts['query'])) ? "?" . $parts['query'] : false) ." HTTP/1.1\r\n"
             . "Host: ". $parts['host'] ."\r\n"
             .  implode("\r\n", $headers) . "\r\n"
             . "\r\n" . $xml;
        
        fwrite($fp, $out);
        
        $found_body = false;
        $response = '';
        $start = microtime(true);
        $timeout = 60;
        
        while (!feof($fp)) {
          if ((microtime(true) - $start) > $timeout) break;
        
          $row = fgets($fp);

          if ($found_body) {
            $response .= $row;
          } else if ($row == "\r\n") {
            $found_body = true;
            continue;
          }
        }
        
        fclose($fp);
      }
      
      $response = str_replace('><', '>'. PHP_EOL .'<', $response);
      
      $this->debug = array(
        'query' => $xml,
        'response' => $response
      );
      
      return $response;
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
          'key' => 'invoice_status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_invoice_status', 'Invoice Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_invoice_status', ''),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'account_status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_account_status', 'Account Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_account_status', ''),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'merchant_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_id', 'Merchant ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_id', 'Your merchant ID provided by Klarna.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by Klarna.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Live',
          'title' => $this->system->language->translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => $this->system->language->translate(__CLASS__.':description_gateway', 'Select your payment gateway.'),
          'function' => 'radio(\'Test\',\'Live\')',
        ),
        array(
          'key' => 'invoice_fee',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_cost', 'Invoice Fee'),
          'description' => $this->system->language->translate(__CLASS__.':description_invoice_fee', ''),
          'function' => 'decimal()',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => $this->system->language->translate(__CLASS__.':description_tax_class', 'The tax class for the invoice fee.'),
          'function' => 'tax_classes()',
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
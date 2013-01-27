<?php

  // Sveawebpay API version 2.1.0
  class pm_sveawebpay_se {
    private $system;
    public $id = __CLASS__;
    public $name = 'SveaWebPay';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.0';
    public $support_link = 'https://www.tim-international.net';
    public $website = 'https://www.sveawebpay.se';
    public $priority = 0;
    
    public function __construct() {
      global $system;
      $this->system = $system;
    }
    
  /*
   * Return selectable payment options for checkout
   */
    public function options($items, $subtotal, $tax, $currency_code, $customer) {
      global $payment;
      
      $options = array();
      
    // If not enabled
      if ($this->settings['status'] != 'Enabled') return;
      
    // If not in geo zone
      if ($customer['country_code'] != 'SE') return;
      
      if (empty($this->settings['merchant_id'])) return;
      
      $options = array();
      
      if ($this->settings['card_status'] == 'Enabled') {
        $options[] = array(
          'id' => 'card',
          'icon' => 'images/payment/sveawebpay-card.png',
          'name' => $this->system->language->translate(__CLASS__.':title_option_card', 'Card'),
          'description' => $this->system->language->translate(__CLASS__.':description_option_card', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => $this->system->language->translate(__CLASS__.':title_next', 'Next'),
        );
      }
      
      if ($this->settings['internetbank_status'] == 'Enabled') {
        $options[] = array(
          'id' => 'internetbank',
          'icon' => 'images/payment/sveawebpay-internetbank.png',
          'name' => $this->system->language->translate(__CLASS__.':title_option_internet_bank', 'Internet Bank'),
          'description' => $this->system->language->translate(__CLASS__.':description_option_internet_bank', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => $this->system->language->translate(__CLASS__.':title_next', 'Next'),
        );
      }
      
      if ($this->settings['invoice_status'] == 'Enabled') {
        $options[] = array(
          'id' => 'invoice',
          'icon' => 'images/payment/sveawebpay-invoice.png',
          'name' => $this->system->language->translate(__CLASS__.':title_option_invoice', 'Invoice'),
          'description' => $this->system->language->translate(__CLASS__.':description_option_invoice', ''),
          'fields' => '',
          'cost' => $this->settings['invoice_fee'],
          'tax_class_id' => $this->settings['tax_class_id'],
          'confirm' => $this->system->language->translate(__CLASS__.':title_next', 'Next'),
        );
      }
      
      if ($this->settings['installment_status'] == 'Enabled') {
        if ($this->system->currency->convert($subtotal + $tax, $this->system->settings->get('store_currency_code'), 'SEK') >= $this->settings['installment_minimum_limit']) {
          $options[] = array(
            'id' => 'installment',
            'icon' => 'images/payment/sveawebpay-installment.png',
            'name' => $this->system->language->translate(__CLASS__.':title_option_installment', 'Installment'),
            'description' => $this->system->language->translate(__CLASS__.':description_option_installment', ''),
            'fields' => '',
            'cost' => 0,
            'tax_class_id' => 0,
            'confirm' => $this->system->language->translate(__CLASS__.':title_next', 'Next'),
          );
        }
      }
      
      return array(
        'title' => $this->name,
        'options' => $options,
      );
    }
    
    public function select($option_id) {
    }
    
    public function pre_check() {
    }
    
    public function transfer() {
      global $order, $payment;
      
      if (empty($this->settings['merchant_id'])) return;
      
      if (!in_array($order->data['currency_code'], explode(',', 'SEK,NOK,DKK,EUR'))) return;
      
      list($payment_module, $payment_option) = explode(':', $order->data['payment_option']['id']);
      
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
           . '<payment>' . PHP_EOL
           . '  <currency>'. $order->data['currency_code'] .'</currency>' . PHP_EOL
           . '  <amount>'. round($order->data['payment_due']*100) .'</amount>' . PHP_EOL
           . '  <vat>'. round($order->data['tax']['total']*100) .'</vat>' . PHP_EOL
           . '  <customerrefno>'. $order->data['uid'] .'</customerrefno>' . PHP_EOL
           . '  <customer>' . PHP_EOL
           //. '    <ssn>'. $order->data['customer']['tax_id'] .'</ssn>' . PHP_EOL
           . '    <country>'. $order->data['customer']['country_code'] .'</country>' . PHP_EOL
           . '  </customer>' . PHP_EOL
           . '  <returnurl>'. $this->system->document->link('order_process.php') .'</returnurl>' . PHP_EOL
           . '  <callbackurl>'. $this->system->document->link('order_process.php', array('order_uid' => $order->data['uid'])) .'</callbackurl>' . PHP_EOL
           . '  <cancelurl>'. $this->system->document->link('checkout.php') .'</cancelurl>' . PHP_EOL
           . '  <iscompany>'. (!empty($order->data['customer']['company']) ? 'true' : 'false') .'</iscompany>' . PHP_EOL;
      
      switch($payment_option) {
        case 'card':
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>DBDANSKEBANKSE</exclude>' . PHP_EOL
                . '  <exclude>DBNORDEASE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBFTGSE</exclude>' . PHP_EOL
                . '  <exclude>DBSHBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSWEDBANKSE</exclude>' . PHP_EOL
                . '  <exclude>PAYPAL</exclude>' . PHP_EOL
                . '  <exclude>SVEASPLITSE</exclude>' . PHP_EOL
                . '  <exclude>SVEAINVOICESE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        case 'internetbank':
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>CARD</exclude>' . PHP_EOL
                . '  <exclude>PAYPAL</exclude>' . PHP_EOL
                . '  <exclude>SVEASPLITSE</exclude>' . PHP_EOL
                . '  <exclude>SVEAINVOICESE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        case 'invoice':
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>CARD</exclude>' . PHP_EOL
                . '  <exclude>DBDANSKEBANKSE</exclude>' . PHP_EOL
                . '  <exclude>DBNORDEASE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBFTGSE</exclude>' . PHP_EOL
                . '  <exclude>DBSHBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSWEDBANKSE</exclude>' . PHP_EOL
                . '  <exclude>PAYPAL</exclude>' . PHP_EOL
                . '  <exclude>SVEASPLITSE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        case 'installment':
        //  $xml .= '<paymentmethod>SVEASPLITSE</paymentmethod>' . PHP_EOL;
                //. '<campaigncode>'. (int)$this->userdata['campaigncode'] .'</campaigncode>' . PHP_EOL;
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>SVEAINVOICESE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        default:
          trigger_error('Unknown payment option', E_USER_ERROR);
          break;
      }
      
      $xml .= '  <orderrows>' . PHP_EOL;
      
      $item_no = 1;
      foreach ($order->data['items'] as $item) {
        $xml .= '    <row>' . PHP_EOL
              . '      <name>'. $item['name'] .'</name>' . PHP_EOL
              . '      <amount>'. round($this->system->tax->calculate($item['price'], $item['tax_class_id'], true)*100) .'</amount>' . PHP_EOL
              . '      <description></description>' . PHP_EOL
              . '      <vat>'. round($this->system->tax->get_tax($item['price'], $item['tax_class_id'])*100) .'</vat>' . PHP_EOL
              . '      <quantity>'. $item['quantity'] .'</quantity>' . PHP_EOL
              . '      <sku>'. (!empty($item['sku']) ? $item['sku'] : $item['code']) .'</sku>' . PHP_EOL
              . '      <unit></unit>' . PHP_EOL
              . '    </row>' . PHP_EOL;
        $item_no++;
      }
      
      foreach ($order->data['order_total'] as $row) {
        if ($row['calculate']) {
          $xml .= '    <row>' . PHP_EOL
                . '      <name>'. $row['title'] .'</name>' . PHP_EOL
                . '      <amount>'. round($this->system->tax->calculate($row['value'], $row['tax_class_id'], true)*100) .'</amount>' . PHP_EOL
                . '      <description></description>' . PHP_EOL
                . '      <vat>'. round($this->system->tax->get_tax($row['value'], $row['tax_class_id'])*100) .'</vat>' . PHP_EOL
                . '      <quantity>1</quantity>' . PHP_EOL
                . '      <sku>-</sku>' . PHP_EOL
                . '      <unit></unit>' . PHP_EOL
                . '    </row>' . PHP_EOL;
          $item_no++;
        }
      }
      
      $xml .= '  </orderrows>' . PHP_EOL
            . '</payment>' . PHP_EOL;
      echo $xml;
      if (strtolower($this->system->language->selected['charset']) != 'utf-8') $xml = utf8_encode($xml);
      
      return array(
        'action' => ($this->settings['gateway'] == 'Live') ? 'https://webpay.sveaekonomi.se/webpay/payment' : 'https://test.sveaekonomi.se/webpay/payment',
        'method' => 'post',
        'fields' => array(
          'message' => base64_encode($xml),
          'merchantid' => $this->settings['merchant_id'],
          'mac' => hash('sha512', base64_encode($xml) . $this->settings['merchant_key']),
        ),
      );
    }
    
    public function verify() {
      global $order;
      
      if (empty($_GET) && empty($_POST)) {
        header('Location: '. $this->system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
        exit;
      }
      
      if (!empty($_POST['response'])) {
        $_SVEA = $_POST;
      } else {
        // Don't use $_GET due to suhosin.get.max_value_length
        $_SVEA = array();
        $params = explode('&', $_SERVER['QUERY_STRING']);
        foreach ($params as $pair) {
          list($key, $value) = explode('=', $pair);
          $_SVEA[urldecode($key)] = urldecode($value);
        }
      }
      
      if (empty($_SVEA['response'])) {
        return array('error' => 'Error: Could not verify payment due to missing response parameter.');
      }
      
    // Verify checksum
      if (empty($_SVEA['mac']) || $_SVEA['mac'] != hash('sha512', $_SVEA['response'] . $this->settings['merchant_key'])) return array('error' => 'Error: Payment verification failed');
      
      $xml = simplexml_load_string(base64_decode($_SVEA['response']));
      
      if (!empty($xml->statuscode)) {
        switch ((string)$xml->statuscode) {
          case '1':
            $code = 'REQUIRES_MANUAL_REVIEW';
            $errormsg = 'Request performed successfully but requires manual review from merchant. Applicable paymentmethods: PAYPAL 100 INTERNAL_ERROR Invalid – contact integrator';
          case '101':
            $code = 'XMLPARSEFAIL';
            $errormsg = 'Invalid XML 102 ILLEGAL_ENCODING Invalid encoding';
            break;
          case '104':
            $code = 'ILLEGAL_URL';
            $errormsg = '';
            break;
          case '105':
            $code = 'ILLEGAL_TRANSACTIONSTATUS';
            $errormsg = 'Invalid transaction status';
            break;
          case '106':
            $code = 'EXTERNAL_ERROR';
            $errormsg = 'Failure at third party e.g. failure at the bank';
            break;
          case '107':
            $code = 'DENIED_BY_BANK';
            $errormsg = 'Transaction rejected by bank';
            break;
          case '108':
            $code = 'CANCELLED';
            $errormsg = 'Transaction cancelled';
            break;
          case '109':
            $code = 'NOT_FOUND_AT_BANK';
            $errormsg = 'Transaction not found at the bank';
            break;
          case '110':
            $code = 'ILLEGAL_TRANSACTIONID';
            $errormsg = 'Invalid transaction ID';
            break;
          case '111':
            $code = 'MERCHANT_NOT_CONFIGURED';
            $errormsg = 'Merchant not configured';
            break;
          case '112':
            $code = 'MERCHANT_NOT_CONFIGURED_AT_BANK';
            $errormsg = 'Merchant not configured at the bank';
            break;
          case '113':
            $code = 'PAYMENTMETHOD_NOT_CONFIGURED';
            $errormsg = 'Payment method not configured for merchant';
            break;
          case '114':
            $code = 'TIMEOUT_AT_BANK';
            $errormsg = 'Timeout at the bank';
            break;
          case '115':
            $code = 'MERCHANT_NOT_ACTIVE';
            $errormsg = 'The merchant is disabled';
            break;
          case '116':
            $code = 'PAYMENTMETHOD_NOT_ACTIVE';
            $errormsg = 'The payment method is disabled';
            break;
          case '117':
            $code = 'ILLEGAL_AUTHORIZED_AMOUNT';
            $errormsg = 'Invalid authorized amount';
            break;
          case '118':
            $code = 'ILLEGAL_CAPTURED_AMOUNT';
            $errormsg = 'Invalid captured amount';
            break;
          case '119':
            $code = 'ILLEGAL_CREDITED_AMOUNT';
            $errormsg = 'Invalid credited amount';
            break;
          case '120':
            $code = 'NOT_SUFFICIENT_FUNDS';
            $errormsg = 'Not enough founds';
            break;
          case '121':
            $code = 'EXPIRED_CARD';
            $errormsg = 'The card has expired';
            break;
          case '122':
            $code = 'STOLEN_CARD';
            $errormsg = 'Stolen card';
            break;
          case '123':
            $code = 'LOST_CARD';
            $errormsg = 'Lost card';
            break;
          case '124':
            $code = 'EXCEEDS_AMOUNT_LIMIT';
            $errormsg = 'Amount exceeds the limit';
            break;
          case '125':
            $code = 'EXCEEDS_FREQUENCY_LIMIT';
            $errormsg = 'Frequency limit exceeded';
            break;
          case '126':
            $code = 'TRANSACTION_NOT_BELONGING_TO_MERCHANT';
            $errormsg = 'Transaction does not belong to merchant';
            break;
          case '127':
            $code = 'CUSTOMERREFNO_ALREADY_USED';
            $errormsg = 'Customer reference number already used in another transaction';
            break;
          case '128':
            $code = 'NO_SUCH_TRANS';
            $errormsg = 'Transaction does not exist';
            break;
          case '129':
            $code = 'DUPLICATE_TRANSACTION';
            $errormsg = 'More than one transaction found for the given customer reference number';
            break;
          case '130':
            $code = 'ILLEGAL_OPERATION';
            $errormsg = 'Operation not allowed for the given payment method';
            break;
          case '131':
            $code = 'COMPANY_NOT_ACTIVE';
            $errormsg = 'Company inactive';
            break;
          case '132':
            $code = 'SUBSCRIPTION_NOT_FOUND';
            $errormsg = 'No subscription exist';
            break;
          case '133':
            $code = 'SUBSCRIPTION_NOT_ACTIVE';
            $errormsg = 'Subscription not active';
            break;
          case '134':
            $code = 'SUBSCRIPTION_NOT_SUPPORTED';
            $errormsg = 'Payment method doesn’t support subscriptions';
            break;
          case '135':
            $code = 'ILLEGAL_DATE_FORMAT';
            $errormsg = 'Illegal date format';
            break;
          case '136':
            $code = 'ILLEGAL_RESPONSE_DATA';
            $errormsg = 'Illegal response data';
            break;
          case '137':
            $code = 'IGNORE_CALLBACK';
            $errormsg = 'Ignore callback';
            break;
          case '138':
            $code = 'CURRENCY_NOT_CONFIGURED';
            $errormsg = 'Currency not configured';
            break;
          case '139':
            $code = 'CURRENCY_NOT_ACTIVE';
            $errormsg = 'Currency not active';
            break;
          case '140':
            $code = 'CURRENCY_ALREADY_CONFIGURED';
            $errormsg = 'Currency is already configured';
            break;
          case '141':
            $code = 'ILLEGAL_AMOUNT_OF_RECURS_TODAY';
            $errormsg = 'Ilegal amount of recurs per day';
            break;
          case '142':
            $code = 'NO_VALID_PAYMENT_METHODS';
            $errormsg = 'No valid paymentmethods';
            break;
          case '143':
            $code = 'CREDIT_DENIED_BY_BANK';
            $errormsg = 'Credit denied by bank';
            break;
          case '144':
            $code = 'ILLEGAL_CREDIT_USER';
            $errormsg = 'User is not allowed to perform credit operation';
            break;
          case '300':
            $code = 'BAD_CARDHOLDER_NAME';
            $errormsg = 'Invalid value for cardholder name';
            break;
          case '301':
            $code = 'BAD_TRANSACTION_ID';
            $errormsg = 'Invalid value for transaction id';
            break;
          case '302':
            $code = 'BAD_REV';
            $errormsg = 'Invalid value for rev';
            break;
          case '303':
            $code = 'BAD_MERCHANT_ID';
            $errormsg = 'Invalid value for merchant id';
            break;
          case '304':
            $code = 'BAD_LANG';
            $errormsg = 'Invalid value for lang';
            break;
          case '305':
            $code = 'BAD_AMOUNT';
            $errormsg = 'Invalid value for amount';
            break;
          case '306':
            $code = 'BAD_CUSTOMERREFNO';
            $errormsg = 'Invalid value for customer refno 307';
            break;
          case '307':
            $code = 'BAD_CURRENCY';
            $errormsg = 'Invalid value for currency';
            break;
          case '308':
            $code = 'BAD_PAYMENTMETHOD';
            $errormsg = 'Invalid value for payment method';
            break;
          case '309':
            $code = 'BAD_RETURNURL';
            $errormsg = 'Invalid value for return url';
            break;
          case '310':
            $code = 'BAD_LASTBOOKINGDAY';
            $errormsg = 'Invalid value for last booking day';
            break;
          case '311':
            $code = 'BAD_MAC';
            $errormsg = 'Invalid value for mac';
            break;
          case '312':
            $code = 'BAD_TRNUMBER';
            $errormsg = 'Invalid value for tr number';
            break;
          case '313':
            $code = 'BAD_AUTHCODE';
            $errormsg = 'Invalid value for authcode';
            break;
          case '314':
            $code = 'BAD_CC_DESCR';
            $errormsg = 'Invalid value for cc_descr';
            break;
          case '315':
            $code = 'BAD_ERROR_CODE';
            $errormsg = 'Invalid value for error_code';
            break;
          case '316':
            $code = 'BAD_CARDNUMBER_OR_CARDTYPE_NOT_CONFIGURED';
            $errormsg = 'Card type not configured for merchant';
            break;
          case '317':
            $code = 'BAD_SSN';
            $errormsg = 'Invalid value for ssn';
            break;
          case '318':
            $code = 'BAD_VAT';
            $errormsg = 'Invalid value for vat';
            break;
          case '319':
            $code = 'BAD_CAPTURE_DATE';
            $errormsg = 'Invalid value for capture date';
            break;
          case '320':
            $code = 'BAD_CAMPAIGN_CODE_INVALID';
            $errormsg = 'Invalid value for campaign code. There are no valid matching campaign codes';
            break;
          case '321':
            $code = 'BAD_SUBSCRIPTION_TYPE';
            $errormsg = 'Invalid subscription type';
            break;
          case '322':
            $code = 'BAD_SUBSCRIPTION_ID';
            $errormsg = 'Invalid subscription id';
            break;
          case '323':
            $code = 'BAD_BASE64';
            $errormsg = 'Invalid base64';
            break;
          case '324':
            $code = 'BAD_CAMPAIGN_CODE';
            $errormsg = 'Invalid campaign code. Missing value';
            break;
          case '325':
            $code = 'BAD_CALLBACKURL';
            $errormsg = 'Invalid callbackurl';
            break;
          case '326':
            $code = 'THREE_D_CHECK_FAILED';
            $errormsg = '3D check failed';
            break;
          case '327':
            $code = 'CARD_NOT_ENROLLED';
            $errormsg = 'Card not enrolled in 3D secure';
            break;
          case '328':
            $code = 'BAD_IPADDRESS';
            $errormsg = 'Provided ip address is incorrect';
            break;
          case '329':
            $code = 'BAD_MOBILE';
            $errormsg = 'Bad mobile phone number';
            break;
          case '330':
            $code = 'BAD_COUNTRY';
            $errormsg = 'Bad country parameter';
            break;
          case '331':
            $code = 'THREE_D_CHECK_NOT_AVAILABLE';
            $errormsg = 'Merchants 3D configuration invalid';
            break;
          case '332':
            $code = 'TIMEOUT';
            $errormsg = 'Timeout at Svea';
            break;
          case '500':
            $code = 'ANTIFRAUD_CARDBIN_NOT_ALLOWED';
            $errormsg = 'Antifraud - cardbin not allowed';
            break;
          case '501':
            $code = 'ANTIFRAUD_IPLOCATION_NOT_ALLOWED';
            $errormsg = 'Antifraud – iplocation not allowed';
            break;
          case '502':
            $code = 'ANTIFRAUD_IPLOCATION_AND_BIN_DOESNT_MATCH';
            $errormsg = 'Antifraud – ip-location and bin does not match';
            break;
          case '503':
            $code = 'ANTIFRAUD_MAX_AMOUNT_PER_IP_EXCEEDED';
            $errormsg = 'Antofraud – max amount per ip exceeded';
            break;
          case '504':
            $code = 'ANTIFRAUD_MAX_TRANSACTIONS_PER_IP_EXCEEDED';
            $errormsg = 'Antifraud – max transactions per ip exceeded';
            break;
          case '505':
            $code = 'ANTIFRAUD_MAX_TRANSACTIONS_PER_CARDNO_EXCEEDED';
            $errormsg = 'Antifraud – max transactions per card number exceeded';
            break;
          case '506':
            $code = 'ANTIFRAUD_MAX_AMOUNT_PER_CARDNO_EXCEEDED';
            $errormsg = 'Antifraud – max amount per cardnumer exceeded';
            break;
          case '507':
            $code = 'ANTIFRAUD_IP_ADDRESS_BLOCKED';
            $errormsg = 'Antifraud – IP address blocked';
            break;
          default:
            $code = 'UNKNOWN_ERROR';
            $errormsg = 'Unknown error';
            break;
        }
        
        return array('error' => 'Error: '. $errormsg .' (Code: '. (string)$xml->statuscode .')');
      }
      
      if (empty($xml->transaction['id'])) {
        return array('error' => 'Error: Missing payment transaction id');
      }
      
      if (($xml->transaction->amount/100) != $order->data['payment_due']) $errors[] = 'Error: Could not verify payment amount';
      
      if ($xml->transaction->currency != $order->data['currency_code']) $errors[] = 'Error: Could not verify payment currency';
      
      if (!empty($xml->transaction->customer)) {
      
        if (!empty($xml->transaction->customer->legalname)) {
          list($lastname, $firstname) = explode(', ', (string)$xml->transaction->customer->legalname);
          $order->data['customer']['firstname'] = $firstname;
          $order->data['customer']['lastname']  = $lastname;
          $order->data['customer']['shipping_address']['firstname'] = $firstname;
          $order->data['customer']['shipping_address']['lastname']  = $lastname;
        }
        
        $order->data['customer']['tax_id'] = (string)$xml->transaction->customer->ssn;
        
        $order->data['customer']['address1']  = (string)$xml->transaction->customer->addressline1;
        $order->data['customer']['address2']  = (string)$xml->transaction->customer->addressline2;
        $order->data['customer']['city'] = (string)$xml->transaction->customer->postarea;
        $order->data['customer']['postcode']  = (string)$xml->transaction->customer->postcode;
        
        $order->data['customer']['shipping_address']['address1']  = (string)$xml->transaction->customer->addressline1;
        $order->data['customer']['shipping_address']['address2']  = (string)$xml->transaction->customer->addressline2;
        $order->data['customer']['shipping_address']['city'] = (string)$xml->transaction->customer->postarea;
        $order->data['customer']['shipping_address']['postcode']  = (string)$xml->transaction->customer->postcode;
      }
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'transaction_id' => (string)$xml->transaction['id'],
      );
    }
    
    public function after_process() {
    }
    
    public function get_campaigns() {
      global $order, $payment;
      
      if (empty($this->settings['merchant_id'])) return;
      
      if (!in_array($order->data['currency_code'], explode(',', 'SEK,NOK,DKK,EUR'))) return;
      
      list($payment_module, $payment_option) = explode(':', $order->data['payment_option']['id']);
      
      $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
           . '<payment>' . PHP_EOL
           . '  <currency>'. $order->data['currency_code'] .'</currency>' . PHP_EOL
           . '  <amount>'. round($order->data['payment_due']*100) .'</amount>' . PHP_EOL
           . '  <vat>'. round($order->data['tax']['total']*100) .'</vat>' . PHP_EOL
           . '  <customerrefno>'. $order->data['uid'] .'</customerrefno>' . PHP_EOL
           . '  <customer>' . PHP_EOL
           . '    <ssn>'. $order->data['customer']['tax_id'] .'</ssn>' . PHP_EOL
           . '    <country>'. $order->data['customer']['country_code'] .'</country>' . PHP_EOL
           . '  </customer>' . PHP_EOL
           . '  <returnurl>'. $this->system->document->link('order_process.php') .'</returnurl>' . PHP_EOL
           . '  <callbackurl>'. $this->system->document->link('order_process.php', array('order_uid' => $order->data['uid'])) .'</callbackurl>' . PHP_EOL
           . '  <cancelurl>'. $this->system->document->link('checkout.php') .'</cancelurl>' . PHP_EOL
           . '  <iscompany>'. (!empty($order->data['customer']['company']) ? 'true' : 'false') .'</iscompany>' . PHP_EOL;
      
      switch($payment_option) {
        case 'card':
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>DBDANSKEBANKSE</exclude>' . PHP_EOL
                . '  <exclude>DBNORDEASE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBFTGSE</exclude>' . PHP_EOL
                . '  <exclude>DBSHBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSWEDBANKSE</exclude>' . PHP_EOL
                . '  <exclude>PAYPAL</exclude>' . PHP_EOL
                . '  <exclude>SVEASPLITSE</exclude>' . PHP_EOL
                . '  <exclude>SVEAINVOICESE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        case 'internetbank':
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>CARD</exclude>' . PHP_EOL
                . '  <exclude>PAYPAL</exclude>' . PHP_EOL
                . '  <exclude>SVEASPLITSE</exclude>' . PHP_EOL
                . '  <exclude>SVEAINVOICESE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        case 'invoice':
          $xml .= '<excludepaymentmethods>' . PHP_EOL
                . '  <exclude>CARD</exclude>' . PHP_EOL
                . '  <exclude>DBDANSKEBANKSE</exclude>' . PHP_EOL
                . '  <exclude>DBNORDEASE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSEBFTGSE</exclude>' . PHP_EOL
                . '  <exclude>DBSHBSE</exclude>' . PHP_EOL
                . '  <exclude>DBSWEDBANKSE</exclude>' . PHP_EOL
                . '  <exclude>PAYPAL</exclude>' . PHP_EOL
                . '  <exclude>SVEASPLITSE</exclude>' . PHP_EOL
                . '</excludepaymentmethods>' . PHP_EOL;
          break;
        case 'installment':
          $xml .= '<paymentmethod>SVEASPLITEU_SE</paymentmethod>' . PHP_EOL
                . '<campaigncode>'. (int)$this->userdata['campaigncode'] .'</campaigncode>' . PHP_EOL;
          break;
        default:
          trigger_error('Unknown payment option', E_USER_ERROR);
          break;
      }
      
      $xml .= '  <orderrows>' . PHP_EOL;
      
      $item_no = 1;
      foreach ($order->data['items'] as $item) {
        $xml .= '    <row>' . PHP_EOL
              . '      <name>'. $item['name'] .'</name>' . PHP_EOL
              . '      <amount>'. round($this->system->tax->calculate($item['price'], $item['tax_class_id'], true)*100) .'</amount>' . PHP_EOL
              . '      <description></description>' . PHP_EOL
              . '      <vat>'. round($this->system->tax->get_tax($item['price'], $item['tax_class_id'])*100) .'</vat>' . PHP_EOL
              . '      <quantity>'. $item['quantity'] .'</quantity>' . PHP_EOL
              . '      <sku>'. (!empty($item['sku']) ? $item['sku'] : $item['code']) .'</sku>' . PHP_EOL
              . '      <unit></unit>' . PHP_EOL
              . '    </row>' . PHP_EOL;
        $item_no++;
      }
      
      foreach ($order->data['order_total'] as $row) {
        if ($row['calculate']) {
          $xml .= '    <row>' . PHP_EOL
                . '      <name>'. $row['title'] .'</name>' . PHP_EOL
                . '      <amount>'. round($this->system->tax->calculate($row['value'], $row['tax_class_id'], true)*100) .'</amount>' . PHP_EOL
                . '      <description></description>' . PHP_EOL
                . '      <vat>'. round($this->system->tax->get_tax($row['value'], $row['tax_class_id'])*100) .'</vat>' . PHP_EOL
                . '      <quantity>1</quantity>' . PHP_EOL
                . '      <sku>-</sku>' . PHP_EOL
                . '      <unit></unit>' . PHP_EOL
                . '    </row>' . PHP_EOL;
          $item_no++;
        }
      }
      
      $xml .= '  </orderrows>' . PHP_EOL
            . '</payment>' . PHP_EOL;
      echo $xml;
      if (strtolower($this->system->language->selected['charset']) != 'utf-8') $xml = utf8_encode($xml);
      
      return array(
        'action' => ($this->settings['gateway'] == 'Live') ? 'https://webpay.sveaekonomi.se/webpay/payment' : 'https://test.sveaekonomi.se/webpay/payment',
        'method' => 'post',
        'fields' => array(
          'message' => base64_encode($xml),
          'merchantid' => $this->settings['merchant_id'],
          'mac' => hash('sha512', base64_encode($xml) . $this->settings['merchant_key']),
        ),
      );
    }
    
    public function callback() {
      file_put_contents('svea.txt', print_r($_SERVER, true) . print_r($_GET, true) . print_r($_POST, true));
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
          'key' => 'card_status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_card_status', 'Card Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_card_status', ''),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'internetbank_status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_internetbank_status', 'Internet Bank Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_internetbank_status', ''),
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
          'key' => 'installment_status',
          'default_value' => 'Enabled',
          'title' => $this->system->language->translate(__CLASS__.':title_installment_status', 'Installment Status'),
          'description' => $this->system->language->translate(__CLASS__.':description_installment_status', ''),
          'function' => 'radio("Enabled", "Disabled")',
        ),
        array(
          'key' => 'merchant_id',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_api_merchant_id', 'Merchant ID'),
          'description' => $this->system->language->translate(__CLASS__.':description_api_merchant_id', 'Your sveawebpay merchant ID.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key', 'Your sveawebpay merchant key.'),
          'function' => 'smalltext()',
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
          'key' => 'installment_minimum_limit',
          'default_value' => '1000',
          'title' => $this->system->language->translate(__CLASS__.':title_installment_minimum_limit', 'Installment Minimum Limit'),
          'description' => $this->system->language->translate(__CLASS__.':description_installment_minimum_limit', 'Disabled installment option if subtotal is below the given amount in SEK (including tax).'),
          'function' => 'int()',
        ),
        array(
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => $this->system->language->translate(__CLASS__.':title_order_status', 'Order Status') .': '. $this->system->language->translate(__CLASS__.':title_complete', 'Complete'),
          'description' => $this->system->language->translate(__CLASS__.':description_order_status', 'Give orders made with this payment module the following order status.'),
          'function' => 'order_status()',
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
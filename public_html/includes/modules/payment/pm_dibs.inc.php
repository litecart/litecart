<?php

  class pm_dibs {
    private $system;
    public $id = __CLASS__;
    public $name = 'DIBS';
    public $description = '';
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
      
      if ($this->dibs_get_currency_id($this->system->currency->selected['code']) === false) return;
      
      if (empty($this->settings['merchant_id'])) return;
      
      return array(
        'title' => $this->name,
        'options' => array(
          array(
            'id' => 'flexwin',
            'icon' => 'images/payment/dibs.png',
            'name' => $this->system->language->translate(__CLASS__.':title_option_flexwin', 'FlexWin'),
            'description' => $this->system->language->translate(__CLASS__.':description_option_flexwin', ''),
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

      $amount = number_format($this->system->currency->convert($order->data['payment_due'], $this->system->settings->get('store_currency_code'), $order->data['currency_code']), 2, '', '');
      $currency = $this->dibs_get_currency_id($order->data['currency_code']);
      
      $checksum = md5(
        $this->settings['merchant_key_2'] .
        md5(
          $this->settings['merchant_key_1'] .
          'merchant=' . $this->settings['merchant_id'] .
          '&orderid=' . $order->data['uid'] .
          '&currency=' . $currency .
          '&amount=' . $amount
        )
      );
      
      $fields = array(
        'merchant' => $this->settings['merchant_id'],
        'orderid' => $order->data['uid'],
        'lang' => $order->data['language_code'],
        'amount' => $amount,
        'currency' => $currency,
        'payType' => $this->settings['payment_types'],
        'cancelurl' => $this->system->document->link('checkout.php'),
        'accepturl' => $this->system->document->link('order_process.php'),
        'skiplastpage' => 'YES',
        'calcfee' => 'NO',
        'uniqueoid' => $order->data['uid'],
        'test' => ($this->settings['gateway'] == 'Live') ? 'NO' : 'YES',
        'capturenow' => 'YES',
        'ip' => $_SERVER['SERVER_ADDR'],
        'md5key' => $checksum,
      );
      
      $fields = array_merge($fields, array(
        'delivery01.Firstname' => $order->data['customer']['firstname'],
        'delivery02.Lastname' => $order->data['customer']['lastname'],
        'delivery03.Company' => $order->data['customer']['company'],
        'delivery04.Address' => $order->data['customer']['address1'],
        'delivery05.Suburb' => $order->data['customer']['address2'],
        'delivery06.City' => $order->data['customer']['city'],
        'delivery07.Postcode' => $order->data['customer']['postcode'],
        'delivery08.State' => $this->system->functions->reference_get_zone_name($order->data['customer']['country_code'], $order->data['customer']['zone_code']),
        'delivery09.Country' => $this->system->functions->reference_get_country_name($order->data['customer']['country_code']),
        'delivery10.Telephone' => $order->data['customer']['phone'],
        'delivery11.Email' => $order->data['customer']['email'],
        'delivery12.Delivery' => 'Delivery Information',
        'delivery13.Firstname' => $order->data['customer']['shipping_address']['firstname'],
        'delivery14.Lastname' => $order->data['customer']['shipping_address']['lastname'],
        'delivery15.Company' => $order->data['customer']['shipping_address']['company'],
        'delivery16.Address' => $order->data['customer']['shipping_address']['address1'],
        'delivery17.Suburb' => $order->data['customer']['shipping_address']['address2'],
        'delivery18.City' => $order->data['customer']['shipping_address']['city'],
        'delivery19.Postcode' => $order->data['customer']['shipping_address']['postcode'],
        'delivery20.State' => $this->system->functions->reference_get_zone_name($order->data['customer']['country_code'], $order->data['customer']['shipping_address']['zone_code']),
        'delivery21.Country' => $this->system->functions->reference_get_country_name($order->data['customer']['shipping_address']['country_code']),
        'delivery22.Comment' => '',
      ));
      
      $fields = array_merge($fields, array(
        'ordline0-1' => 'Line',
        'ordline0-2' => 'Name',
        'ordline0-3' => 'Quantity',
        'ordline0-4' => 'Price',
        'ordline0-4' => 'VAT',
      ));
      $i = 1;
      
      foreach (array_keys($order->data['items']) as $key) {
        $fields = array_merge($fields, array(
          'ordline'.($i).'-1' => $i,
          'ordline'.($i).'-2' => $order->data['items'][$key]['name'],
          'ordline'.($i).'-3' => $order->data['items'][$key]['quantity'],
          'ordline'.($i).'-4' => $this->system->currency->format($order->data['items'][$key]['price'], false, false, $order->data['currency_code']),
          'ordline'.($i).'-4' => $this->system->currency->format($order->data['items'][$key]['tax'], false, false, $order->data['currency_code']),
        ));
        $i++;
      }
      
      foreach (array_keys($order->data['order_total']) as $key) {
        if (empty($order->data['order_total'][$key]['calculate'])) continue;
        $fields = array_merge($fields, array(
          'ordline'.($i).'-1' => $i,
          'ordline'.($i).'-2' => $order->data['order_total'][$key]['title'],
          'ordline'.($i).'-3' => 1,
          'ordline'.($i).'-4' => $this->system->currency->format($order->data['order_total'][$key]['value'], false, false, $order->data['currency_code']),
          'ordline'.($i).'-4' => $this->system->currency->format($order->data['order_total'][$key]['tax'], false, false, $order->data['currency_code']),
        ));
        $i++;
      }
      
      return array(
        'action' => 'https://payment.architrade.com/paymentweb/start.action',
        'method' => 'post',
        'fields' => $fields,
      );
    }
    
    public function verify() {
      global $order;
      
      $_POST['amount'] = $_POST['amount'] + $_POST['fee'];
      
      $checksum = md5(
        $this->settings['merchant_key_2'] .
        md5(
          $this->settings['merchant_key_1'] .
          'transact=' . $_POST['transact'] .
          '&amount=' . $_POST['amount'] .
          '&currency=' . $_POST['currency']
        )
      );
      
      if ($_POST['authkey'] != $checksum) {
        return array('error' => 'Could not verify payment');
      }
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => $_POST['transact'],
        'comments' => 'DIBS Authorized ' . date("Y-m-d H:i:s") . PHP_EOL
                    . ' - Transaction #: ' . $trans_id . PHP_EOL
                    . ' - DIBS order #: ' . $order_id . PHP_EOL
                    . ' - OSC order#: ' . $insert_id
      );
    }
    
    public function after_process() {
    }
    
    public function callback() {
    }
    
    private function dibs_get_currency_id($currency_code) {
      
      $currency_code = strtoupper($currency_code);
    
      $map = array(
        'AFN' => '971', 'ALL' => '8', 'AMD' => '51', 'ANG' => '532', 'AOA' => '973', 'ARS' => '32', 'AUD' => '36', 'AWG' => '533', 'AZN' => '944', 'BAM' => '977',
        'BBD' => '52', 'BDT' => '50', 'BGN' => '975', 'BHD' => '48', 'BIF' => '108', 'BMD' => '60', 'BND' => '96', 'BOB' => '68', 'BOV' => '984', 'BRL' => '986', 'BSD' => '44', 'BTN' => '64',
        'BWP' => '72', 'BYR' => '974', 'BZD' => '84', 'CAD' => '124', 'CDF' => '976', 'CHE' => '947', 'CHF' => '756', 'CHW' => '948', 'CLF' => '990', 'CLP' => '152', 'CNY' => '156', 'COP' => '170',
        'COU' => '970', 'CRC' => '188', 'CUP' => '192', 'CVE' => '132', 'CYP' => '196', 'CZK' => '203', 'DJF' => '262', 'DKK' => '208', 'DOP' => '214', 'DZD' => '12', 'EEK' => '233', 'EGP' => '818',
        'ERN' => '232', 'ETB' => '230', 'EUR' => '978', 'FJD' => '242', 'FKP' => '238', 'GBP' => '826', 'GEL' => '981', 'GHS' => '288', 'GIP' => '292', 'GMD' => '270', 'GNF' => '324',
        'GTQ' => '320', 'GYD' => '328', 'HKD' => '344', 'HNL' => '340', 'HRK' => '191', 'HTG' => '332', 'HUF' => '348', 'IDR' => '360', 'ILS' => '376', 'INR' => '356', 'IQD' => '368', 'IRR' => '364',
        'ISK' => '352', 'JMD' => '388', 'JOD' => '400', 'JPY' => '392', 'KES' => '404', 'KGS' => '417', 'KHR' => '116', 'KMF' => '174', 'KPW' => '408', 'KRW' => '410', 'KWD' => '414', 'KYD' => '136',
        'KZT' => '398', 'LAK' => '418', 'LBP' => '422', 'LKR' => '144', 'LRD' => '430', 'LSL' => '426', 'LTL' => '440', 'LVL' => '428', 'LYD' => '434', 'MAD' => '504', 'MDL' => '498', 'WST' => '882',
        'MGA' => '969', 'MKD' => '807', 'MMK' => '104', 'MNT' => '496', 'MOP' => '446', 'MRO' => '478', 'MTL' => '470', 'MUR' => '480', 'MVR' => '462', 'MWK' => '454', 'MXN' => '484', 'MXV' => '979',
        'MYR' => '458', 'MZN' => '943', 'NAD' => '516', 'NGN' => '566', 'NIO' => '558', 'NOK' => '578', 'NPR' => '524', 'NZD' => '554', 'OMR' => '512', 'PAB' => '590', 'PEN' => '604', 'PGK' => '598',
        'PHP' => '608', 'PKR' => '586', 'PLN' => '985', 'PYG' => '600', 'QAR' => '634', 'RON' => '946', 'RSD' => '941', 'RUB' => '643', 'RWF' => '646', 'SAR' => '682', 'SBD' => '90', 'SCR' => '690',
        'SDG' => '938', 'SEK' => '752', 'SGD' => '702', 'SHP' => '654', 'SKK' => '703', 'SLL' => '694', 'SOS' => '706', 'SRD' => '968', 'STD' => '678', 'SYP' => '760', 'SZL' => '748', 'USN' => '997',
        'THB' => '764', 'TJS' => '972', 'TMM' => '795', 'TND' => '788', 'TOP' => '776', 'TRY' => '949', 'TTD' => '780', 'TWD' => '901', 'TZS' => '834', 'UAH' => '980', 'UGX' => '800', 'USD' => '840',
        'USS' => '998', 'UYU' => '858', 'UZS' => '860', 'VEB' => '862', 'VND' => '704', 'VUV' => '548', 'XAF' => '950', 'XAG' => '961', 'XAU' => '959', 'XBA' => '955', 'XBB' => '956', 'XBC' => '957',
        'XBD' => '958', 'XCD' => '951', 'XDR' => '960', 'XOF' => '952', 'XPD' => '964', 'XPF' => '953', 'XPT' => '962', 'XTS' => '963', 'XXX' => '999', 'YER' => '886', 'ZAR' => '710', 'ZMK' => '894',
        'ZWD' => '716'
      );
      
      if (!isset($map[$currency_code])) return false;
      
      return $map[$currency_code];
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
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_id', 'Your merchant ID provided by Dibs.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key_1',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key_1', 'Merchant Key 1'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key_1', 'Your merchant MD5 1 key provided by Dibs.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key_2',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_merchant_key_2', 'Merchant Key 2'),
          'description' => $this->system->language->translate(__CLASS__.':description_merchant_key_2', 'Your merchant MD5 2 key provided by Dibs.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'payment_types',
          'default_value' => '',
          'title' => $this->system->language->translate(__CLASS__.':title_payment_types', 'Payment Types'),
          'description' => $this->system->language->translate(__CLASS__.':description_payment_types', 'A coma separated list of payment types to be displayed.'),
          'function' => 'input("ALL_CARDS,ALL_NETBANKS,ALL_INVOICES")',
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
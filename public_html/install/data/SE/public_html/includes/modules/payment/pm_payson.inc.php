<?php

  class pm_payson {
    public $id = __CLASS__;
    public $name = 'Payson';
    public $description = '';
    public $author = 'TiM International';
    public $version = '2.1';
    public $support_link = 'https://api.payson.se/';
    public $website = 'https://www.payson.se';
    public $priority = 0;

    public function options($items, $subtotal, $tax, $currency_code, $customer) {

    // If not enabled
      if (empty($this->settings['status'])) return;

    // If not in geo zone
      if (!empty($this->settings['geo_zone_id'])) {
        if (!reference::country($customer['country_code'])->in_geo_zone($this->settings['geo_zone_id'], $customer)) return;
      }

      $options = [];

    // Card method
      if (!empty($this->settings['card_status'])) {
        $options[] = [
          'id' => 'card',
          'icon' => $this->settings['icon'],
          'name' => language::translate(__CLASS__.':title_card_payment', 'Card Payment'),
          'description' => language::translate(__CLASS__.':description_card_payment', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
        ];
      }

    // Bank method
      if (!empty($this->settings['direct_bank_status'])) {
        $options[] = [
          'id' => 'direct_bank',
          'icon' => $this->settings['icon'],
          'name' => language::translate(__CLASS__.':title_direct_bank_payment', 'Direct Bank Payment'),
          'description' => language::translate(__CLASS__.':description_bank_payment', ''),
          'fields' => '',
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
        ];
      }

    // Invoice method
      if (!empty($this->settings['invoice_status']) && $subtotal >= $this->settings['order_minimum']) {
        $options[] = [
          'id' => 'invoice',
          'icon' => $this->settings['icon'],
          'name' => language::translate(__CLASS__.':title_invoice_payment', 'Invoice Payment'),
          'description' => language::translate(__CLASS__.':description_invoice_payment', ''),
          'fields' => '',
          'cost' => $this->settings['invoice_fee'],
          'tax_class_id' => $this->settings['tax_class_id'],
          'confirm' => language::translate(__CLASS__.':title_pay_now', 'Pay Now'),
        ];
      }

      return [
        'title' => $this->name,
        'options' => $options,
      ];
    }

    public function transfer($order) {

      try {
        $order->save(); // Creates a order id

        if (!in_array($order->data['currency_code'], ['EUR', 'SEK'])) {
          throw new Exception($order->data['currency_code'] . ' is not a valid Payson currency');
        }

        $request = [
          'receiverList.receiver(0).email' => $this->settings['merchant_email'],
          'receiverList.receiver(0).amount' => currency::format_raw($order->data['payment_due'], $order->data['currency_code'], $order->data['currency_value']),
          'localeCode' => strtoupper(language::$selected['code']),
          'currencyCode' => $order->data['currency_code'],
          'returnUrl' => (string)document::ilink('order_process'),
          'cancelUrl' => (string)document::ilink('checkout'),
          'feesPayer' => 'PRIMARYRECEIVER',
          'memo' => settings::get('store_name'),
          'trackingId' => $order->data['id'],
          'guaranteeOffered' => 'NO',
          'senderEmail' => $order->data['customer']['email'],
          'senderFirstName' => $order->data['customer']['firstname'],
          'senderLastName' => $order->data['customer']['lastname'],
          'ipnNotificationUrl' => (string)document::link(WS_DIR_APP.'ext/payson/callback.php'),
          'fundingList.fundingConstraint(0).constraint' => 'INVOICE',
          'invoiceFee' => '0',
        ];

        list($module_id, $option_id) = explode(':', $order->data['payment_option']['id']);
        switch($option_id) {
          case 'card':
            $request['fundingList.fundingConstraint(0).constraint'] = 'CREDITCARD';
            break;
          case 'direct_bank':
            $request['fundingList.fundingConstraint(0).constraint'] = 'BANK';
            break;
          case 'invoice':
            $request['fundingList.fundingConstraint(0).constraint'] = 'INVOICE';
            break;
          default:
            die('Unknown payment option '. $option_id);
        }

        $item_no = 0;

        if (!empty($this->settings['display_cart_contents'])) {

          foreach ($order->data['items'] as $item) {
            $request = array_merge($request, [
              'orderItemList.orderItem('.$item_no.').description' => $item['name'],
              'orderItemList.orderItem('.$item_no.').sku' => $item['sku'] ? $item['sku'] : '-',
              'orderItemList.orderItem('.$item_no.').quantity' => $item['quantity'],
              'orderItemList.orderItem('.$item_no.').unitPrice' => currency::format_raw($item['price'], $order->data['currency_code'], $order->data['currency_value']),
              'orderItemList.orderItem('.$item_no.').taxPercentage' => ($item['price'] != 0 && $item['tax'] != 0) ? round($item['tax'] / $item['price'], 2) : 0,
            ]);
            $item_no++;
          }

          foreach ($order->data['order_total'] as $row) {
            if (!empty($row['calculate'])) {
              $request = array_merge($request, [
                'orderItemList.orderItem('.$item_no.').description' => $row['title'],
                'orderItemList.orderItem('.$item_no.').sku' => '-',
                'orderItemList.orderItem('.$item_no.').quantity' => '1',
                'orderItemList.orderItem('.$item_no.').unitPrice' => currency::format_raw($row['value'], $order->data['currency_code'], $order->data['currency_value']),
                'orderItemList.orderItem('.$item_no.').taxPercentage' => ($row['value'] != 0 && $row['tax'] != 0) ? round($row['tax'] / $row['value'], 2) : 0,
              ]);
              $item_no++;
            }
          }

        } else {
          $request = array_merge($request, [
            'orderItemList.orderItem(0).description' => settings::get('store_name'),
            'orderItemList.orderItem(0).sku' => $order->data['uid'],
            'orderItemList.orderItem(0).quantity' => '1',
            'orderItemList.orderItem(0).unitPrice' => currency::calculate($order->data['payment_due'] - $order->data['tax_total'], $order->data['currency_code']),
            'orderItemList.orderItem(0).taxPercentage' => round($order->data['tax_total'] / $order->data['payment_due'] * 100, 2),
          ]);
        }

        if ($this->settings['gateway'] == 'Test') {
          $url = 'https://test-api.payson.se/1.0/Pay/';
        } else {
          $url = 'https://api.payson.se/1.0/Pay/';
        }

        $response = $this->http_post($url, $request);

      // Extract response
        parse_str($response, $response);

      // Verify result
        if (!isset($response['responseEnvelope_ack']) || $response['responseEnvelope_ack'] != 'SUCCESS') {
          throw new Exception(!empty($response['errorList_error(0)_message']) ? $response['errorList_error(0)_message'] : 'Failure in Payson communication');
        }

      // Verify token
        if (empty($response['TOKEN'])) {
          throw new Exception('Failure in Payson communication: No token');
        }

      // Register token
        session::$data['payson_token'] = $response['TOKEN'];

        if ($this->settings['gateway'] == 'Test') {
          $url = 'https://test-www.payson.se/paysecure/';
        } else {
          $url = 'https://www.payson.se/paySecure/';
        }

      // Redirect
        return [
          'method' => 'get',
          'action' => document::link($url, ['token' => $response['TOKEN']]),
        ];

      } catch(Exception $e) {
        return ['error' => $e->getMessage()];
      }
    }

    public function verify($order) {

      try {
        if ($this->settings['gateway'] == 'Test') {
          $url = 'https://test-api.payson.se/1.0/PaymentDetails/';
        } else {
          $url = 'https://api.payson.se/1.0/PaymentDetails/';
        }

        if (empty(session::$data['payson_token'])) {
          throw new Exception('Payment verification failure: Missing session token');
        }

      // Query API
        $response = $this->http_post($url, ['token' => session::$data['payson_token']]);

      // Extract response
        parse_str($response, $response);

      // Throw a couple of errors
        if (empty($response['responseEnvelope_ack']) || $response['responseEnvelope_ack'] != 'SUCCESS') {
          throw new Exception('Payment verification failure: ' . !empty($response['errorList_error(0)_message']) ? $response['errorList_error(0)_message'] : 'Unknown error');
        }

      // Verify purchase ID
        if (empty($response['purchaseId'])) {
          throw new Exception('Payment verification failure: Missing purchase id');
        }

      // Verify status
        switch (strtolower($response['status'])) {
          case 'created':
          case 'pending':
          case 'processing':    $orders_status_id = $this->settings['order_status_id_pending']; break;
          case 'completed':     $orders_status_id = $this->settings['order_status_id_completed']; break;
          case 'incomplete':    throw new Exception('The payment was incomplete');
          case 'error':         throw new Exception('There were payment errors');
          case 'expired':       throw new Exception('The payment has expired');
          case 'reversalerror': throw new Exception('The payment had reversal errors');
          case 'aborted':       throw new Exception('The payment was aborted');
          default:              throw new Exception('Unknown payment status');
        }

      // Set shipping address
        if (isset($response['fundingList_fundingConstraint(0)_constraint']) && $response['fundingList_fundingConstraint(0)_constraint'] == 'INVOICE') {
          $name_parts = explode(' ', $response['shippingAddress_name']);
          $order->data['customer']['firstname'] = array_shift($name_parts);
          $order->data['customer']['lastname'] = array_pop($name_parts);
          $order->data['customer']['firstname'] .= $name_parts ? ' ' . implode(' ', $name_parts) : ''; // Append middle names to firstname
          $order->data['customer']['address1'] = $response['shippingAddress_streetAddress'];
          $order->data['customer']['postcode'] = $response['shippingAddress_postalCode'];
          $order->data['customer']['city'] = $response['shippingAddress_city'];
          $order->data['customer']['country_code'] = $response['shippingAddress_country'];
        }

      // Verify currency
        if (!isset($response['currencyCode']) || $response['currencyCode'] != $order->data['currency_code']) {
          throw new Exception('Failure in payment: Payment currency did not match order currency');
        }

      // Verify amount
        if (empty($response['receiverList_receiver(0)_amount']) || ($response['receiverList_receiver(0)_amount'] != currency::format_raw($order->data['payment_due'], $order->data['currency_code'], $order->data['currency_value']))) {
          throw new Exception('Failure in payment: Payment amount '. $response['receiverlist.receiver(0).amount'] .' did not match order amount '. currency::format_raw($order->data['payment_due'], $order->data['currency_code'], $order->data['currency_value']) .'.');
        }

        return [
          'transaction_id' => $response['purchaseId'],
          'order_status_id' => $orders_status_id,
        ];

      } catch(Exception $e) {
        return ['error' => $e->getMessage()];
      }
    }

    function http_post($url, $post_fields=false, $raw=false) {

      mb_convert_variables(language::$selected['charset'], 'UTF-8', $post_fields);

      $headers = [
        'PAYSON-SECURITY-USERID' => $this->settings['merchant_id'],
        'PAYSON-SECURITY-PASSWORD' => $this->settings['merchant_key'],
        'PAYSON-APPLICATION-ID' => 'Litecart',
      ];

      $client = new wrap_http();
      $response = $client->call('POST', $url, $post_fields, $headers);

      $this->_last_request = $client->last_request;
      $this->_last_response = $client->last_response;

      mb_convert_variables('UTF-8', language::$selected['charset'], $response);

      return $response;
    }

    function settings() {
      return [
        [
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', ''),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'icon',
          'default_value' => 'images/payment/payson.png',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
          'function' => 'text()',
        ],
        [
          'key' => 'card_status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_card_status', 'Card Enabled'),
          'description' => language::translate(__CLASS__.':description_card_enabled', 'Enables or disables the card payment option.'),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'direct_bank_status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_direct_bank_status', 'Direct Bank Enabled'),
          'description' => language::translate(__CLASS__.':description_direct_bank_enabled', 'Enables or disables the direct bank payment option.'),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'invoice_status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_invoice_enabled', 'Invoice Enabled'),
          'description' => language::translate(__CLASS__.':description_invoice_enabled', 'Enables or disables the invoice payment option.'),
          'function' => 'toggle("e/d")',
        ],
        [
          'key' => 'merchant_email',
          'default_value' => 'testagent-1@payson.se',
          'title' => language::translate(__CLASS__.':title_merchant_email', 'Merchant Email'),
          'description' => language::translate(__CLASS__.':description_merchant_email', 'Your merchant email.'),
          'function' => 'text()',
        ],
        [
          'key' => 'merchant_id',
          'default_value' => '4',
          'title' => language::translate(__CLASS__.':title_merchant_id', 'Merchant ID'),
          'description' => language::translate(__CLASS__.':description_merchant_id', 'Your merchant ID provided by Payson.'),
          'function' => 'text()',
        ],
        [
          'key' => 'merchant_key',
          'default_value' => '2acab30d-fe50-426f-90d7-8c60a7eb31d4',
          'title' => language::translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => language::translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by Payson.'),
          'function' => 'password()',
        ],
        [
          'key' => 'gateway',
          'default_value' => 'Test',
          'title' => language::translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => language::translate(__CLASS__.':description_gateway', 'Select your payment gateway.'),
          'function' => 'radio(\'Live\',\'Test\')',
        ],
        [
          'key' => 'invoice_fee',
          'default_value' => '20',
          'title' => language::translate(__CLASS__.':title_invoice_fee', 'Invoice Fee'),
          'description' => language::translate(__CLASS__.':description_invoice_fee', 'The fee for your invoice excluding tax.'),
          'function' => 'decimal()',
        ],
        [
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => language::translate(__CLASS__.':description_tax_class', 'The tax class for the fee.'),
          'function' => 'tax_class()',
        ],
        [
          'key' => 'order_minimum',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_minimum', 'Order Minimum'),
          'description' => language::translate(__CLASS__.':description_order_minimum', 'The minimum payment amount for invoice.'),
          'function' => 'decimal()',
        ],
        [
          'key' => 'display_cart_contents',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_display_cart_contents', 'Display Cart Contents'),
          'description' => language::translate(__CLASS__.':description_display_cart_contents', 'Display the cart contents on the invoice.'),
          'function' => 'toggle("y/n")',
        ],
        [
          'key' => 'order_status_id_pending',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_pending', 'Pending'),
          'description' => language::translate(__CLASS__.':description_order_status', 'Give transactions with this status the following order status.'),
          'function' => 'order_status()',
        ],
        [
          'key' => 'order_status_id_canceled',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_canceled', 'Canceled'),
          'description' => language::translate(__CLASS__.':description_order_status', 'Give transactions with this status the following order status.'),
          'function' => 'order_status()',
        ],
        [
          'key' => 'order_status_id_completed',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_completed', 'Completed'),
          'description' => language::translate(__CLASS__.':description_order_status', 'Give transactions with this status the following order status.'),
          'function' => 'order_status()',
        ],
        [
          'key' => 'order_status_id_credited',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_credited', 'Credited'),
          'description' => language::translate(__CLASS__.':description_order_status', 'Give transactions with this status the following order status.'),
          'function' => 'order_status()',
        ],
        [
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise, leave it blank.'),
          'function' => 'geo_zone()',
        ],
        [
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'number()',
        ],
      ];
    }

    public function install() {
      database::query(
        "CREATE TABLE IF NOT EXISTS ". DB_TABLE_PREFIX ."payson (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `purchase_id` varchar(32) NOT NULL,
          `status` varchar(16) NOT NULL,
          `parameters` TEXT NOT NULL,
          `ip` varchar(15) NOT NULL,
          `date_created` datetime NOT NULL,
          PRIMARY KEY (`id`)
        );"
      );
    }

    public function uninstall() {
      database::query(
        "DROP TABLE IF EXISTS ". DB_TABLE_PREFIX ."payson;"
      );
    }
  }

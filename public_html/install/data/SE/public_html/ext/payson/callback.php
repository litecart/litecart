<?php

  require('../../includes/app_header.inc.php');

  try {

  // Store callback in database
    database::query(
      "insert into `". DB_DATABASE ."`.`". DB_TABLE_PREFIX ."payson`
      (purchase_id, status, parameters, ip, date_created)
      values ('". database::input($_POST['purchaseId']) ."', '". database::input($_POST['status']) ."', '". database::input(serialize($_POST)) ."', '". database::input($_SERVER['REMOTE_ADDR']) ."', '". date('Y-m-d H:i:s') ."');"
    );

  // Halt if no POST data
    if (empty($_POST)) throw new Exception('A Payson callback from '. $_SERVER['REMOTE_ADDR'] .' was missing HTTP POST data');

  // Check if module is installed
    if (!$settings = reference::module('pm_payson')->settings) {
      throw new Exception('Callback recceived but Payson module is not installed?');
    }

  // Check if module is enabled
    if (empty($settings['status'])) throw new Exception('Callback recceived but Payson module is not enabled');

  // Set gateway
    if ($settings['gateway'] == 'Test') {
      $gateway_url = 'https://test-api.payson.se/1.0/Validate/';
    } else {
      $gateway_url = 'https://api.payson.se/1.0/Validate/';
    }

  // Set request headers
    $headers = [
      'PAYSON-SECURITY-USERID' => $settings['merchant_id'],
      'PAYSON-SECURITY-PASSWORD' => $settings['merchant_key'],
      'PAYSON-APPLICATION-ID' => 'Litecart',
    ];

  // Validate callback data
    $client = new wrap_http();
    $response = $client->call('POST', $gateway_url, file_get_contents('php://input'), $headers);
    if (strtolower($response) != 'verified') {
      parse_str($response, $response);
      error_log(
        'Payson Debug.' . PHP_EOL .':' .
        print_r($response, true) . PHP_EOL .
        print_r($_POST, true) . PHP_EOL .
        PHP_EOL
      );
      throw new Exception('Could not verify callback');
    }

  // Charset compatibility
    mb_convert_variables('UTF-8', language::$selected['charset'], $_POST);

  // Get order from database
    if (!empty($_POST['trackingId'])) {
      $orders_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."orders
        where id = '". database::input($_POST['trackingId']) ."'
        limit 1;"
      );
      $order = database::fetch($orders_query);
    } else if (!empty($_POST['purchaseId'])) {
      $orders_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."orders
        where payment_transaction_id = '". database::input($_POST['purchaseId']) ."'
        limit 1;"
      );
      $order = database::fetch($orders_query);
    }

    if (empty($order)) {
      throw new Exception('A callback for purchaseID '. $_POST['purchaseId'] .' did not match any order in the database');
    }

    $order = new ent_order($order['id']);

  // Set transaction status
    switch (strtolower($_POST['status'])) {
      case 'created':
      case 'pending':
      case 'processing':
        $new_order_status_id = $settings['order_status_id_pending'];
        break;
      case 'completed':
        $new_order_status_id = $settings['order_status_id_completed'];
        break;
      case 'incomplete':
      case 'error':
      case 'expired':
      case 'reversalerror':
      case 'aborted':
        $new_order_status_id = $settings['order_status_id_canceled'];
        break;
      default:
        throw new Exception('Unknown transaction status ('. $_POST['status'] .')');
    }

    if (!empty($_POST['invoiceStatus'])) {
      switch (strtolower($_POST['invoiceStatus'])) {
        case 'pending':
        case 'ordercreated':
        case 'shipped':
          $new_order_status_id = $settings['order_status_id_pending'];
          break;
        case 'canceled':
          $new_order_status_id = $settings['order_status_id_canceled'];
          break;
        case 'done':
          $new_order_status_id = $settings['order_status_id_completed'];
          break;
        case 'credited':
          $new_order_status_id = $settings['order_status_id_credited'];
          break;
        default:
          throw new Exception('Unknown invoice status ('. $_POST['invoiceStatus'] .').');
          break;
      }

    // Update order
      if ($new_order_status_id != $order->data['order_status_id']) {
        $order->data['comments'][]['text'] = '*** [Payson Callback] Transaction status change: '. ucfirst(strtolower($_POST['status']));
        $order->data['order_status_id'] = (int)$new_order_status_id;
        $order->save();
      }
    }

    echo 'OK';

  } catch(Exception $e) {

    error_log('Payson Callback Error: '. $e->getMessage());
    echo 'Error: ' . $e->getMessage();
  }

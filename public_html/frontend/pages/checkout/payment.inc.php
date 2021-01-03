<?php

  header('X-Robots-Tag: noindex');

  $order = &session::$data['order'];

  if (empty($order->data['items'])) return;

  $options = $order->payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer']);

  if (file_get_contents('php://input') != '' && !empty($_POST['payment_option_id'])) {
    list($module_id, $option_id) = explode(':', $_POST['payment_option_id']);

    $order->payment->select($module_id, $option_id, $_POST);
    if (route::$route['page'] != 'order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($order->payment->selected['id'])) {
    $key = $order->payment->selected['module_id'] .':'. $order->payment->selected['option_id'];
    if (!isset($options[$key]) || !empty($options[$key]['error'])) {
      $order->payment->selected = []; // Clear because option is no longer present
    } else {
      $order->payment->select($key); // Reinstate a present option
    }
  }

  if (empty($options)) return;

  if (empty($order->payment->selected)) {
    if ($cheapest = $order->payment->cheapest($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
      $order->payment->select($cheapest['module_id'], $cheapest['option_id']);
    }
  }

/*
// Hide
  if (count($options) == 1
  && empty($options[key($options)]['error'])
  && empty($options[key($options)]['fields'])
  && $options[key($options)]['cost'] == 0) return;
*/

  $box_checkout_payment = new ent_view();

  $box_checkout_payment->snippets = [
    'selected' => $order->payment->selected,
    'options' => $options,
  ];

  echo $box_checkout_payment->stitch('views/box_checkout_payment.inc.php');

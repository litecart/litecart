<?php

  header('X-Robots-Tag: noindex');

  $order = &session::$data['order'];

  if (empty($order->data['items'])) return;

  if (!$options = $order->payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
    return;
  }

  if (!empty($_POST['select_shipping'])) {
    $order->payment->select($_POST['payment_option']['id'], $_POST);

    if (route::$route['page'] != 'order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($order->payment->selected['id'])) {
    if (array_search($order->payment->selected['id'], array_column($options, 'id')) === false) {
      $order->payment->selected = []; // Clear because option is no longer present
    } else {
      $order->payment->select($order->payment->selected['id'], $order->payment->selected['userdata']); // Reinstate a present option
    }
  }

  if (empty($order->payment->selected['id'])) {
    if ($cheapest = $order->payment->cheapest($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
      $order->payment->select($cheapest['id']);
    }
  }

  $box_checkout_payment = new ent_view(FS_DIR_TEMPLATE . 'partials/box_checkout_payment.inc.php');

  $box_checkout_payment->snippets = [
    'selected' => $order->payment->selected,
    'options' => $options,
  ];

  echo $box_checkout_payment;

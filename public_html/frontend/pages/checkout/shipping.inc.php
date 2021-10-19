<?php

  header('X-Robots-Tag: noindex');

  $order = &session::$data['order'];

  if (empty($order->data['items'])) return;

  if (!$options = $order->shipping->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
    return;
  }

  if (file_get_contents('php://input') != '' && !empty($_POST['shipping_option_id'])) {
    list($module_id, $option_id) = explode(':', $_POST['shipping_option_id']);

    $order->shipping->select($module_id, $option_id, $_POST);
    if (route::$route['page'] != 'order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($order->shipping->selected['id'])) {
    $key = $order->payment->selected['module_id'] .':'. $order->payment->selected['option_id'];
    if (!isset($options[$key]) || !empty($options[$key]['error'])) {
      $order->shipping->selected = []; // Clear because option is no longer present
    } else {
      $order->shipping->select($order->payment->selected['module_id'], $order->payment->selected['option_id'], $order->payment->selected['userdata']); // Reinstate a present option
    }
  }

  if (empty($order->shipping->selected)) {
    if ($cheapest = $order->shipping->cheapest($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
      $order->shipping->select($cheapest['module_id'], $cheapest['option_id'], $_POST);
    }
  }

  $box_checkout_shipping = new ent_view('partials/box_checkout_shipping.inc.php');

  $box_checkout_shipping->snippets = [
    'selected' => $order->shipping->selected,
    'options' => $options,
  ];

  echo $box_checkout_shipping;

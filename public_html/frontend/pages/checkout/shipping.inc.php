<?php

  header('X-Robots-Tag: noindex');

  $order = &session::$data['order'];

  if (empty($order->data['items'])) return;

  if (!$options = $order->shipping->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
    return;
  }

  if (!empty($_POST['select_shipping'])) {
    $order->shipping->select($_POST['shipping_option']['id'], $_POST);

    if (!empty($order->shipping->selected['incoterm'])) {
      $order->data['incoterm'] = $order->shipping->selected['incoterm'];
    }

    if (route::$route['page'] != 'order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($order->shipping->selected['id'])) {
    if (array_search($order->shipping->selected['id'], array_column($options, 'id')) === false) {
      $order->shipping->selected = []; // Clear because option is no longer present
    } else {
      $order->shipping->select($order->shipping->selected['id'], $order->shipping->selected['userdata']); // Reinstate a present option
    }
  }

  if (empty($order->shipping->selected['id'])) {
    if ($cheapest = $order->shipping->cheapest($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
      $order->shipping->select($cheapest['id'], $_POST);
    }
  }

  $box_checkout_shipping = new ent_view('partials/box_checkout_shipping.inc.php');

  $box_checkout_shipping->snippets = [
    'selected' => $order->shipping->selected,
    'options' => $options,
  ];

  echo $box_checkout_shipping;

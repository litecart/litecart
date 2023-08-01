<?php

  header('X-Robots-Tag: noindex');

  $shopping_cart = &session::$data['checkout']['shopping_cart'];

  if (empty($shopping_cart->data['items'])) return;

  if (!$options = $shopping_cart->payment->options()) {
    return;
  }

  if (!empty($_POST['select_shipping'])) {
    $shopping_cart->payment->select($_POST['payment_option']['id'], $_POST);

    if (route::$selected['route'] != 'f:order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($shopping_cart->payment->selected['id'])) {
    if (array_search($shopping_cart->payment->selected['id'], array_column($options, 'id')) === false) {
      $shopping_cart->payment->selected = []; // Clear because option is no longer present
    } else {
      $shopping_cart->payment->select($shopping_cart->payment->selected['id'], $shopping_cart->payment->selected['userdata']); // Reinstate a present option
    }
  }

  if (empty($shopping_cart->payment->selected['id'])) {
    if ($cheapest = $shopping_cart->payment->cheapest($shopping_cart->data['items'], $shopping_cart->data['currency_code'], $shopping_cart->data['customer'])) {
      $shopping_cart->payment->select($cheapest['id']);
    }
  }

  $box_checkout_payment = new ent_view();

  $box_checkout_payment->snippets = [
    'selected' => $shopping_cart->payment->selected,
    'options' => $options,
  ];

  echo $box_checkout_payment->render(FS_DIR_TEMPLATE . 'partials/box_checkout_payment.inc.php');

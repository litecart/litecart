<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/partials/box_checkout_payment.inc.php
   */

  header('X-Robots-Tag: noindex');

  if (settings::get('catalog_only_mode')) return;

  $shopping_cart = &session::$data['checkout']['shopping_cart'];

  if (empty($shopping_cart->data['items'])) return;

  $payment = new mod_payment();
  if (!$options = $payment->options($shopping_cart)) {
    return;
  }

  if (!empty($_POST['select_shipping'])) {
    $payment->select($_POST['payment_option']['id'], $_POST);

    if (route::$selected['route'] != 'f:order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($payment->selected['id'])) {
    if (array_search($payment->selected['id'], array_column($options, 'id')) === false) {
      $payment->selected = []; // Clear because option is no longer present
    } else {
      $payment->select($payment->selected['id'], $payment->selected['userdata']); // Reinstate a present option
    }
  }

  if (empty($payment->selected['id'])) {
    if ($cheapest = $payment->cheapest($shopping_cart)) {
      $payment->select($cheapest['id']);
    }
  }

  if (!empty($payment->selected)) {
    $_POST['payment_option'] = $payment->selected;
  }

  $box_checkout_payment = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_checkout_payment.inc.php');

  $box_checkout_payment->snippets = [
    'selected' => $payment->selected,
    'options' => $options,
  ];

  echo $box_checkout_payment->render();

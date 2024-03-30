<?php

  /*!
   * This file contains PHP logic that is separated from the HTML view.
   * Visual changes can be made to the file found in the template folder:
   *
   *   ~/frontend/templates/default/partials/box_checkout_shipping.inc.php
   */

  header('X-Robots-Tag: noindex');

  if (settings::get('catalog_only_mode')) return;

  $shopping_cart = &session::$data['checkout']['shopping_cart'];

  if (empty($shopping_cart->data['items'])) return;

  if (!$options = $shopping_cart->shipping->options()) {
    return;
  }

  if (!empty($_POST['select_shipping'])) {
    $shopping_cart->shipping->select($_POST['shipping_option']['id'], $_POST);

    if (!empty($shopping_cart->shipping->selected['incoterm'])) {
      $shopping_cart->data['incoterm'] = $shopping_cart->shipping->selected['incoterm'];
    }

    if (route::$selected['route'] != 'f:checkout/process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($shopping_cart->shipping->selected['id'])) {
    if (array_search($shopping_cart->shipping->selected['id'], array_column($options, 'id')) === false) {
      $shopping_cart->shipping->selected = []; // Clear because option is no longer present
    } else {
      $shopping_cart->shipping->select($shopping_cart->shipping->selected['id'], $shopping_cart->shipping->selected['userdata']); // Reinstate a present option
    }
  }

  if (empty($shopping_cart->shipping->selected['id'])) {
    if ($cheapest = $shopping_cart->shipping->cheapest($shopping_cart->data['items'], $shopping_cart->data['currency_code'], $shopping_cart->data['customer'])) {
      $shopping_cart->shipping->select($cheapest['id'], $_POST);
    }
  }

  if (!empty($shopping_cart->shipping->selected)) {
    $_POST['shipping_option'] = $shopping_cart->shipping->selected;
  }

  $box_checkout_shipping = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_checkout_shipping.inc.php');

  $box_checkout_shipping->snippets = [
    'selected' => $shopping_cart->shipping->selected,
    'options' => $options,
  ];

  echo $box_checkout_shipping->render();

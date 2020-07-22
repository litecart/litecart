<?php

  header('X-Robots-Tag: noindex');

  $order = &session::$data['order'];

  if (empty($order->data['items'])) return;

  if (empty(customer::$data['country_code'])) return;

  $options = $order->shipping->options($order->data['items'], currency::$selected['code'], customer::$data);

  if (file_get_contents('php://input') != '' && !empty($_POST['shipping'])) {
    list($module_id, $option_id) = explode(':', $_POST['shipping']['option_id']);

    $order->shipping->select($module_id, $option_id, $_POST);
    if (route::$route['page'] != 'order_process') {
      header('Location: '. $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  if (!empty($order->shipping->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $order->shipping->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id]) || !empty($options[$module_id]['options'][$option_id]['error'])) {
      $order->shipping->data['selected'] = array(); // Clear because option is no longer present
    } else {
      $order->shipping->select($module_id, $option_id); // Reinstate a present option
    }
    $order->data['shipping_option'] = $order->shipping->data['selected'];
  }

  if (empty($options)) return;

  if (empty($order->shipping->data['selected'])) {
    if ($cheapest_shipping = $order->shipping->cheapest()) {
      $order->shipping->select($cheapest_shipping['module_id'], $cheapest_shipping['option_id']);
    }
  }

/*
// Hide
  if (count($options) == 1
  && count($options[key($options)]['options']) == 1
  && empty($options[key($options)]['options'][key($options[key($options)]['options'])]['error'])
  && empty($options[key($options)]['options'][key($options[key($options)]['options'])]['fields'])
  && $options[key($options)]['options'][key($options[key($options)]['options'])]['cost'] == 0) return;
*/

  $box_checkout_shipping = new ent_view();

  $box_checkout_shipping->snippets = [
    'selected' => !empty($order->shipping->data['selected']) ? $order->shipping->data['selected'] : array(),
    'options' => $options,
  ];

  echo $box_checkout_shipping->stitch('views/box_checkout_shipping');

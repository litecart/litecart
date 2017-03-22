<?php
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
    header('X-Robots-Tag: noindex');
  }

  if (empty(cart::$items)) return;

  if (empty(customer::$data['country_code'])) return;

  $shipping = new mod_shipping();
  $options = $shipping->options();

  if (file_get_contents('php://input') != '' && !empty($_POST['shipping'])) {
    list($module_id, $option_id) = explode(':', $_POST['shipping']['option_id']);
    $result = $shipping->run('before_select', $module_id, $option_id, $_POST);
    if (!empty($result) && (is_string($result) || !empty($result['error']))) {
      notices::add('errors', is_string($result) ? $result : $result['error']);
    } else {
      $shipping->select($module_id, $option_id, $_POST);
    }
  }

  if (!empty($shipping->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $shipping->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id]) || !empty($options[$module_id]['options'][$option_id]['error'])) {
      $shipping->data['selected'] = array(); // Clear
    } else {
      $shipping->select($module_id, $option_id); // Refresh
    }
  }

  if (empty($options)) return;

  if (empty($shipping->data['selected'])) {
    if ($cheapest_shipping = $shipping->cheapest()) {
      $cheapest_shipping = explode(':', $cheapest_shipping);
      $shipping->select($cheapest_shipping[0], $cheapest_shipping[1]);
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

  $box_checkout_shipping = new view();

  $box_checkout_shipping->snippets = array(
    'selected' => !empty($shipping->data['selected']) ? $shipping->data['selected'] : array(),
    'options' => $options,
  );

  echo $box_checkout_shipping->stitch('views/box_checkout_shipping');

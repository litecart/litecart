<?php
  if (realpath(__FILE__) == realpath($_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'])) {
    header('Content-type: text/html; charset='. language::$selected['charset']);
    document::$layout = 'ajax';
  }
  
  if (empty(cart::$data['total']['physical'])) return;
  
  $shipping = new mod_shipping();
  
  if (empty(customer::$data['country_code'])) return;
  
  if (!empty($_POST['set_shipping'])) {
    list($module_id, $option_id) = explode(':', $_POST['selected_shipping']);
    if ($error = $shipping->run('before_select', $module_id)) {
      notices::add('errors', $error);
    } else {
      $shipping->select($module_id, $option_id);
    }
    header('Location: '. ((FS_DIR_HTTP_ROOT . $_SERVER['SCRIPT_NAME'] == __FILE__) ? $_SERVER['REQUEST_URI'] : document::ilink('checkout')));
    exit;
  }
  
  $options = $shipping->options();
  
  if (!empty($shipping->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $shipping->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id])) {
      $shipping->data['selected'] = array();
    } else {
      $shipping->select($module_id, $option_id); // Refresh
    }
  }
  
  if (empty($options)) return;

  if (empty($shipping->data['selected'])) {
    $cheapest_shipping = explode(':', $shipping->cheapest());
    $shipping->select($cheapest_shipping[0], $cheapest_shipping[1]);
  }
  
  if (count($options) == 1
  && count($options[key($options)]['options']) == 1
  && empty($options[key($options)]['options'][key($options[key($options)]['options'])]['fields'])
  && $options[key($options)]['options'][key($options[key($options)]['options'])]['cost'] == 0) return;
  
  $box_checkout_shipping = new view();
  
  echo $box_checkout_shipping->stitch('box_checkout_shipping');
?>
<?php
  require_once('../includes/app_header.inc.php');
  header('Content-type: application/json; charset='. $system->language->selected['charset']);
  
  $json = array(
    'quantity' => $system->cart->data['total']['items'],
    'value' => $system->settings->get('display_prices_including_tax') ? $system->cart->data['total']['value'] + $system->cart->data['total']['tax'] : $system->cart->data['total']['value'],
    'formatted_value' => $system->settings->get('display_prices_including_tax') ? $system->currency->format($system->cart->data['total']['value'] + $system->cart->data['total']['tax']) : $system->currency->format($system->cart->data['total']['value']),
  );
  
  if (!empty($system->notices->data['warnings'])) {
    $warnings = array_values($system->notices->data['warnings']);
    $json['alert'] = array_shift($warnings);
  }
  
  if (!empty($system->notices->data['errors'])) {
    $errors = array_values($system->notices->data['errors']);
    $json['alert'] = array_shift($errors);
  }
  
  $system->notices->reset();
  
  mb_convert_variables($system->language->selected['charset'], 'UTF-8', $json);
  $json = json_encode($json);
  
  mb_convert_variables('UTF-8', $system->language->selected['charset'], $json);
  echo $json;
  
?>
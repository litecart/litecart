<?php
  require_once('../includes/app_header.inc.php');
  header('Content-type: application/json; charset='. language::$selected['charset']);
  
  $params = array(
    'quantity' => cart::$data['total']['items'],
    'value' => settings::get('display_prices_including_tax') ? cart::$data['total']['value'] + cart::$data['total']['tax'] : cart::$data['total']['value'],
    'formatted_value' => settings::get('display_prices_including_tax') ? currency::format(cart::$data['total']['value'] + cart::$data['total']['tax']) : currency::format(cart::$data['total']['value']),
  );
  
  if (!empty(notices::$data['warnings'])) {
    $warnings = array_values(notices::$data['warnings']);
    $params['alert'] = array_shift($warnings);
  }
  
  if (!empty(notices::$data['errors'])) {
    $errors = array_values(notices::$data['errors']);
    $params['alert'] = array_shift($errors);
  }
  
  notices::reset();
  
  echo '{';
  foreach ($params as $key => $value) {
    if (!empty($use_coma)) echo ',';
    echo '"'.$key.'":"'. $value .'"';
    $use_coma = true;
  }
  echo '}';
  
?>
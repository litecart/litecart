<?php
  header('Content-type: application/json; charset='. language::$selected['charset']);

  $json = array(
    'quantity' => cart::$total['items'],
    'value' => !empty(customer::$data['display_prices_including_tax']) ? cart::$total['value'] + cart::$total['tax'] : cart::$total['value'],
    'formatted_value' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(cart::$total['value'] + cart::$total['tax']) : currency::format(cart::$total['value']),
  );

  if (!empty(notices::$data['warnings'])) {
    $warnings = array_values(notices::$data['warnings']);
    $json['alert'] = array_shift($warnings);
  }

  if (!empty(notices::$data['errors'])) {
    $errors = array_values(notices::$data['errors']);
    $json['alert'] = array_shift($errors);
  }

  notices::reset();

  mb_convert_variables(language::$selected['charset'], 'UTF-8', $json);
  $json = json_encode($json);

  mb_convert_variables('UTF-8', language::$selected['charset'], $json);
  echo $json;

  exit;
?>
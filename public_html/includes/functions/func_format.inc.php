<?php
  
  function format_address($address) {
    
    $country_query = database::query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where iso_code_2 = '". database::input($address['country_code']) ."'
      limit 1;"
    );
    $country = database::fetch($country_query);
    if (empty($country)) trigger_error('Invalid country code for address format', E_USER_WARNING);
    
    if (isset($address['zone_code'])) {
      $zones_query = database::query(
        "select * from ". DB_TABLE_ZONES ."
        where country_code = '". database::input($country['iso_code_2']) ."'
        and code = '". database::input($address['zone_code']) ."'
        limit 1;"
      );
      $zone = database::fetch($zones_query);
    }
    
    $translation_map = array(
      '%company' => !empty($address['company']) ? $address['company'] : '',
      '%firstname' => !empty($address['firstname']) ? $address['firstname'] : '',
      '%lastname' => !empty($address['lastname']) ? $address['lastname'] : '',
      '%address1' => !empty($address['address1']) ? $address['address1'] : '',
      '%address2' => !empty($address['address2']) ? $address['address2'] : '',
      '%city' => !empty($address['city']) ? $address['city'] : '',
      '%postcode' => !empty($address['postcode']) ? $address['postcode'] : '',
      '%country_code' => $country['iso_code_2'],
      '%country_name' => $country['name'],
      '%zone_code' => !empty($zone['code']) ? $zone['code'] : '',
      '%zone_name' => !empty($zone['name']) ? $zone['name'] : '',
    );
    
    $output = $country['address_format'] ? $country['address_format'] : settings::get('default_address_format');
    
    foreach ($translation_map as $search => $replace) {
      $output = str_replace($search, $replace, $output);
    }
    
    while (strpos($output, "\r\n\r\n") !== false) $output = str_replace("\r\n\r\n", "\r\n", $output);
    while (strpos($output, "\r\r") !== false) $output = str_replace("\r\r", "\n\n", $output);
    while (strpos($output, "\n\n") !== false) $output = str_replace("\n\n", "\n\n", $output);
    
    $output = trim($output);
    
    return $output;
  }

?>
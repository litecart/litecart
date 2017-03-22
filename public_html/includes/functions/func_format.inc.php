<?php

  function format_address($address) {

    $country_query = database::query(
      "select * from ". DB_TABLE_COUNTRIES ."
      where iso_code_2 = '". database::input($address['country_code']) ."'
      limit 1;"
    );
    $country = database::fetch($country_query);

    if (empty($country)) {
      trigger_error('Invalid country code for address format', E_USER_WARNING);
      return;
    }

    return reference::country($address['country_code'])->format_address($address);
  }

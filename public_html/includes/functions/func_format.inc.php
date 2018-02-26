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

  function format_regex_code($string) {

    $string = preg_replace('#[ -\./]+#', '', $string);

    if (mb_strlen($string) > 1) {

      do {
        $c = mb_strlen($string);
        $parts[] = mb_substr($string, 0, 1);
        $string = mb_substr($string, 1);
      } while(!empty($string));

    } else {
      $parts = array($string);
    }

    $string = implode('([ -\./]+)?', $parts);

    return $string;
  }
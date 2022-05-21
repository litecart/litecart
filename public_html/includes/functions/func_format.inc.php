<?php

  function format_address($address) {

    $country = database::fetch(database::query(
      "select * from ". DB_TABLE_PREFIX ."countries
      where iso_code_2 = '". database::input($address['country_code']) ."'
      limit 1;"
    ));

    if (!$country) {
      trigger_error('Invalid country code for address format', E_USER_WARNING);
      return;
    }

    return reference::country($address['country_code'])->format_address($address);
  }

  function format_mysql_fulltext($string) {
    $string = strip_tags($string);
    return preg_replace('#[+\-<>\(\)~*\"@;]+#', ' ', $string);
  }

  function format_regex_code($string) {

    $string = strip_tags($string);

    if (strlen($string) > 24 || preg_match('#[^0-9a-zA-Z \-\./]#', $string)) {
      return addcslashes(preg_quote($string, "'"), '&<>');
    }

    $string = preg_replace('#[ -\./]+#', '', $string);

    $parts = preg_split('#(.)#u', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    foreach ($parts as $key => $char) {
      $parts[$key] = addcslashes(preg_quote($char, "'"), '&<>');
    }

    $string = implode('([ \-\./]+)?', $parts);

    return $string;
  }

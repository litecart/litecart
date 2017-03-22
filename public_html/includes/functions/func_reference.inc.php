<?php

  function reference_in_geo_zone($geo_zone_id, $country_code, $zone_code='') {

    $zones_to_geo_zones_query = database::query(
      "select id from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
      where geo_zone_id = ". (int)$geo_zone_id ."
      and (country_code = '' or country_code = '". database::input($country_code) ."')
      ". ((!empty($zone_code)) ? "and (zone_code = '' or zone_code = '". database::input($zone_code) ."')" : "") ."
      limit 1;"
    );

    return (database::num_rows($zones_to_geo_zones_query) > 0) ? true : false;
  }

  function reference_get_manufacturer_name($manufacturer_id) {
    trigger_error(__METHOD__.' is deprecated. Use instead reference::manufacturer($id)->name', E_USER_DEPRECATED);
    return reference::manufacturer($manufacturer_id)->name;
  }

  function reference_get_country_name($country_code) {
    trigger_error(__METHOD__.' is deprecated. Use instead reference::country($code)->name', E_USER_DEPRECATED);
    return reference::country($country_code)->name;
  }

  function reference_get_zone_name($country_code, $zone_code) {
    trigger_error(__METHOD__.' is deprecated. Use instead reference::country($code)->zones[$zone_code][\'name\']', E_USER_DEPRECATED);
    return isset(reference::country($country_code)->zones[$zone_code]) ? reference::country($country_code)->zones[$zone_code]['name'] : null;
  }

  function reference_country_num_zones($country_code) {
    trigger_error(__METHOD__.' is deprecated. Use instead count(reference::country($code)->zones)', E_USER_DEPRECATED);
    return count(reference::country($country_code)->zones);
  }

  function reference_verify_zone_code($country_code, $code) {
    trigger_error(__METHOD__.' is deprecated. Use instead isset(reference::country($code)->zones[$zone_code])', E_USER_DEPRECATED);
    return isset(reference::country($country_code)->zones[$code]);
  }

  function reference_get_postcode_required($country_code) {
    trigger_error(__METHOD__.' is deprecated. Use instead reference::country($code)->postcode_format', E_USER_DEPRECATED);
    return !empty(reference::country($country_code)->postcode_format);
  }

  function reference_get_phone_country_code($country_code) {
    trigger_error(__METHOD__.' is deprecated. Use instead reference::country($code)->phone_code', E_USER_DEPRECATED);
    return '+'. reference::country($country_code)->phone_code;
  }

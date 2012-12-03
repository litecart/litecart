<?php

  function reference_in_geo_zone($geo_zone_id, $country_code, $zone_code='') {
    global $system;
    
    $zones_to_geo_zones_query = $system->database->query(
      "select id from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
      where geo_zone_id = '". (int)$geo_zone_id ."'
      and (country_code = '' or country_code = '". $system->database->input($country_code) ."')
      ". ((!empty($zone_code)) ? "and (country_code = '' or country_code = '". $system->database->input($zone_code) ."')" : "") ."
      limit 1;"
    );
    
    return ($system->database->num_rows($zones_to_geo_zones_query) > 0) ? true : false;
  }

  function reference_get_manufacturer_name($manufacturer_id) {
    global $system;
    
    $manufacturer_query = $system->database->query(
      "select name from ". DB_TABLE_MANUFACTURERS ."
      where id = '". (int)$manufacturer_id ."'
      limit 1;"
    );
    $manufacturer = $system->database->fetch($manufacturer_query);
    
    return isset($manufacturer['name']) ? $manufacturer['name'] : '';
  }

  function reference_get_country_name($code) {
    global $system;
    
    $country_query = $system->database->query(
      "select name from ". DB_TABLE_COUNTRIES ."
      where iso_code_2 = '". $system->database->input($code) ."'
      limit 1;"
    );
    
    $country = $system->database->fetch($country_query);
    
    return isset($country['name']) ? $country['name'] : '';
  }
  
  function reference_get_zone_name($country_code, $code) {
    global $system;
    
    $zones_query = $system->database->query(
      "select name from ". DB_TABLE_ZONES ."
      where country_code = '". $system->database->input($country_code) ."'
      and code = '". $system->database->input($code) ."'
      limit 1;"
    );
    
    $zone = $system->database->fetch($zones_query);
    
    return isset($zone['name']) ? $zone['name'] : '';
  }
  
  function reference_country_num_zones($country_code) {
    global $system;
    
    $zones_query = $system->database->query(
      "select id from ". DB_TABLE_ZONES ."
      where country_code = '". $system->database->input($country_code) ."';"
    );
    
    return $system->database->num_rows($zones_query);
  }
  
  function reference_verify_zone_code($country_code, $code) {
    global $system;
    
    $zones_query = $system->database->query(
      "select id from ". DB_TABLE_ZONES ."
      where country_code = '". $system->database->input($country_code) ."'
      and code = '". $system->database->input($code) ."'
      limit 1;"
    );
    
    return $system->database->num_rows($zones_query) ? true : false;
  }
  
  function reference_get_postcode_required($country_code) {
    global $system;
    
    $country_query = $system->database->query(
      "select postcode_required from ". DB_TABLE_COUNTRIES ."
      where iso_code_2 = '". $system->database->input($country_code) ."'
      limit 1;"
    );
    
    $country = $system->database->fetch($country_query);
    
    return (!empty($country['postcode_required'])) ? true : false;
  }
  
?>
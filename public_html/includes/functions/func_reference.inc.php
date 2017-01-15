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

?>
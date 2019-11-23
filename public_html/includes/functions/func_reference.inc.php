<?php

  function reference_in_geo_zone($geo_zone_id, $country_code, $zone_code='') {
    //trigger_error(__METHOD__." is deprecated. Use instead reference::country('$country_code')->in_geo_zones('$zone_code')", E_USER_DEPRECATED);
    return reference::country($country_code)->in_geo_zone($zone_code, $geo_zone_id);
  }

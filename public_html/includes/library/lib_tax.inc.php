<?php
  
  class tax {
    private static $_cache = array();
    
    public static function construct() {
    }
    
    //public static function load_dependencies() {
    //}
    
    //public static function initiate() {
    //}
    
    //public static function startup() {
    //}
    
    //public static function before_capture() {
    //}
    
    //public static function after_capture() {
    //}
    
    //public static function prepare_output() {
    //}
    
    //public static function before_output() {
    //}
    
    //public static function shutdown() {
    //}
    
    ######################################################################
    
    public static function calculate($value, $tax_class_id, $calculate=null, $country_code=null, $zone_code=null) {
      
      if ($calculate === null) $calculate = settings::get('display_prices_including_tax') ? true : false;
      
      if ($calculate) {
        return $value + self::get_tax($value, $tax_class_id, $country_code, $zone_code);
      } else {
        return $value;
      }
    }
    
    public static function get_tax($value, $tax_class_id, $country_code=null, $zone_code=null) {
      
      if ($value == 0) return 0;
      
      if ($tax_class_id == 0) return 0;
      
      $tax = 0;
      
      $tax_rates = self::get_rates($tax_class_id, $country_code, $zone_code);
      
      foreach ($tax_rates as $tax_rate) {
        switch($tax_rate['type']) {
          case 'fixed':
            $tax += $tax_rate['rate'];
            break;
          case 'percent':
            $tax += ($value / 100 * $tax_rate['rate']);
            break;
        }
      }
      
      return $tax;
    }
    
    public static function get_tax_by_rate($value, $tax_class_id, $country_code=null, $zone_code=null) {
      
      if ($value == 0) return 0;
      
      $tax_rates = array();
      
      foreach (self::get_rates($tax_class_id, $country_code, $zone_code) as $tax_rate) {
        if (!isset($tax_rates[$tax_rate['id']])) {
          $tax_rates[$tax_rate['id']] = array(
            'id' => $tax_rate['id'],
            'name' => $tax_rate['name'],
            'tax' => 0,
          );
        }
        switch($tax_rate['type']) {
          case 'fixed':
            $tax_rates[$tax_rate['id']]['tax'] += $tax_rate['rate'];
            break;
          case 'percent':
            $tax_rates[$tax_rate['id']]['tax'] += ($value / 100 * $tax_rate['rate']);
            break;
        }
      }
      
      return $tax_rates;
    }
    
    public static function get_rates($tax_class_id, $country_code=null, $zone_code=null) {
      
      if (empty($tax_class_id)) return array();
      
      $tax_rates = array();
      
      if ($country_code === null) {
        $country_code = (!empty(customer::$data['country_code'])) ? customer::$data['country_code'] : settings::get('default_country_code');
      } else if ($zone_code === null) {
        $zone_code = (!empty(customer::$data['zone_code'])) ? customer::$data['zone_code'] : settings::get('default_zone_code');
      }
      
      if (isset(self::$_cache['rates'][$tax_class_id][$country_code.':'.$zone_code])) return self::$_cache['rates'][$tax_class_id][$country_code.':'.$zone_code];
      
      $tax_rates_query = database::query(
        "select tr.id, tr.name, tr.type, tr.rate, tr.customer_type, tr.tax_id_rule
        from ". DB_TABLE_TAX_RATES ." tr
        left join ". DB_TABLE_GEO_ZONES . " gz on (gz.id = tr.geo_zone_id)
        left join ". DB_TABLE_ZONES_TO_GEO_ZONES ." z2gz on (z2gz.geo_zone_id = tr.geo_zone_id)
        where tr.tax_class_id = '" . (int)$tax_class_id . "'
        and z2gz.country_code = '" . database::input($country_code) . "'
        and (z2gz.zone_code = '' or z2gz.zone_code = '". database::input($zone_code) ."');"
      );
      
      while ($row=database::fetch($tax_rates_query)) {
        if ($row['customer_type'] == 'individuals' && !empty(customer::$data['company'])) continue;
        if ($row['customer_type'] == 'companies' && empty(customer::$data['company'])) continue;
        if ($row['tax_id_rule'] == 'without' && !empty(customer::$data['tax_id'])) continue;
        if ($row['tax_id_rule'] == 'with' && empty(customer::$data['tax_id'])) continue;
        $tax_rates[$row['id']] = $row;
      }
      
      self::$_cache['rates'][$tax_class_id][$country_code.':'.$zone_code] = $tax_rates;
      
      return $tax_rates;
    }
    
    public static function get_class_name($tax_class_id) {
      
      $tax_class_query = database::query(
        "select name from ". DB_TABLE_TAX_CLASSES ."
        where id = '" . (int)$tax_class_id . "'
        limit 1;"
      );
      $tax_class = database::fetch($tax_class_query);
      
      if (isset($tax_class['name'])) return $tax_class['name'];
      
      return false;
    }
    
    public static function get_rate_name($tax_rate_id) {
      
      $tax_rates_query = database::query(
        "select name from ". DB_TABLE_TAX_RATES ."
        where id = '" . (int)$tax_rate_id . "'
        limit 1;"
      );
      $tax_rate = database::fetch($tax_rates_query);
      
      if (isset($tax_rate['name'])) return $tax_rate['name'];
      
      return false;
    }
  }
  
?>
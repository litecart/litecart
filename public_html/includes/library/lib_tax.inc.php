<?php
  
  class lib_tax {
    private $cache = array();
    
    public function __construct() {
    }
    
    //public function load_dependencies() {
    //}
    
    //public function initiate() {
    //}
    
    //public function startup() {
    //}
    
    //public function before_capture() {
    //}
    
    //public function after_capture() {
    //}
    
    //public function prepare_output() {
    //}
    
    //public function before_output() {
    //}
    
    //public function shutdown() {
    //}
    
    ######################################################################
    
    public function calculate($value, $tax_class_id, $calculate=null, $country_code=null, $zone_code=null) {
      
      if ($calculate === null) $calculate = $GLOBALS['system']->settings->get('display_prices_including_tax') ? true : false;
      
      if ($calculate) {
        return $value + $this->get_tax($value, $tax_class_id, $country_code, $zone_code);
      } else {
        return $value;
      }
    }
    
    public function get_tax($value, $tax_class_id, $country_code=null, $zone_code=null) {
      
      if ($value == 0) return 0;
      
      if ($tax_class_id == 0) return 0;
      
      $tax = 0;
      
      $tax_rates = $this->get_rates($tax_class_id, $country_code, $zone_code);
      
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
    
    public function get_tax_by_rate($value, $tax_class_id, $country_code=null, $zone_code=null) {
      
      if ($value == 0) return 0;
      
      $tax_rates = array();
      
      foreach ($this->get_rates($tax_class_id, $country_code, $zone_code) as $tax_rate) {
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
    
    public function get_rates($tax_class_id, $country_code=null, $zone_code=null) {
      
      if (empty($tax_class_id)) return array();
      
      $tax_rates = array();
      
      if ($country_code === null) {
        $country_code = (!empty($GLOBALS['system']->customer->data['country_code'])) ? $GLOBALS['system']->customer->data['country_code'] : $GLOBALS['system']->settings->get('default_country_code');
      } else if ($zone_code === null) {
        $zone_code = (!empty($GLOBALS['system']->customer->data['zone_code'])) ? $GLOBALS['system']->customer->data['zone_code'] : $GLOBALS['system']->settings->get('default_zone_code');
      }
      
      if (isset($this->cache['rates'][$tax_class_id][$country_code.':'.$zone_code])) return $this->cache['rates'][$tax_class_id][$country_code.':'.$zone_code];
      
      $tax_rates_query = $GLOBALS['system']->database->query(
        "select tr.id, tr.name, tr.type, tr.rate, tr.customer_type, tr.tax_id_rule
        from ". DB_TABLE_TAX_RATES ." tr
        left join ". DB_TABLE_GEO_ZONES . " gz on (gz.id = tr.geo_zone_id)
        left join ". DB_TABLE_ZONES_TO_GEO_ZONES ." z2gz on (z2gz.geo_zone_id = tr.geo_zone_id)
        where tr.tax_class_id = '" . (int)$tax_class_id . "'
        and z2gz.country_code = '" . $GLOBALS['system']->database->input($country_code) . "'
        and (z2gz.zone_code = '' or z2gz.zone_code = '". $GLOBALS['system']->database->input($zone_code) ."');"
      );
      
      while ($row=$GLOBALS['system']->database->fetch($tax_rates_query)) {
        if ($row['customer_type'] == 'individuals' && !empty($GLOBALS['system']->customer->data['company'])) continue;
        if ($row['customer_type'] == 'companies' && empty($GLOBALS['system']->customer->data['company'])) continue;
        if ($row['tax_id_rule'] == 'without' && !empty($GLOBALS['system']->customer->data['tax_id'])) continue;
        if ($row['tax_id_rule'] == 'with' && empty($GLOBALS['system']->customer->data['tax_id'])) continue;
        $tax_rates[$row['id']] = $row;
      }
      
      $this->cache['rates'][$tax_class_id][$country_code.':'.$zone_code] = $tax_rates;
      
      return $tax_rates;
    }
    
    public function get_class_name($tax_class_id) {
      
      $tax_class_query = $GLOBALS['system']->database->query(
        "select name from ". DB_TABLE_TAX_CLASSES ."
        where id = '" . (int)$tax_class_id . "'
        limit 1;"
      );
      $tax_class = $GLOBALS['system']->database->fetch($tax_class_query);
      
      if (isset($tax_class['name'])) return $tax_class['name'];
      
      return false;
    }
    
    public function get_rate_name($tax_rate_id) {
      
      $tax_rates_query = $GLOBALS['system']->database->query(
        "select name from ". DB_TABLE_TAX_RATES ."
        where id = '" . (int)$tax_rate_id . "'
        limit 1;"
      );
      $tax_rate = $GLOBALS['system']->database->fetch($tax_rates_query);
      
      if (isset($tax_rate['name'])) return $tax_rate['name'];
      
      return false;
    }
  }
  
?>
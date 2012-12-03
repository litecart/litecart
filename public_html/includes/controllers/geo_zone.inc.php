<?php

  class ctrl_geo_zone {
    public $data = array();
    
    public function __construct($geo_zone_id=null) {
      global $system;
      
      $this->system = &$system;
      
      if ($geo_zone_id !== null) $this->load($geo_zone_id);
    }
    
    public function load($geo_zone_id) {
      $geo_zone_query = $this->system->database->query(
        "select * from ". DB_TABLE_GEO_ZONES ."
        where id = '". (int)$geo_zone_id ."'
        limit 1;"
      );
      $this->data = $this->system->database->fetch($geo_zone_query);
      if (empty($this->data)) trigger_error('Could not find tax class ('. $geo_zone_id .') in database.', E_USER_ERROR);
      
      $zones_to_geo_zones_query = $this->system->database->query(
        "select z2gz.*, c.name as country_name, z.name as zone_name from ". DB_TABLE_ZONES_TO_GEO_ZONES ." z2gz
        left join ". DB_TABLE_COUNTRIES ." c on (c.iso_code_2 = z2gz.country_code)
        left join ". DB_TABLE_ZONES ." z on (z.code = z2gz.zone_code)
        where geo_zone_id = '". (int)$geo_zone_id ."'
        order by c.name, z.name;"
      );
      
      $this->data['zones'] = array();
      while ($zone = $this->system->database->fetch($zones_to_geo_zones_query)) {
        $this->data['zones'][$zone['id']] = $zone;
        if (empty($zone['zone_code'])) $this->data['zones'][$zone['id']]['zone_name'] = '-- '. $this->system->language->translate('title_all_zones', 'All Zones') .' --';
      }
    }
    
    public function save() {
      
      if (empty($this->data['id'])) {
        $this->system->database->query(
          "insert into ". DB_TABLE_GEO_ZONES ."
          (date_created)
          values ('". $this->system->database->input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = $this->system->database->insert_id();
      }
      
      $this->system->database->query(
        "update ". DB_TABLE_GEO_ZONES ."
        set
          name = '". $this->system->database->input($this->data['name']) ."',
          description = '". $this->system->database->input($this->data['description']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
        where geo_zone_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['zones'])) ."');"
      );
      
      if (!empty($this->data['zones'])) {
        foreach ($this->data['zones'] as $zone) {
          if (empty($zone['id'])) {
            $this->system->database->query(
              "insert into ". DB_TABLE_ZONES_TO_GEO_ZONES ."
              (geo_zone_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $zone['id'] = $this->system->database->insert_id();
          }
          $this->system->database->query(
            "update ". DB_TABLE_ZONES_TO_GEO_ZONES ." 
            set country_code = '". $this->system->database->input($zone['country_code']) ."',
            zone_code = '". $this->system->database->input($zone['zone_code']) ."',
            date_updated =  '". date('Y-m-d H:i:s') ."'
            where geo_zone_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$zone['id'] ."'
            limit 1;"
          );
        }
      }
      
      $this->system->cache->set_breakpoint();
    }
    
    public function delete() {
    
      $this->system->database->query(
        "delete from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
        where geo_zone_id = '". (int)$this->data['id'] ."';"
      );
      
      $this->system->database->query(
        "delete from ". DB_TABLE_GEO_ZONES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      $this->data['id'] = null;
      
      $this->system->cache->set_breakpoint();
    }
  }

?>
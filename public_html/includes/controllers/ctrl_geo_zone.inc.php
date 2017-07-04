<?php

  class ctrl_geo_zone {
    public $data;

    public function __construct($geo_zone_id=null) {

      if ($geo_zone_id !== null) {
        $this->load((int)$geo_zone_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = array();

      $fields_query = database::query(
        "show fields from ". DB_TABLE_GEO_ZONES .";"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->data['zones'] = array();
    }

    public function load($geo_zone_id) {

      $this->reset();

      $geo_zone_query = database::query(
        "select * from ". DB_TABLE_GEO_ZONES ."
        where id = '". (int)$geo_zone_id ."'
        limit 1;"
      );

      if ($geo_zone = database::fetch($geo_zone_query)) {
        $this->data = array_replace($this->data, array_intersect_key($geo_zone, $this->data));
      } else {
        trigger_error('Could not find geo zone (ID: '. (int)$geo_zone_id .') in database.', E_USER_ERROR);
      }

      $zones_to_geo_zones_query = database::query(
        "select z2gz.*, c.name as country_name, z.name as zone_name from ". DB_TABLE_ZONES_TO_GEO_ZONES ." z2gz
        left join ". DB_TABLE_COUNTRIES ." c on (c.iso_code_2 = z2gz.country_code)
        left join ". DB_TABLE_ZONES ." z on (z.code = z2gz.zone_code)
        where geo_zone_id = '". (int)$geo_zone_id ."'
        order by c.name, z.name;"
      );

      $this->data['zones'] = array();
      while ($zone = database::fetch($zones_to_geo_zones_query)) {
        $this->data['zones'][$zone['id']] = $zone;
        if (empty($zone['zone_code'])) $this->data['zones'][$zone['id']]['zone_name'] = '-- '. language::translate('title_all_zones', 'All Zones') .' --';
      }
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_GEO_ZONES ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_GEO_ZONES ."
        set
          code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
        where geo_zone_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", array_column($this->data['zones'], 'id')) ."');"
      );

      if (!empty($this->data['zones'])) {
        foreach ($this->data['zones'] as $zone) {
          if (empty($zone['id'])) {
            database::query(
              "insert into ". DB_TABLE_ZONES_TO_GEO_ZONES ."
              (geo_zone_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $zone['id'] = database::insert_id();
          }
          database::query(
            "update ". DB_TABLE_ZONES_TO_GEO_ZONES ."
            set country_code = '". database::input($zone['country_code']) ."',
            zone_code = '". (isset($zone['zone_code']) ? database::input($zone['zone_code']) : '') ."',
            date_updated =  '". date('Y-m-d H:i:s') ."'
            where geo_zone_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$zone['id'] ."'
            limit 1;"
          );
        }
      }

      cache::clear_cache('geo_zones');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_ZONES_TO_GEO_ZONES ."
        where geo_zone_id = '". (int)$this->data['id'] ."';"
      );

      database::query(
        "delete from ". DB_TABLE_GEO_ZONES ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );

      cache::clear_cache('geo_zones');

      $this->data['id'] = null;
    }
  }

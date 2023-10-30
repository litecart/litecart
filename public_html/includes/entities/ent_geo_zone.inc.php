<?php

  class ent_geo_zone {
    public $data;
    public $previous;

    public function __construct($geo_zone_id=null) {

      if (!empty($geo_zone_id)) {
        $this->load($geo_zone_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."geo_zones;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $this->data['zones'] = [];

      $this->previous = $this->data;
    }

    public function load($geo_zone_id) {

      if (!preg_match('#^[0-9]+$#', $geo_zone_id)) {
        throw new Exception('Invalid geo zone (ID: '. $geo_zone_id .')');
      }

      $this->reset();

      $geo_zone = database::query(
        "select * from ". DB_TABLE_PREFIX ."geo_zones
        where id = ". (int)$geo_zone_id ."
        limit 1;"
      )->fetch();

      if ($geo_zone) {
        $this->data = array_replace($this->data, array_intersect_key($geo_zone, $this->data));
      } else {
        throw new Exception('Could not find geo zone (ID: '. (int)$geo_zone_id .') in database.');
      }

      $zones_to_geo_zones_query = database::query(
        "select z2gz.*, c.name as country_name, z.name as zone_name from ". DB_TABLE_PREFIX ."zones_to_geo_zones z2gz
        left join ". DB_TABLE_PREFIX ."countries c on (c.iso_code_2 = z2gz.country_code)
        left join ". DB_TABLE_PREFIX ."zones z on (z.code = z2gz.zone_code)
        where geo_zone_id = ". (int)$geo_zone_id ."
        order by c.name, z.name;"
      );

      $this->data['zones'] = [];
      while ($zone = database::fetch($zones_to_geo_zones_query)) {
        if (empty($zone['zone_code'])) {
          $zone['zone_name'] = '-- '. language::translate('title_all_zones', 'All Zones') .' --';
        }
        $this->data['zones'][] = $zone;
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."geo_zones
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."geo_zones
        set code = '". database::input($this->data['code']) ."',
          name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      database::query(
        "delete from ". DB_TABLE_PREFIX ."zones_to_geo_zones
        where geo_zone_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['zones'], 'id')) ."');"
      );

      if (!empty($this->data['zones'])) {
        foreach ($this->data['zones'] as $key => $zone) {

          if (empty($zone['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."zones_to_geo_zones
              (geo_zone_id, country_code, zone_code, city, date_created)
              values (". (int)$this->data['id'] .", '". database::input($zone['country_code']) ."', '". database::input($zone['zone_code']) ."', '". database::input($zone['city']) ."', '". ($this->data['zones'][$key]['date_created'] = date('Y-m-d H:i:s')) ."');"
            );
            $this->data['zones'][$key]['id'] = $zone['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_TABLE_PREFIX ."zones_to_geo_zones
            set country_code = '". database::input($zone['country_code']) ."',
            zone_code = '". database::input($zone['zone_code']) ."',
            city = '". database::input($zone['city']) ."',
            date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
            where geo_zone_id = ". (int)$this->data['id'] ."
            and id = ". (int)$zone['id'] ."
            limit 1;"
          );
        }
      }

      $this->previous = $this->data;

      cache::clear_cache('geo_zones');
    }

    public function delete() {

      database::query(
        "delete gz, ztgz
        from ". DB_TABLE_PREFIX ."geo_zones gz
        left join ". DB_TABLE_PREFIX ."zones_to_geo_zones ztgz on (ztgz.geo_zone_id = gz.id)
        where gz.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('geo_zones');
    }
  }

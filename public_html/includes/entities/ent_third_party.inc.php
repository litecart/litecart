<?php

  class ent_third_party {
    public $data;
    public $previous;

    public function __construct($third_party_id=null) {

      if ($third_party_id !== null) {
        $this->load($third_party_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      database::query(
        "show fields from ". DB_TABLE_PREFIX ."third_parties;"
      )->each(function($field) {
				$this->data[$field['Field']] = database::create_variable($field['Type']);
			});

      database::query(
        "show fields from ". DB_TABLE_PREFIX ."third_parties_info;"
      )->each(function($field) {

        if (in_array($field['Field'], ['id', 'third_party_id', 'language_code'])) return;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field['Type']);
        }
      });

      $this->data['privacy_classes'] = [];

      $this->previous = $this->data;
    }

    public function load($third_party_id) {

      if (!preg_match('#^[0-9]+$#', $third_party_id)) {
        throw new Exception('Invalid third party (ID: '. $third_party_id .')');
      }

      $this->reset();

      $third_party = database::query(
        "select * from ". DB_TABLE_PREFIX ."third_parties
        where id = ". (int)$third_party_id ."
        limit 1;"
      )->fetch();

      if ($third_party) {
        $this->data = array_replace($this->data, array_intersect_key($third_party, $this->data));
      } else {
        throw new Exception('Could not find third party (ID: '. (int)$third_party_id .') in database.');
      }

      database::query(
        "select * from ". DB_TABLE_PREFIX ."third_parties_info
        where third_party_id = ". (int)$third_party_id .";"
      )->each(function($info) {
				foreach ($info as $key => $value) {
					if (in_array($key, ['id', 'third_party_id', 'language_code'])) return;
					$this->data[$key][$info['language_code']] = $value;
				}
			});

      $this->data['privacy_classes'] = preg_split('#\s*,\s*#', $this->data['privacy_classes'], -1, PREG_SPLIT_NO_EMPTY);

      $this->previous = $this->data;
    }

    public function save() {

      if (!$this->data['id']) {

        database::query(
          "insert into ". DB_TABLE_PREFIX ."third_parties
          (name, date_created)
          values ('". database::input($this->data['name']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );

        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."third_parties set
        status = ". (int)$this->data['status'] .",
        privacy_classes = '". implode(',', database::input($this->data['privacy_classes'])) ."',
        name = '". database::input($this->data['name']) ."',
        country_code = '". database::input($this->data['country_code']) ."',
        homepage = '". database::input($this->data['homepage']) ."',
        cookie_policy_url = '". database::input($this->data['cookie_policy_url']) ."',
        privacy_policy_url = '". database::input($this->data['privacy_policy_url']) ."',
        opt_out_url = '". database::input($this->data['opt_out_url']) ."',
        do_not_sell_url = '". database::input($this->data['opt_out_url']) ."',
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {

        if (!database::query(
          "select * from ". DB_TABLE_PREFIX ."third_parties_info
          where third_party_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        )->num_rows) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."third_parties_info
            (third_party_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."third_parties_info
          set description = '". database::input($this->data['description'][$language_code], true) ."',
            collected_data = '". database::input($this->data['collected_data'][$language_code]) ."',
            purposes = '". database::input($this->data['purposes'][$language_code]) ."'
          where third_party_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('third_parties');
    }

    public function delete() {

      database::query(
        "delete tp, tpi
				from ". DB_TABLE_PREFIX ."third_parties tp
        left join ". DB_TABLE_PREFIX ."third_parties_info tpi on (tpi.third_party_id = tp.id)
        where tp.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('third_parties');
    }
  }

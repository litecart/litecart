<?php

  class ent_currency {
    public $data;
    public $previous;

    public function __construct($currency_code=null) {

      if ($currency_code !== null) {
        $this->load($currency_code);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."currencies;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $this->previous = $this->data;
    }

    public function load($currency_code) {

      if (!preg_match('#^([0-9]{1,3}|[A-Z]{3}|[a-z A-Z]{4,})$#', $currency_code)) throw new Exception('Invalid currency ('. $currency_code .')');

      $this->reset();

      $currency_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."currencies
        ". (preg_match('#^[0-9]{1,2}$#', $currency_code) ? "where id = '". (int)$currency_code ."'" : "") ."
        ". (preg_match('#^[0-9]{3}$#', $currency_code) ? "where number = '". database::input($currency_code) ."'" : "") ."
        ". (preg_match('#^[A-Z]{3}$#', $currency_code) ? "where code = '". database::input($currency_code) ."'" : "") ."
        ". (preg_match('#^[a-z A-Z]{4,}$#', $currency_code) ? "where name like '". addcslashes(database::input($currency_code), '%_') ."'" : "") ."
        limit 1;"
      );

      if ($currency = database::fetch($currency_query)) {
        $this->data = array_replace($this->data, array_intersect_key($currency, $this->data));
      } else {
        throw new Exception('Could not find currency ('. functions::escape_html($currency_code) .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['status']) && $this->data['code'] == settings::get('store_currency_code')) {
        throw new Exception(language::translate('error_cannot_disable_store_currency', 'You must change the store currency before disabling it.'));
      }

      if (empty($this->data['status']) && $this->data['code'] == settings::get('default_currency_code')) {
        throw new Exception(language::translate('error_cannot_disable_default_currency', 'You must change the default currency before disabling it.'));
      }

      $currency_query = database::query(
        "select id from ". DB_TABLE_PREFIX ."currencies
        where (
          code = '". database::input($this->data['code']) ."'
          ". (!empty($this->data['number']) ? "or number = '". database::input($this->data['number']) ."'" : "") ."
        )
        ". (!empty($this->data['id']) ? "and id != ". $this->data['id'] : "") ."
        limit 1;"
      );

      if (database::num_rows($currency_query)) {
        throw new Exception(language::translate('error_currency_conflict', 'The currency conflicts another currency in the database'));
      }

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."currencies
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."currencies
        set
          status = ". (int)$this->data['status'] .",
          code = '". database::input($this->data['code']) ."',
          number = '". database::input($this->data['number']) ."',
          name = '". database::input($this->data['name']) ."',
          value = '". database::input($this->data['value']) ."',
          prefix = '". database::input($this->data['prefix'], false, false) ."',
          suffix = '". database::input($this->data['suffix'], false, false) ."',
          decimals = ". (int)$this->data['decimals'] .",
          priority = ". (int)$this->data['priority'] .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      if (!empty($this->previous['code'])) {
        if ($this->data['code'] != $this->previous['code']) {

          if ($this->previous['code'] == settings::get('store_currency_code')) {
            throw new Exception('Cannot rename the store currency.');
          }

          database::query(
            "alter table ". DB_TABLE_PREFIX ."products_prices
            change `". database::input($this->previous['code']) ."` `". database::input($this->data['code']) ."` decimal(11, 4) not null;"
          );

          database::query(
            "alter table ". DB_TABLE_PREFIX ."products_campaigns
            change `". database::input($this->previous['code']) ."` `". database::input($this->data['code']) ."` decimal(11, 4) not null;"
          );

          database::query(
            "alter table ". DB_TABLE_PREFIX ."products_options_values
            change `". database::input($this->previous['code']) ."` `". database::input($this->data['code']) ."` decimal(11, 4) not null;"
          );
        }

      } else {

        $products_prices_query = database::query(
          "show fields from ". DB_TABLE_PREFIX ."products_prices
          where `Field` = '". database::input($this->data['code']) ."';"
        );

        if (!database::num_rows($products_prices_query)) {
          database::query(
            "alter table ". DB_TABLE_PREFIX ."products_prices
            add `". database::input($this->data['code']) ."` decimal(11, 4) not null;"
          );
        }

        $products_campaigns_query = database::query(
          "show fields from ". DB_TABLE_PREFIX ."products_campaigns
          where `Field` = '". database::input($this->data['code']) ."';"
        );

        if (!database::num_rows($products_campaigns_query)) {
          database::query(
            "alter table ". DB_TABLE_PREFIX ."products_campaigns
            add `". database::input($this->data['code']) ."` decimal(11, 4) not null;"
          );
        }

        $products_options_query = database::query(
          "show fields from ". DB_TABLE_PREFIX ."products_options_values
          where `Field` = '". database::input($this->data['code']) ."';"
        );

        if (!database::num_rows($products_options_query)) {
          database::query(
            "alter table ". DB_TABLE_PREFIX ."products_options_values
            add `". database::input($this->data['code']) ."` decimal(11, 4) not null after `price_operator`;"
          );
        }
      }

      $this->previous = $this->data;

      cache::clear_cache('currencies');
    }

    public function delete() {

      if ($this->data['code'] == settings::get('store_currency_code')) {
        throw new Exception(language::translate('error_cannot_delete_store_currency', 'You must change the store currency before it can be deleted.'));
      }

      if ($this->data['code'] == settings::get('default_currency_code')) {
        throw new Exception(language::translate('error_cannot_delete_default_currency', 'You must change the default currency before it can be deleted.'));
      }

      database::query(
        "delete from ". DB_TABLE_PREFIX ."currencies
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      database::query(
        "alter table ". DB_TABLE_PREFIX ."products_prices drop `". database::input($this->data['code']) ."`;"
      );

      database::query(
        "alter table ". DB_TABLE_PREFIX ."products_campaigns drop `". database::input($this->data['code']) ."`;"
      );

      database::query(
        "alter table ". DB_TABLE_PREFIX ."products_options_values drop `". database::input($this->data['code']) ."`;"
      );

      $this->reset();

      cache::clear_cache('currencies');
    }
  }

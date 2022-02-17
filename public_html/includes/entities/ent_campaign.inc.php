<?php

  class ent_campaign {
    public $data;
    public $previous;

    public function __construct($campaign_id=null) {

      if (!empty($campaign_id)) {
        $this->load($campaign_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."products_campaigns;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $this->previous = $this->data;
    }

    public function load($campaign_id) {

      if (preg_match('#[^0-9]#', $campaign_id)) throw new Exception('Invalid campaign id ('. $campaign_id .')');

      $this->reset();

      $campaign_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_campaigns
        ". (preg_match('#^[0-9]+$#', $campaign_id) ? "where id = '". (int)$campaign_id ."'" : "") ."
        limit 1;"
      );

      if ($campaign = database::fetch($campaign_query)) {
        $this->data = array_replace($this->data, array_intersect_key($campaign, $this->data));
      } else {
        throw new Exception('Could not find campaign ('. functions::escape_html($campaign_id) .') in database.');
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."products_campaigns
          (product_id)
          values (". (int)$this->data['product_id'] .");"
        );
        $this->data['id'] = database::insert_id();
      }

      $sql_campaign_prices = '';
      foreach (currency::$currencies as $currency) {
        $sql_campaign_prices .= $currency['code'] ." = ". (!empty($this->data[$currency['code']]) ? (float)$this->data[$currency['code']] : 0) . "," . PHP_EOL;
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."products_campaigns
        set product_id = ". (int)$this->data['product_id'] .",
          $sql_campaign_prices
          start_date = '". database::input($this->data['start_date']) ."',
          end_date = '". database::input($this->data['end_date']) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('campaigns');
      cache::clear_cache('products');
    }

    public function delete() {

      database::query(
        "delete from ". DB_TABLE_PREFIX ."products_campaigns
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('campaigns');
      cache::clear_cache('products');
    }
  }

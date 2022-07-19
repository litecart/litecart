<?php

  class ent_stock_transaction {
    public $data;
    public $previous;

    public function __construct($transaction_id=null) {

      if (!empty($transaction_id)) {
        $this->load($transaction_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."stock_transactions;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field['Type']);
      }

      $this->data['contents'] = [];

      $this->previous = $this->data;
    }

    public function load($transaction_id) {

      if (!preg_match('#^(system|[0-9]+)$#', $transaction_id)) throw new Exception('Invalid stock transaction (ID: '. $transaction_id .')');

      $this->reset();

      if ($transaction_id == 'system') {

        $transaction_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."stock_transactions
          where name like 'System Generated%'
          and date(date_created) = '". date('Y-m-d') ."'
          limit 1;"
        );

        if ($transaction = database::fetch($transaction_query)) {
          $this->load($transaction['id']);
        } else {
          $this->data['name'] = 'System Generated '. date('Y-m-d');
        }

        return;
      }

      $transaction_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."stock_transactions
        where id = ". (int)$transaction_id ."
        limit 1;"
      );

      if ($transaction = database::fetch($transaction_query)) {
        $this->data = array_replace($this->data, array_intersect_key($transaction, $this->data));
      } else {
        trigger_error('Could not find stock transacction (ID: '. (int)$transaction_id .') in database.', E_USER_ERROR);
      }

      $this->data['contents'] = database::query(
        "select stc.*, si.sku, si.quantity, si.backordered, sii.name
        from ". DB_TABLE_PREFIX ."stock_transactions_contents stc
        left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = stc.stock_item_id)
        left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = stc.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
        where stc.transaction_id = ". (int)$this->data['id'] .";"
      )->fetch_all();

      $this->previous = $this->data;
    }

    public function save() {

    // Insert/update transaction
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."stock_transactions
          (name, date_created)
          values ('". database::input($this->data['name']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."stock_transactions
        set name = '". database::input($this->data['name']) ."',
          description = '". database::input($this->data['description']) ."',
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

    // Revert stock changes
      foreach ($this->previous['contents'] as $content) {
        database::query(
          "update ". DB_TABLE_PREFIX ."stock_items
          set quantity = quantity - ". (float)$content['quantity_adjustment'] ."
          where id = ". (int)$content['stock_item_id'] ."
          limit 1;"
        );
      }

    // Delete transaction contents
      database::query(
        "delete from ". DB_TABLE_PREFIX ."stock_transactions_contents
        where transaction_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", database::input(array_column($this->data['contents'], 'id'))) ."');"
      );

    // Insert/update transaction contents
      foreach ($this->data['contents'] as $key => $content) {
        if (empty($content['id'])) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."stock_transactions_contents
            (transaction_id)
            values (". (int)$this->data['id'] .");"
          );
          $this->data['contents'][$key]['id'] = $content['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."stock_transactions_contents
          set stock_item_id = ". (int)$content['stock_item_id'] .",
            quantity_adjustment = ". (float)$content['quantity_adjustment'] ."
          where transaction_id = ". (int)$this->data['id'] ."
          and id = ". (int)$content['id'] ."
          limit 1;"
        );

      // Commit stock changes
        database::query(
          "update ". DB_TABLE_PREFIX ."stock_items
          set quantity = quantity + ". (float)$content['quantity_adjustment'] ."
          where id = ". (int)$content['stock_item_id'] ."
          limit 1;"
        );
      }

      $this->previous = $this->data;

      cache::clear_cache('stock');
      cache::clear_cache('product');
    }

    public function delete() {

      if (empty($this->data['id'])) return;

      database::query(
        "delete st, stc
        from ". DB_TABLE_PREFIX ."stock_transactions st
        left join ". DB_TABLE_PREFIX ."stock_transactions_contents stc on (stc.transaction_id = st.id)
        where st.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('stock');
      cache::clear_cache('product');
    }
  }

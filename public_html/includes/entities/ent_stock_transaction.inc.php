<?php

  class ent_stock_transaction {
    public $data;
    public $previous;

    public function __construct($transaction_id='') {

      if ($transaction_id == 'system') {
        $this->reset();

        $transactions_query = database::query(
          "select * from ". DB_TABLE_STOCK_TRANSACTIONS ."
          where name like 'System Generated%'
          and date(date_created) = '". date('Y-m-d') ."'
          limit 1;"
        );

        if ($transaction = database::fetch($transactions_query)) {
          $this->load($transaction['id']);
        } else {
          $this->data['name'] = 'System Generated '. date('Y-m-d');
        }

      } else if (!empty($transaction_id)) {
        $this->load((int)$transaction_id);

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
        $this->data[$field['Field']] = null;
      }

      $this->data['contents'] = [];

      $this->previous = $this->data;
    }

    public function load($transaction_id) {

      if (!preg_match('#^[0-9]+$#', $transaction_id)) throw new Exception('Invalid stock transaction (ID: '. $transaction_id .')');

      $this->reset();

      $transactions_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."stock_transactions
        where id = ". (int)$transaction_id ."
        limit 1;"
      );

      if ($transaction = database::fetch($transactions_query)) {
        $this->data = array_replace($this->data, array_intersect_key($transaction, $this->data));
      } else {
        trigger_error('Could not find stock transacction (ID: '. (int)$transaction_id .') in database.', E_USER_ERROR);
      }

      $contents_query = database::query(
        "select stc.*, ps.sku from ". DB_TABLE_PREFIX ."stock_transactions_contents stc
        left join ". DB_TABLE_PREFIX ."products_stock ps on (ps.product_id = stc.product_id and ps.combination = stc.combination)
        where stc.transaction_id = ". (int)$this->data['id'] .";"
      );

      while ($content = database::fetch($contents_query)) {

        $content['name'] = @reference::product($content['product_id'])->id ? reference::product($content['product_id'])->name : '<em>Removed</em>';

        if (!empty($content['combination'])) {
          foreach(explode(',', $content['combination']) as $combination) {
            @list($group_id, $value_id) = explode('-', $combination);

            $attribute_groups_query = database::query(
              "select ag.id, agi.name from ". DB_TABLE_PREFIX ."attribute_groups ag
              left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
              where ag.id = ". (int)$group_id .";"
            );
            $attribute_group = database::fetch($attribute_groups_query);

            $attribute_values_query = database::query(
              "select avi.id, ovi.name from ". DB_TABLE_PREFIX ."attribute_values av
              left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (ovi.value_id = avi.id and ovi.language_code = '". database::input(language::$selected['code']) ."')
              where avi.group_id = ". (int)$group_id ."
              and avi.id = ". (int)$value_id .";"
            );
            $attribute_value = database::fetch($attribute_values_query);

            $content['name'] .= (!empty($use_separator)  ? ' + ' : '') . @$attribute_group['name'] .': '. @$attribute_value['name'];
            $use_separator = true;
          }
        }

        $this->data['contents'][] = $content;
      }

      $this->previous = $this->data;
    }

    public function save() {

    // Insert/update transaction
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."stock_transactions
          (name, notes, date_created)
          values ('". database::input($this->data['name']) ."', '". database::input($this->data['notes']) ."', '". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."stock_transactions set
        name = '". database::input($this->data['name']) ."',
        notes = '". database::input($this->data['notes']) ."',
        date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

    // Revert stock changes
      foreach ($this->previous['contents'] as $content) {
        reference::product($content['product_id'])->adjust_stock(-$content['quantity'], $content['combination']);
      }

    // Delete transaction contents
      database::query(
        "delete from ". DB_TABLE_PREFIX ."stock_transactions_contents
        where transaction_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", database::input(array_column($this->data['contents'], 'id'))) ."');"
      );

    // Insert/update transaction contents
      foreach ($this->data['contents'] as &$content) {
        if (empty($content['id'])) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."stock_transactions_contents
            (transaction_id)
            values (". (int)$this->data['id'] .");"
          );
          $content['id'] = database::insert_id();
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."stock_transactions_contents
          set product_id = ". (int)$content['product_id'] .",
              combination = '". database::input($content['combination']) ."',
              quantity = ". (float)$content['quantity'] ."
          where transaction_id = ". (int)$this->data['id'] ."
          and id = ". (int)$content['id'] ."
          limit 1;"
        );

      // Commit stock changes
        reference::product($content['product_id'])->adjust_stock($content['quantity'], $content['combination']);
      } unset($content);

      $this->previous = $this->data;

      cache::clear_cache('stock');
      cache::clear_cache('product');
    }

    public function delete() {

      if (empty($this->data['id'])) return;

    // Empty transaction first..
      $this->data['contents'] = [];
      $this->save();

    // ..then delete
      database::query(
        "delete from ". DB_TABLE_PREFIX ."stock_transactions
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->reset();

      cache::clear_cache('stock');
      cache::clear_cache('product');
    }
  }

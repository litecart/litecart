<?php

  class ent_stock_item {
    public $data;
    public $previous;

    public function __construct($stock_item_id=null) {

      if ($stock_item_id !== null) {
        $this->load((int)$stock_item_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."stock_items;"
      );
      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = null;
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."products_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'stock_item_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = null;
        }
      }

      $this->previous = $this->data;
    }

    public function load($stock_item_id) {

      $this->reset();

      $stock_items_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."stock_items
        where id = ". (int)$stock_item_id ."
        limit 1;"
      );

      if ($stock_item = database::fetch($stock_items_query)) {
        $this->data = array_replace($this->data, array_intersect_key($stock_item, $this->data));
      } else {
        trigger_error('Could not find stock item (ID: '. (int)$stock_item_id .') in database.', E_USER_ERROR);
      }

    // Info
      $stock_items_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."stock_items_info
         where stock_item_id = ". (int)$stock_item_id .";"
      );

      while ($stock_item_info = database::fetch($stock_items_info_query)) {
        foreach ($stock_item_info as $key => $value) {
          if (in_array($key, ['id', 'stock_item_id', 'language_code'])) continue;
          $this->data[$key][$stock_item_info['language_code']] = $value;
        }
      }

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."stock_items
          (sku, mpn, gtin, date_created)
          values ('". database::input($this->data['sku']) ."', '". database::input($this->data['mpn']) ."', '". database::input($this->data['gtin']) ."', '". ($this->data['date_created'] = date('c')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."stock_items set
        sku = '". database::input(strtoupper($this->data['sku'])) ."',
        mpn = '". database::input($this->data['mpn']) ."',
        gtin = '". database::input($this->data['gtin']) ."',
        reordered = ". (float)$this->data['reordered'] .",
        quantity_unit_id = ". (int)$this->data['quantity_unit_id'] .",
        purchase_price = '". (float)$this->data['purchase_price'] ."',
        purchase_price_currency_code = '". database::input($this->data['purchase_price_currency_code']) ."',
        weight = '". (float)$this->data['weight'] ."',
        weight_unit = '". database::input($this->data['weight_unit']) ."',
        length = ". (float)$this->data['length'] .",
        width = ". (float)$this->data['width'] .",
        height = ". (float)$this->data['height'] .",
        length_unit = '". database::input($this->data['length_unit']) ."',
        date_updated = '". ($this->data['date_updated'] = date('c')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      foreach (array_keys(language::$languages) as $language_code) {
        $stock_items_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."stock_items_info
          where stock_item_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
        $stock_item_info = database::fetch($stock_items_info_query);

        if (empty($stock_item_info)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."stock_items_info
            (stock_item_id, language_code)
            values (". (int)$this->data['id'] .", '". $language_code ."');"
          );
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."stock_items_info set
          name = '". database::input($this->data['name'][$language_code]) ."'
          where stock_item_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

    // If new total quantity is set
      if ((float)$this->data['quantity_adjustment'] == 0 && (float)$this->data['quantity'] != (float)$this->previous['quantity']) {
        $this->data['quantity_adjustment'] = (float)$this->data['quantity'] - (float)$this->previous['quantity'];
      }

    // If quantity adjustment is set
      if (!empty($this->data['quantity_adjustment']) && (float)$this->data['quantity_adjustment'] != 0) {

        $stock_transaction = new ent_stock_transaction('system');

        if (($key = array_search($this->data['id'], array_column($stock_transaction->data['contents'], 'stock_item_id'))) !== false) {
          $stock_transaction->data['contents'][$key]['quantity_adjustment'] += (float)$this->data['quantity_adjustment'];
        } else {
          $stock_transaction->data['contents'][] = [
            'stock_item_id' => $this->data['id'],
            'quantity_adjustment' => $this->data['quantity_adjustment'],
          ];
        }

        $stock_transaction->save();

        $this->data['quantity_adjustment'] = 0;
      }

      $this->previous = $this->data;

      cache::clear_cache('stock_item');
    }

    public function save_file($source, $filename, $mime_type) {

      if (empty($source)) return;

      if (empty($this->data['id'])) {
        $this->save();
      }

      if (!is_dir(FS_DIR_STORAGE . 'files/')) mkdir(FS_DIR_STORAGE . 'files/', 0777);

      if (is_file(FS_DIR_STORAGE . $this->data['file'])) {
        unlink(FS_DIR_STORAGE . $this->data['file']);
      }

      $file = 'files/' . $this->data['id'] .'-'. $filename;
      copy($source, FS_DIR_STORAGE . $file);

      $this->previous['file'] = $this->data['file'] = $file;
      $this->previous['filename'] = $this->data['filename'] = $filename;
      $this->previous['mime_type'] = $this->data['mime_type'] = $mime_type;

      database::query(
        "update ". DB_TABLE_PREFIX ."stock_items
        set file = '". database::input($this->data['file']) ."',
          filename = '". database::input($this->data['filename']) ."',
          mime_type = '". database::input($this->data['mime_type']) ."'
        where id = ". (int)$this->data['id'] .";"
      );
    }

    public function delete_file() {

      if (empty($this->data['id'])) return;

      if (is_file(FS_DIR_STORAGE . $this->data['file'])) {
        unlink(FS_DIR_STORAGE . $this->data['file']);
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."stock_items
        set file = ''
        where id = ". (int)$this->data['id'] .";"
      );

      $this->previous['file'] = $this->data['file'] = '';
    }

    public function delete() {

      database::query(
        "delete si, sii
        from ". DB_TABLE_PREFIX ."stock_items si
        left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = si.id)
        where si.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('stock_item');
    }
  }

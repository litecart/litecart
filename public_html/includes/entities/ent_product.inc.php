<?php

  class ent_product {
    public $data;
    public $previous;

    public function __construct($product_id=null) {

      if (!empty($product_id)) {
        $this->load($product_id);
      } else {
        $this->reset();
      }
    }

    public function reset() {

      $this->data = [];

      $fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."products;"
      );

      while ($field = database::fetch($fields_query)) {
        $this->data[$field['Field']] = database::create_variable($field);
      }

      $info_fields_query = database::query(
        "show fields from ". DB_TABLE_PREFIX ."products_info;"
      );

      while ($field = database::fetch($info_fields_query)) {
        if (in_array($field['Field'], ['id', 'product_id', 'language_code'])) continue;

        $this->data[$field['Field']] = [];
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = database::create_variable($field);
        }
      }

      $this->data['status'] = 1;
      $this->data['purchase_price_currency_code'] = settings::get('store_currency_code');

      $this->data['categories'] = [];
      $this->data['attributes'] = [];
      $this->data['images'] = [];
      $this->data['prices'] = [];
      $this->data['campaigns'] = [];
      $this->data['stock_options'] = [];

      $this->previous = $this->data;
    }

    public function load($product_id) {

      if (empty($product_id)) throw new Exception('Invalid product (ID: n/a)');

      $this->reset();

    // Product
      $product = database::query(
        "select * from ". DB_TABLE_PREFIX ."products
        where ". (preg_match('#^[0-9]+$#', $product_id) ? "id = ". (int)$product_id : "code = '". database::input($product_id) ."'") ."
        limit 1;"
      )->fetch();

      if ($product) {
        $this->data = array_replace($this->data, array_intersect_key($product, $this->data));
      } else {
        throw new Exception('Could not find product (ID: '. (int)$product_id .') in database.');
      }

    // Categories
      $this->data['categories'] = database::query(
        "select category_id from ". DB_TABLE_PREFIX ."products_to_categories
         where product_id = ". (int)$product_id .";"
      )->fetch_all('category_id');

    // Info
      $products_info_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_info
         where product_id = ". (int)$product_id .";"
      );

      while ($product_info = database::fetch($products_info_query)) {
        foreach ($product_info as $key => $value) {
          if (in_array($key, ['id', 'product_id', 'language_code'])) continue;
          $this->data[$key][$product_info['language_code']] = $value;
        }
      }

    // Attributes
      $this->data['attributes'] = database::query(
        "select pa.*, agi.name as group_name, avi.name as value_name
        from ". DB_TABLE_PREFIX ."products_attributes pa
        left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = pa.group_id and agi.language_code = '". database::input(language::$selected['code']) ."')
        left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = pa.value_id and avi.language_code = '". database::input(language::$selected['code']) ."')
        where product_id = ". (int)$product_id ."
        order by priority, group_name, value_name, custom_value;"
      )->fetch_all();

    // Prices
      $products_prices_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_prices
        where product_id = ". (int)$this->data['id'] .";"
      );

      while ($product_price = database::fetch($products_prices_query)) {
        foreach (array_keys(currency::$currencies) as $currency_code) {
          $this->data['prices'][$currency_code] = $product_price[$currency_code];
        }
      }

    // Campaigns
      $this->data['campaigns'] = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_campaigns
        where product_id = ". (int)$this->data['id'] ."
        order by start_date;"
      )->fetch_all();

    // Stock Items
      $this->data['stock_options'] = database::query(
        "select p2si.*, sii.name, si.sku, si.gtin, si.quantity, si.quantity_unit_id, si.backordered, si.weight, si.weight_unit, si.length, si.width, si.height, si.length_unit from ". DB_TABLE_PREFIX ."products_to_stock_items p2si
        left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = p2si.stock_item_id)
        left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = p2si.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
        where p2si.product_id = ". (int)$this->data['id'] ."
        order by p2si.priority;"
      )->fetch_all();

    // Images
      $this->data['images'] = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_images
        where product_id = ". (int)$this->data['id'] ."
        order by priority asc, id asc;"
      )->fetch_all();

      $this->previous = $this->data;
    }

    public function save() {

      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PREFIX ."products
          (date_created)
          values ('". ($this->data['date_created'] = date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }

      $this->data['categories'] = array_map('trim', $this->data['categories']);
      $this->data['categories'] = array_filter($this->data['categories'], function($var) { return ($var != ''); }); // Don't filter root ('0')
      $this->data['categories'] = array_unique($this->data['categories']);

      $this->data['keywords'] = preg_split('#\s*,\s*#', $this->data['keywords'], -1, PREG_SPLIT_NO_EMPTY);
      $this->data['keywords'] = array_map('trim', $this->data['keywords']);
      $this->data['keywords'] = array_filter($this->data['keywords']);
      $this->data['keywords'] = array_unique($this->data['keywords']);
      $this->data['keywords'] = implode(',', $this->data['keywords']);

      if (empty($this->data['default_category_id']) || !in_array($this->data['default_category_id'], $this->data['categories'])) {
        $this->data['default_category_id'] = reset($this->data['categories']);
      }

      database::query(
        "update ". DB_TABLE_PREFIX ."products
        set status = ". (int)$this->data['status'] .",
          brand_id = ". (int)$this->data['brand_id'] .",
          supplier_id = ". (int)$this->data['supplier_id'] .",
          delivery_status_id = ". (int)$this->data['delivery_status_id'] .",
          sold_out_status_id = ". (int)$this->data['sold_out_status_id'] .",
          default_category_id = ". (int)$this->data['default_category_id'] .",
          keywords = '". database::input($this->data['keywords']) ."',
          quantity_min = ". (float)$this->data['quantity_min'] .",
          quantity_max = ". (float)$this->data['quantity_max'] .",
          quantity_step = ". (float)$this->data['quantity_step'] .",
          quantity_unit_id = ". (int)$this->data['quantity_unit_id'] .",
          recommended_price = ". (float)$this->data['recommended_price'] .",
          tax_class_id = ". (int)$this->data['tax_class_id'] .",
          code = '". database::input($this->data['code']) ."',
          autofill_technical_data = ". (int)$this->data['autofill_technical_data'] .",
          date_valid_from = ". (empty($this->data['date_valid_from']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
          date_valid_to = ". (empty($this->data['date_valid_to']) ? "null" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
          date_updated = '". ($this->data['date_updated'] = date('Y-m-d H:i:s')) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

    // Categories
      database::query(
        "delete from ". DB_TABLE_PREFIX ."products_to_categories
        where product_id = ". (int)$this->data['id'] ."
        and category_id not in ('". implode("', '", database::input($this->data['categories'])) ."');"
      );

      foreach ($this->data['categories'] as $category_id) {
        if (in_array($category_id, $this->previous['categories'])) continue;
        database::query(
          "insert into ". DB_TABLE_PREFIX ."products_to_categories
          (product_id, category_id)
          values (". (int)$this->data['id'] .", ". (int)$category_id .");"
        );
      }

    // Info
      foreach (array_keys(language::$languages) as $language_code) {
        $products_info_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."products_info
          where product_id = ". (int)$this->data['id'] ."
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );

        if (!$product_info = database::fetch($products_info_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."products_info
            (product_id, language_code)
            values (". (int)$this->data['id'] .", '". database::input($language_code) ."');"
          );
        }

        database::query(
          "update ". DB_TABLE_PREFIX ."products_info
          set name = '". database::input($this->data['name'][$language_code]) ."',
            short_description = '". database::input($this->data['short_description'][$language_code]) ."',
            description = '". database::input($this->data['description'][$language_code], true) ."',
            technical_data = '". database::input($this->data['technical_data'][$language_code], true) ."',
            head_title = '". database::input($this->data['head_title'][$language_code]) ."',
            meta_description = '". database::input($this->data['meta_description'][$language_code]) ."'
            where product_id = ". (int)$this->data['id'] ."
            and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }

    // Attributes
      database::query(
        "delete from ". DB_TABLE_PREFIX ."products_attributes
        where product_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['attributes'], 'id')) ."');"
      );

      if (!empty($this->data['attributes'])) {
        $i = 0;
        foreach ($this->data['attributes'] as $key => $attribute) {
          if (empty($attribute['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."products_attributes
              (product_id, group_id, value_id, custom_value)
              values (". (int)$this->data['id'] .", ". (int)$attribute['group_id'] .", ". (int)$attribute['value_id'] .", '". database::input($attribute['custom_value']) ."');"
            );
            $this->data['attributes'][$key]['id'] = $attribute['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_TABLE_PREFIX ."products_attributes
            set group_id = ". (int)$attribute['group_id'] .",
              value_id = ". (int)$attribute['value_id'] .",
              custom_value = '". database::input($attribute['custom_value']) ."',
              priority = ". (int)++$i ."
            where product_id = ". (int)$this->data['id'] ."
            and id = ". (int)$attribute['id'] ."
            limit 1;"
          );
        }
      }

    // Prices
      foreach (array_keys(currency::$currencies) as $currency_code) {

        $products_prices_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."products_prices
          where product_id = ". (int)$this->data['id'] ."
          limit 1;"
        );

        if (!$product_price = database::fetch($products_prices_query)) {
          database::query(
            "insert into ". DB_TABLE_PREFIX ."products_prices
            (product_id)
            values (". (int)$this->data['id'] .");"
          );
        }

        $sql_currency_prices = "";
        foreach (array_keys(currency::$currencies) as $currency_code) {
          $sql_currency_prices .= $currency_code ." = '". (!empty($this->data['prices'][$currency_code]) ? (float)$this->data['prices'][$currency_code] : 0) ."', ";
        }
        $sql_currency_prices = rtrim($sql_currency_prices, ', ');

        database::query(
          "update ". DB_TABLE_PREFIX ."products_prices
          set $sql_currency_prices
          where product_id = ". (int)$this->data['id'] ."
          limit 1;"
        );
      }

    // Delete campaigns
      database::query(
        "delete from ". DB_TABLE_PREFIX ."products_campaigns
        where product_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['campaigns'], 'id')) ."');"
      );

    // Update campaigns
      if (!empty($this->data['campaigns'])) {
        foreach ($this->data['campaigns'] as $key => $campaign) {
          if (empty($campaign['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."products_campaigns
              (product_id)
              values (". (int)$this->data['id'] .");"
            );
            $campaign['id'] = database::insert_id();
          }

          $sql_currency_campaigns = "";
          foreach (array_keys(currency::$currencies) as $currency_code) {
            $sql_currency_campaigns .= $currency_code ." = '". (float)$campaign[$currency_code] ."', ";
          }
          $sql_currency_campaigns = rtrim($sql_currency_campaigns, ', ');

          database::query(
            "update ". DB_TABLE_PREFIX ."products_campaigns
            set start_date = ". (empty($campaign['start_date']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($campaign['start_date'])) ."'") .",
              end_date = ". (empty($campaign['end_date']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($campaign['end_date'])) ."'") .",
              $sql_currency_campaigns
            where product_id = ". (int)$this->data['id'] ."
            and id = ". (int)$campaign['id'] ."
            limit 1;"
          );
        }
      }

    // Delete stock items
      database::query(
        "delete from ". DB_TABLE_PREFIX ."products_to_stock_items
        where product_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['stock_options'], 'id')) ."');"
      );

    // Update stock items
      if (!empty($this->data['stock_options'])) {

        $i = 0;
        foreach ($this->data['stock_options'] as $key => $stock_option) {

          if (empty($stock_option['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."products_to_stock_items
              (product_id, stock_item_id)
              values (". (int)$this->data['id'] .", ". (int)$stock_option['stock_item_id'] .");"
            );
            $this->data['stock_options'][$key]['id'] = $stock_option['id'] = database::insert_id();
          }

          database::query(
            "update ". DB_TABLE_PREFIX ."products_to_stock_items
            set priority = ". (int)$i++ ."
            where product_id = ". (int)$this->data['id'] ."
            and id = ". (int)$stock_option['id'] ."
            limit 1;"
          );

          $ent_stock_item = new ent_stock_item($stock_option['stock_item_id']);
          $ent_stock_item->data['quantity_adjustment'] = $stock_option['quantity_adjustment'];
          $ent_stock_item->save();
        }
      }

    // Delete images
      $products_images_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."products_images
        where product_id = ". (int)$this->data['id'] ."
        and id not in ('". implode("', '", array_column($this->data['images'], 'id')) ."');"
      );

      while ($product_image = database::fetch($products_images_query)) {
        if (is_file('storage://images/' . $product_image['filename'])) {
          unlink('storage://images/' . $product_image['filename']);
        }

        functions::image_delete_cache('storage://images/' . $product_image['filename']);

        database::query(
          "delete from ". DB_TABLE_PREFIX ."products_images
          where product_id = ". (int)$this->data['id'] ."
          and id = ". (int)$product_image['id'] ."
          limit 1;"
        );
      }

    // Update images
      if (!empty($this->data['images'])) {
        $image_priority = 1;

        foreach ($this->data['images'] as $key => $image) {

          if (empty($image['id'])) continue;
          if (empty($image['new_filename'])) continue;

          if ($this->data['images'][$key]['new_filename'] != $image['filename']) {

            if (is_file('storage://images/' . $image['new_filename'])) {
              throw new Exception('Cannot rename '. $this->data['images'][$key]['filename'] .' to '. $this->data['images'][$key]['filename'] .' as the new filename already exists');
            }

            rename('storage://images/' . $image['filename'], FS_DIR_STORAGE . 'images/' . $image['new_filename']);
            $this->data['images'][$key]['filename'] = $image['new_filename'];

            functions::image_delete_cache('storage://images/' . $image['filename']);
            functions::image_delete_cache('storage://images/' . $image['new_filename']);
          }

          database::query(
            "update ". DB_TABLE_PREFIX ."products_images
            set filename = '". database::input($image['filename']) ."',
              priority = ". (int)$image_priority++ ."
            where product_id = ". (int)$this->data['id'] ."
            and id = ". (int)$image['id'] ."
            limit 1;"
          );
        }
      }

    // Set main product image
      $this->data['image'] = !empty($this->data['images']) ? array_values($this->data['images'])[0]['filename'] : '';

      database::query(
        "update ". DB_TABLE_PREFIX ."products
        set image = '". database::input($this->data['image']) ."'
        where id = ". (int)$this->data['id'] ."
        limit 1;"
      );

      $this->previous = $this->data;

      cache::clear_cache('category');
      cache::clear_cache('brands');
      cache::clear_cache('products');
    }

    public function add_image($file, $filename='') {

      if (empty($file)) {
        throw new Exception('Missing image');
      };

      $checksum = md5_file($file);
      if (in_array($checksum, array_column($this->data['images'], 'checksum'))) return false;

      if (!empty($filename)) $filename = 'products/' . $filename;

      if (empty($this->data['id'])) {
        $this->save();
      }

      if (!is_dir('storage://images/products/')) {
        mkdir('storage://images/products/', 0777);
      }

      if (!$image = new ent_image($file)) {
        throw new Exception('Failed decoding image');
      }

    // 456-Fancy-product-title-N.jpg
      $i=1;
      while (empty($filename) || is_file('storage://images/' . $filename)) {
        $filename = 'products/' . $this->data['id'] .'-'. functions::format_path_friendly($this->data['name'][settings::get('store_language_code')], settings::get('store_language_code')) .'-'. $i++ .'.'. $image->type;
      }

      $priority = count($this->data['images'])+1;

      if (settings::get('image_downsample_size')) {
        list($width, $height) = explode(',', settings::get('image_downsample_size'));
        $image->resample($width, $height, 'FIT_ONLY_BIGGER');
      }

      if (!$image->write('storage://images/' . $filename, 90)) return false;

      functions::image_delete_cache('storage://images/' . $filename);

      database::query(
        "insert into ". DB_TABLE_PREFIX ."products_images
        (product_id, filename, checksum, priority)
        values (". (int)$this->data['id'] .", '". database::input($filename) ."', '". database::input($checksum) ."', ". (int)$priority .");"
      );

      $image_id = database::insert_id();

      $row = [
        'id' => $image_id,
        'filename' => $filename,
        'checksum' => $checksum,
        'priority' => $priority,
      ];

      $this->previous['images'][] = $row;
      $this->data['images'][] = $row;
    }

    public function delete() {

      if (empty($this->data['id'])) return;

    // Delete images
      $this->data['images'] = [];
      $this->save();

      database::query(
        "delete p, pi, pa, pp, pc, p2si, ptc
        from ". DB_TABLE_PREFIX ."products p
        left join ". DB_TABLE_PREFIX ."products_info pi on (pi.id = p.id)
        left join ". DB_TABLE_PREFIX ."products_attributes pa on (pa.product_id = p.id)
        left join ". DB_TABLE_PREFIX ."products_prices pp on (pp.product_id = p.id)
        left join ". DB_TABLE_PREFIX ."products_campaigns pc on (pc.product_id = p.id)
        left join ". DB_TABLE_PREFIX ."products_to_stock_items p2si on (p2si.product_id = p.id)
        left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (ptc.product_id = p.id)
        where p.id = ". (int)$this->data['id'] .";"
      );

      $this->reset();

      cache::clear_cache('category');
      cache::clear_cache('products');
    }
  }

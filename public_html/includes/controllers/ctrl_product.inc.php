<?php

  class ctrl_product {
    public $data;
    
    public function __construct($product_id='') {
      
      if (!empty($product_id)) {
        $this->load($product_id);
      } else {
        $this->reset();
      }
    }
    
    public function reset() {
      
      $this->data = array();
      
      $products_query = database::query(
        "show fields from ". DB_TABLE_PRODUCTS .";"
      );
      while ($field = database::fetch($products_query)) {
        $this->data[$field['Field']] = '';
      }
      
      $this->data['categories'] = array();
      $this->data['product_groups'] = array();
      $this->data['images'] = array();
      $this->data['prices'] = array();
      $this->data['campaigns'] = array();
      $this->data['options'] = array();
      $this->data['options_stock'] = array();
      
      $products_info_query = database::query(
        "show fields from ". DB_TABLE_PRODUCTS_INFO .";"
      );
      
      while ($field = database::fetch($products_info_query)) {
        if (in_array($field['Field'], array('id', 'product_id', 'language_code'))) continue;
        $this->data[$field['Field']] = array();
        foreach (array_keys(language::$languages) as $language_code) {
          $this->data[$field['Field']][$language_code] = '';
        }
      }
    }
    
    public function load($product_id) {
      
      $this->reset();
      
    // Product
      $products_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS ."
        where id = '". (int)$product_id ."'
        limit 1;"
      );
      $product = database::fetch($products_query);
      
      foreach ($product as $key => $value) {
        $this->data[$key] = $value;
      }
      
      $this->data['categories'] = explode(',', $this->data['categories']);
      $this->data['product_groups'] = explode(',', $this->data['product_groups']);
      
    // Info
      $products_info_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id = '". (int)$product_id ."';"
      );
      while ($product_info = database::fetch($products_info_query)) {
        foreach ($product_info as $key => $value) {
          if (in_array($key, array('id', 'product_id', 'language_code'))) continue;
          $this->data[$key][$product_info['language_code']] = $value;
        }
      }
      
    // Prices
      $products_prices_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_PRICES ."
        where product_id = '". (int)$this->data['id'] ."';"
      );
      while ($product_price = database::fetch($products_prices_query)) {
        foreach (array_keys(currency::$currencies) as $currency_code) {
          $this->data['prices'][$currency_code] = $product_price[$currency_code];
        }
      }
      
    // Campaigns
      $product_campaigns_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where product_id = '". (int)$this->data['id'] ."'
        order by start_date;"
      );
      while ($product_campaign = database::fetch($product_campaigns_query)) {
        $this->data['campaigns'][$product_campaign['id']] = $product_campaign;
      }
      
    // Options
      $products_options_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where product_id = '". (int)$this->data['id'] ."'
        order by priority asc;"
      );
      while($option = database::fetch($products_options_query)) {
        $this->data['options'][$option['id']] = $option;
      }
      
    // Options stock
      $products_options_stock_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ." po
        where po.product_id = '". (int)$this->data['id'] ."'
        order by priority;"
      );
      while($option_stock = database::fetch($products_options_stock_query)) {
      
        $this->data['options_stock'][$option_stock['id']] = $option_stock;
        $this->data['options_stock'][$option_stock['id']]['name'] = array();
        
        foreach (explode(',', $option_stock['combination']) as $combination) {
          list($group_id, $value_id) = explode('-', $combination);
          
          $options_values_query = database::query(
            "select ovi.value_id, ovi.name, ovi.language_code from ". DB_TABLE_OPTION_VALUES_INFO ." ovi
            where ovi.value_id = '". (int)$value_id ."';"
          );
          while($option_value = database::fetch($options_values_query)) {
            if (!isset($this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']])) {
              $this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']] = '';
            } else {
              $this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']] .= ', ';
            }
            $this->data['options_stock'][$option_stock['id']]['name'][$option_value['language_code']] .= $option_value['name'];
          }
        }
      }
      
    // Images
      $products_images_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_IMAGES."
        where product_id = '". (int)$this->data['id'] ."'
        order by priority asc, id asc;"
      );
      while($image = database::fetch($products_images_query)) {
        $this->data['images'][$image['id']] = $image;
      }
    }
    
    public function save() {
    
      if (empty($this->data['id'])) {
        database::query(
          "insert into ". DB_TABLE_PRODUCTS ."
          (date_created)
          values ('". database::input(date('Y-m-d H:i:s')) ."');"
        );
        $this->data['id'] = database::insert_id();
      }
      
    // Calculate product quantity from options
      if (!empty($this->data['options_stock'])) {
        $this->data['quantity'] = 0;
        foreach ($this->data['options_stock'] as $option) {
          $this->data['quantity'] += @$option['quantity'];
        }
      }
      
    // Extract first image
      if (!empty($this->data['images'])){
        $images = array_values($this->data['images']);
        $image = array_shift($images);
        $this->data['image'] = $image['filename'];
      } else {
        $this->data['image'];
      }
      
    // Cleanup empty elements in categories
      //$this->data['categories'] = array_filter($this->data['categories']);
      foreach(array_keys($this->data['categories']) as $key){
        if ($this->data['categories'][$key] == '') unset($this->data['categories'][$key]);
      }
      $this->data['categories'] = array_unique($this->data['categories']);
      
      database::query(
        "update ". DB_TABLE_PRODUCTS ." set
        status = '". (int)$this->data['status'] ."',
        manufacturer_id = '". (int)$this->data['manufacturer_id'] ."',
        supplier_id = '". (int)$this->data['supplier_id'] ."',
        delivery_status_id = '". (int)$this->data['delivery_status_id'] ."',
        sold_out_status_id = '". (int)$this->data['sold_out_status_id'] ."',
        categories = '". database::input(implode(',', $this->data['categories'])) ."',
        product_groups = '". database::input(implode(',', $this->data['product_groups'])) ."',
        keywords = '". database::input(rtrim(trim($this->data['keywords']), ',')) ."',
        quantity = '". database::input($this->data['quantity']) ."',
        purchase_price = '". database::input($this->data['purchase_price']) ."',
        tax_class_id = '". database::input($this->data['tax_class_id']) ."',
        code = '". database::input($this->data['code']) ."',
        sku = '". database::input($this->data['sku']) ."',
        upc = '". database::input($this->data['upc']) ."',
        taric = '". database::input($this->data['taric']) ."',
        dim_x = '". database::input($this->data['dim_x']) ."',
        dim_y = '". database::input($this->data['dim_y']) ."',
        dim_z = '". database::input($this->data['dim_z']) ."',
        dim_class = '". database::input($this->data['dim_class']) ."',
        weight = '". database::input($this->data['weight']) ."',
        weight_class = '". database::input($this->data['weight_class']) ."',
        image = '". database::input($this->data['image']) ."',
        date_valid_from = ". (empty($this->data['date_valid_from']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_from'])) ."'") .",
        date_valid_to = ". (empty($this->data['date_valid_to']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['date_valid_to'])) ."'") .",
        date_updated = '". date('Y-m-d H:i:s') ."'
        where id='". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      foreach (array_keys(language::$languages) as $language_code) {
        
        $products_info_query = database::query(
          "select * from ". DB_TABLE_PRODUCTS_INFO ."
          where product_id = '". (int)$this->data['id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
        $product_info = database::fetch($products_info_query);
        
        if (empty($product_info)) {
          database::query(
            "insert into ". DB_TABLE_PRODUCTS_INFO ."
            (product_id, language_code)
            values ('". (int)$this->data['id'] ."', '". $language_code ."');"
          );
        }
        
        database::query(
          "update ". DB_TABLE_PRODUCTS_INFO ." set
          name = '". database::input($this->data['name'][$language_code]) ."',
          short_description = '". database::input($this->data['short_description'][$language_code]) ."',
          description = '". database::input($this->data['description'][$language_code], true) ."',
          head_title = '". database::input($this->data['head_title'][$language_code]) ."',
          meta_description = '". database::input($this->data['meta_description'][$language_code]) ."',
          meta_keywords = '". database::input($this->data['meta_keywords'][$language_code]) ."',
          attributes = '". database::input($this->data['attributes'][$language_code], true) ."'
          where product_id = '". (int)$this->data['id'] ."'
          and language_code = '". database::input($language_code) ."'
          limit 1;"
        );
      }
      
      foreach (array_keys(currency::$currencies) as $currency_code) {
        
        $products_prices_query = database::query(
          "select * from ". DB_TABLE_PRODUCTS_PRICES ."
          where product_id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
        $product_price = database::fetch($products_prices_query);
        
        if (empty($product_price)) {
          database::query(
            "insert into ". DB_TABLE_PRODUCTS_PRICES ."
            (product_id)
            values ('". (int)$this->data['id'] ."');"
          );
        }
        
        $sql_currency_prices = "";
        foreach (array_keys(currency::$currencies) as $currency_code) {
          $sql_currency_prices .= $currency_code ." = '". (!empty($this->data['prices'][$currency_code]) ? (float)$this->data['prices'][$currency_code] : 0) ."', ";
        }
        $sql_currency_prices = rtrim($sql_currency_prices, ', ');
        
        database::query(
          "update ". DB_TABLE_PRODUCTS_PRICES ." set
          $sql_currency_prices
          where product_id = '". (int)$this->data['id'] ."'
          limit 1;"
        );
      }
      
    // Delete campaigns
      database::query(
        "delete from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['campaigns'])) ."');"
      );
      
    // Update campaigns
      if (!empty($this->data['campaigns'])) {
        foreach (array_keys($this->data['campaigns']) as $key) {
          if (empty($this->data['campaigns'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
              (product_id)
              values ('". (int)$this->data['id'] ."');"
            );
            $this->data['campaigns'][$key]['id'] = database::insert_id();
          }
          
          $sql_currency_campaigns = "";
          foreach (array_keys(currency::$currencies) as $currency_code) {
            $sql_currency_campaigns .= $currency_code ." = '". (float)$this->data['campaigns'][$key][$currency_code] ."', ";
          }
          $sql_currency_campaigns = rtrim($sql_currency_campaigns, ', ');
          
          database::query(
            "update ". DB_TABLE_PRODUCTS_CAMPAIGNS ." set
            start_date = ". (empty($this->data['campaigns'][$key]['start_date']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['campaigns'][$key]['start_date'])) ."'") .",
            end_date = ". (empty($this->data['campaigns'][$key]['end_date']) ? "NULL" : "'". date('Y-m-d H:i:s', strtotime($this->data['campaigns'][$key]['end_date'])) ."'") .",
            $sql_currency_campaigns
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['campaigns'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
    // Delete options
      database::query(
        "delete from ". DB_TABLE_PRODUCTS_OPTIONS ."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['options'])) ."');"
      );
      
    // Update options
      if (!empty($this->data['options'])) {
        $i = 0;
        foreach (array_keys($this->data['options']) as $key) {
          $i++;
          
          if (empty($this->data['options'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_PRODUCTS_OPTIONS ."
              (product_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $this->data['options'][$key]['id'] = database::insert_id();
          }
          
          $sql_currency_options = "";
          foreach (array_keys(currency::$currencies) as $currency_code) {
            $sql_currency_options .= $currency_code ." = '". (isset($this->data['options'][$key][$currency_code]) ? (float)$this->data['options'][$key][$currency_code] : 0) ."', ";
          }
          
          database::query(
            "update ". DB_TABLE_PRODUCTS_OPTIONS ."
            set group_id = '". database::input($this->data['options'][$key]['group_id']) ."',
                value_id = '". database::input($this->data['options'][$key]['value_id']) ."',
                price_operator = '". database::input($this->data['options'][$key]['price_operator']) ."',
                $sql_currency_options
                priority = '". (int)$i ."',
                date_updated = '". date('Y-m-d H:i:s') ."'
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['options'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
    // Delete stock options
      database::query(
        "delete from ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['options_stock'])) ."');"
      );
      
    // Update stock options
      if (!empty($this->data['options_stock'])) {
        $i = 0;
        foreach (array_keys($this->data['options_stock']) as $key) {
          if (empty($this->data['options_stock'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ."
              (product_id, date_created)
              values ('". (int)$this->data['id'] ."', '". date('Y-m-d H:i:s') ."');"
            );
            $this->data['options_stock'][$key]['id'] = database::insert_id();
          }
          
        // Ascending option combination
          $combinations = explode(',', $this->data['options_stock'][$key]['combination']);
          if (!function_exists('custom_sort_combinations')) {
            function custom_sort_combinations($a, $b) {
              $a = explode('-', $a);
              $b = explode('-', $b);
              if ($a[0] == $b[0]) {
                return ($a[1] < $b[1]) ? -1 : 1;
              }
              return ($a[0] < $b[0]) ? -1 : 1;
            }
          }
          usort($combinations, 'custom_sort_combinations');
          $this->data['options_stock'][$key]['combination'] = implode(',', $combinations);
          
          database::query(
            "update ". DB_TABLE_PRODUCTS_OPTIONS_STOCK ." 
            set combination = '". database::input($this->data['options_stock'][$key]['combination']) ."',
            sku = '". database::input(@$this->data['options_stock'][$key]['sku']) ."',
            weight = '". database::input(@$this->data['options_stock'][$key]['weight']) ."',
            weight_class = '". database::input(@$this->data['options_stock'][$key]['weight_class']) ."',
            dim_x = '". database::input(@$this->data['options_stock'][$key]['dim_x']) ."',
            dim_y = '". database::input(@$this->data['options_stock'][$key]['dim_y']) ."',
            dim_z = '". database::input(@$this->data['options_stock'][$key]['dim_z']) ."',
            dim_class = '". database::input(@$this->data['options_stock'][$key]['dim_class']) ."',
            quantity = '". database::input(@$this->data['options_stock'][$key]['quantity']) ."',
            priority = '". $i++ ."',
            date_updated =  '". date('Y-m-d H:i:s') ."'
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['options_stock'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
    // Delete images
      $products_images_query = database::query(
        "select * from ". DB_TABLE_PRODUCTS_IMAGES."
        where product_id = '". (int)$this->data['id'] ."'
        and id not in ('". @implode("', '", @array_keys($this->data['images'])) ."');"
      );
      while ($product_image = database::fetch($products_images_query)) {
        if (is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product_image['filename'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product_image['filename']);
        functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product_image['filename']);
        database::query(
          "delete from ". DB_TABLE_PRODUCTS_IMAGES ."
          where product_id = '". (int)$this->data['id'] ."'
          and id = '". (int)$product_image['id'] ."'
          limit 1;"
        );
      }
      
    // Update images
      if (!empty($this->data['images'])) {
        $image_priority = 1;
        foreach (array_keys($this->data['images']) as $key) {
          if (empty($this->data['images'][$key]['id'])) {
            database::query(
              "insert into ". DB_TABLE_PRODUCTS_IMAGES ."
              (product_id)
              values ('". (int)$this->data['id'] ."');"
            );
            $this->data['images'][$key]['id'] = database::insert_id();
          }
          if (!empty($this->data['images'][$key]['new_filename']) && !is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['new_filename'])) {
            functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['filename']);
            functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['new_filename']);
            rename(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['filename'], FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $this->data['images'][$key]['new_filename']);
            $this->data['images'][$key]['filename'] = $this->data['images'][$key]['new_filename'];
          }
          database::query(
            "update ". DB_TABLE_PRODUCTS_IMAGES ."
            set filename = '". $this->data['images'][$key]['filename'] ."',
                priority = '". $image_priority++ ."'
            where product_id = '". (int)$this->data['id'] ."'
            and id = '". (int)$this->data['images'][$key]['id'] ."'
            limit 1;"
          );
        }
      }
      
      cache::clear_cache('product_'.$this->data['id']);
      cache::clear_cache('products');
    }
    
    public function delete() {
    
      if (empty($this->data['id'])) return;
    
      $this->data['images'] = array();
      $this->data['options_stock'] = array();
      $this->data['campaigns'] = array();
      $this->save();
      
      database::query(
        "delete from ". DB_TABLE_PRODUCTS ."
        where id = '". (int)$this->data['id'] ."'
        limit 1;"
      );
      
      database::query(
        "delete from ". DB_TABLE_PRODUCTS_INFO ."
        where product_id = '". (int)$this->data['id'] ."';"
      );
      
      database::query(
        "delete from ". DB_TABLE_PRODUCTS_PRICES ."
        where product_id = '". (int)$this->data['id'] ."';"
      );

      database::query(
        "delete from ". DB_TABLE_PRODUCTS_CAMPAIGNS ."
        where product_id = '". (int)$this->data['id'] ."';"
      );
      

      cache::clear_cache('products');
      
      $this->data['id'] = null;
    }
    
    public function add_image($file, $filename='') {
      
      if (empty($file)) return;
      
      if (!empty($filename)) $filename = 'products/' . $filename;
      
      if (empty($this->data['id'])) {
        $this->save();
      }
      
      if (!is_dir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'products/')) mkdir(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'products/', 0777);
      
      if (!$image = new ctrl_image($file)) return false;
      
    // 456-Fancy-product-title-N.jpg
      $i=1;
      while (empty($filename) || is_file(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename)) {
        $filename = 'products/' . $this->data['id'] .'-'. functions::general_path_friendly($this->data['name'][settings::get('default_language_code')]) .'-'. $i++ .'.'. $image->type();
      }
      
      $priority = count($this->data['images'])+1;
      
      if (!$image->resample(2048, 2048, 'FIT_ONLY_BIGGER')) return false;
      
      //$image->watermark(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'watermark.png', 'CENTER', 'MIDDLE'); // Watermark image
      
      if (!$image->write(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename, '', 90)) return false;
      
      functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $filename);
      
      database::query(
        "insert into ". DB_TABLE_PRODUCTS_IMAGES ."
        (product_id, filename, priority)
        values ('". (int)$this->data['id'] ."', '". database::input($filename) ."', '". (int)$priority ."');"
      );
      $image_id = database::insert_id();
      
      $this->data['images'][$image_id] = array(
        'id' => $image_id,
        'filename' => $filename,
        'priority' => $priority,
      );
    }
  }

?>